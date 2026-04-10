# Specification - Page Feuille de Présence (Unified Presence Sheet Management)

**Version**: 1.1
**Date**: 2026-02-13
**Status**: In Progress (Team Mode implemented)
**Legacy PHP**: GestionEquipeJoueur.php, GestionMatchEquipeJoueur.php

---

## 1. Vue d'ensemble

La page Feuille de Présence unifie la gestion des joueurs d'une équipe dans deux contextes distincts :

1. **Mode Équipe** (Team Composition) : Gestion de la composition d'une équipe pour une saison/compétition (`kp_competition_equipe_joueur`)
2. **Mode Match** (Match Composition) : Gestion de la composition d'une équipe pour un match spécifique (`kp_match_joueur`)

La page détecte automatiquement le mode en fonction des paramètres d'URL et adapte son comportement, ses actions disponibles, et ses règles de validation.

### 1.1 Routes

**Team Mode:**
```
/admin2/presence/team/:teamId
```
- Paramètre: `teamId` (Id de kp_competition_equipe)
- Exemple: `/admin2/presence/team/12345`

**Match Mode:**
```
/admin2/presence/match/:matchId/team/:teamCode
```
- Paramètres:
  - `matchId` (Id de kp_match)
  - `teamCode` ('A' ou 'B')
- Exemple: `/admin2/presence/match/67890/team/A`

### 1.2 Contrôle d'Accès

**Team Mode:**

| Profil | Droits | Conditions |
|--------|--------|------------|
| ≤ 10 | Lecture seule | - |
| ≤ 8 | Édition inline (numéro, capitaine) | Verrou = 'N' |
| ≤ 8 | Ajout joueurs (recherche licence) | Verrou = 'N' |
| ≤ 8 | Suppression | Verrou = 'N' |
| ≤ 4 | Copie depuis autre compétition | Verrou = 'N' |
| ≤ 4 | Création joueurs non-licenciés | Verrou = 'N' |

**Bloqué si**: Competition verrouillée (`kp_competition.Verrou = 'O'`)

**Match Mode:**

| Profil | Droits | Conditions |
|--------|--------|------------|
| ≤ 10 | Lecture seule | - |
| ≤ 9 | Édition inline, ajout/suppression | Validation = 'N' |
| ≤ 6 | Copie vers matchs même journée | Validation = 'N' |
| ≤ 4 | Copie vers tous matchs compétition | Validation = 'N' |

**Bloqué si**: Match validé (`kp_match.Validation = 'O'`)

---

## 2. Détection du Mode

### 2.1 URL Patterns

```typescript
// Routes Nuxt
definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

// Team Mode
const route = useRoute()
if (route.params.teamId) {
  mode = 'team'
  teamId = parseInt(route.params.teamId)
}

// Match Mode
if (route.params.matchId && route.params.teamCode) {
  mode = 'match'
  matchId = parseInt(route.params.matchId)
  teamCode = route.params.teamCode as 'A' | 'B'
}
```

### 2.2 State Interface

```typescript
interface PresencePageState {
  mode: 'team' | 'match'

  // Team mode
  teamId?: number

  // Match mode
  matchId?: number
  teamCode?: 'A' | 'B'

  // Context info (loaded from API)
  team: TeamInfo | null
  competition: CompetitionInfo | null
  match?: MatchInfo // only in match mode

  // Lock status
  isLocked: boolean // Verrou (team) or Validation (match)

  // Players
  players: Player[]

  // UI state
  loading: boolean
  selectedPlayerIds: number[]
  editingCell: { matric: number; field: 'numero' | 'capitaine' } | null
  editingValue: string
}
```

---

## 3. Fonctionnalités par Mode

### 3.1 Team Mode (Composition Équipe)

| # | Fonctionnalité | Profil | Bloqué si verrou | Source |
|---|----------------|--------|------------------|--------|
| 1 | Afficher liste joueurs triée | ≤ 10 | - | GestionEquipeJoueur.php:156-170 |
| 2 | Édition inline Numero (0-99) | ≤ 8 | ❌ | GestionEquipeJoueur.js:75-104 |
| 3 | Édition inline Capitaine (-, C, E, A, X) | ≤ 8 | ❌ | GestionEquipeJoueur.js:108-156 |
| 4 | Ajouter joueur depuis recherche licence | ≤ 8 | ❌ | GestionEquipeJoueur.php:269-276 |
| 5 | Créer nouveau joueur (licence, arbitre, ICF) | ≤ 4 | ❌ | GestionEquipeJoueur.php:280-341 |
| 6 | Ajouter joueur existant (autocomplete) | ≤ 8 | ❌ | GestionEquipeJoueur.tpl:197-235 |
| 7 | Sélection multiple + suppression en masse | ≤ 8 | ❌ | GestionEquipeJoueur.php:416-420 |
| 8 | Copier composition depuis autre compét. | ≤ 4 | ❌ | CopyTeamComposition.php |
| 9 | Afficher info licence (ICF si dispo, sinon Matric) | ≤ 10 | - | GestionEquipeJoueur.php:156-170 |
| 9b | Afficher saison licence entre parenthèses si < saison travail | ≤ 10 | - | - |
| 10 | Afficher surclassement (icône #S) | ≤ 10 | - | GestionEquipeJoueur.tpl:110-115 |
| 11 | Validation compétitions nationales | ≤ 8 | ❌ | GestionEquipeJoueur.js:242-265 |
| 12 | Séparateur visuel joueurs actifs/inactifs | ≤ 10 | - | GestionEquipeJoueur.tpl:148-150 |
| 13 | Dernière modification | ≤ 10 | - | GestionEquipeJoueur.php:172-175 |
| 14 | Liens PDF (FR/EN/Photo/Contrôle) | ≤ 10 | - | GestionEquipeJoueur.tpl:38-47 |
| 15 | Détection doublons à la création (nom+prénom, cliquable) | ≤ 4 | ❌ | Frontend uniquement |

**Tri par défaut:**
```sql
ORDER BY
  FIELD(IF(Capitaine='C', '-', Capitaine), '-', 'E', 'A', 'X'),
  Numero,
  Nom,
  Prenom
```

### 3.2 Match Mode (Composition Match)

| # | Fonctionnalité | Profil | Bloqué si validation | Source |
|---|----------------|--------|-----------------------|--------|
| 1 | Afficher liste joueurs | ≤ 10 | - | GestionMatchEquipeJoueur.php:168-195 |
| 2 | Édition inline Numero (0-99) | ≤ 9 | ❌ | GestionMatchEquipeJoueur.js:75-104 |
| 3 | Édition inline Capitaine (-, C, E) | ≤ 9 | ❌ | GestionMatchEquipeJoueur.js:108-156 |
| 4 | Initialiser depuis composition équipe | ≤ 9 | ❌ | GestionMatchEquipeJoueur.php:291-301 |
| 5 | Ajouter joueur (limité équipe, pas E/A/X) | ≤ 9 | ❌ | GestionMatchEquipeJoueur.php:306-327 |
| 6 | Sélection multiple + suppression | ≤ 9 | ❌ | GestionMatchEquipeJoueur.php:372-380 |
| 7 | Vider composition match | ≤ 9 | ❌ | GestionMatchEquipeJoueur.php:387-395 |
| 8 | Copier vers matchs même journée | ≤ 6 | ❌ | GestionMatchEquipeJoueur.php:293-358 |
| 9 | Copier vers tous matchs compétition | ≤ 4 | ❌ | GestionMatchEquipeJoueur.php:361-428 |
| 10 | Afficher contexte match | ≤ 10 | - | GestionMatchEquipeJoueur.tpl:4-16 |
| 11 | Afficher pagaie avec warning | ≤ 10 | - | GestionMatchEquipeJoueur.tpl:120-125 |

**Exclusion statuts E/A/X:**
- Lors de l'initialisation depuis équipe
- Lors de l'ajout manuel de joueurs
- Impossibilité de définir ces statuts

---

## 4. Règles de Validation

### 4.1 Compétitions Nationales (Team Mode)

**Détection:**
```php
// GestionEquipeJoueur.php:106-111
if (substr($Code_compet, 0, 1) == 'N')
    $typeCompet = 'CH'; // Championnat
elseif (substr($Code_compet, 0, 2) == 'CF')
    $typeCompet = 'CF'; // Coupe de France
else
    $typeCompet = '';
```

**Validation obligatoire pour N* et CF*:**

| Critère | Champ DB | Valeur attendue | Erreur |
|---------|----------|----------------|--------|
| Saison licence | `lc.Origine` | >= saison compétition | "Saison_licence" |
| Certificat CK | `lc.Etat_certificat_CK` | 'OUI' | "Certif" |
| Pagaie ECA | `lc.Pagaie_ECA` | NOT IN ('', 'PAGJ', 'PAGB') | "Pagaie_couleur" |
| Surclassement | `s.Date` | NOT NULL (si nécessaire) | "Surclassement" |

**Compétitions nécessitant surclassement:**
```javascript
// GestionEquipeJoueur.php:112-119
const surcl_necessaire = [
  'N1D', 'N1F', 'N1H', 'N2', 'N2H', 'N3H', 'N4H',
  'NQH', 'CFF', 'CFH', 'MCP'
]
const surcl_necessaire2 = ['N3', 'N4']
```

**Catégories exemptées de surclassement:**
- JUN, SEN, V1, V2, V3, V4

**Message d'erreur si validation échoue:**
```
"Ce joueur n'est pas en règle pour cette compétition (vérifier licence, certificat médical, pagaie, surclassement)"
```

### 4.2 Numéro de Maillot

- **Type**: INTEGER
- **Plage**: 0-99
- **Affichage**: 0 affiché comme vide ou placeholder
- **Validation**: Accepte valeurs vides → stocke 0

### 4.3 Statut Capitaine

**Team Mode** (kp_competition_equipe_joueur.Capitaine):

| Valeur | Libellé | Compte comme joueur | Initialisable dans match |
|--------|---------|---------------------|--------------------------|
| `-` | Joueur | ✅ Oui | ✅ Oui |
| `C` | Capitaine | ✅ Oui | ✅ Oui |
| `E` | Entraîneur (non joueur) | ❌ Non | ✅ Oui |
| `A` | Arbitre (non joueur) | ❌ Non | ❌ Non |
| `X` | Inactif | ❌ Non | ❌ Non |

**Match Mode** (kp_match_joueur.Capitaine):

| Valeur | Libellé | Autorisé |
|--------|---------|----------|
| `-` | Joueur | ✅ Oui |
| `C` | Capitaine | ✅ Oui |
| `E` | Entraîneur | ✅ Oui |
| `A` | Arbitre | ❌ Non |
| `X` | Inactif | ❌ Non |

**Classes CSS:**
```css
.colorCap-  /* Joueur - couleur normale */
.colorCapC  /* Capitaine - couleur spéciale */
.colorCapE  /* Entraîneur - grisé */
.colorCapA  /* Arbitre - grisé */
.colorCapX  /* Inactif - grisé */
```

---

## 5. API Endpoints

### 5.1 Team Mode Endpoints

#### GET /admin/teams/:teamId/players

**Description**: Récupère la composition d'une équipe

**Response:**
```typescript
interface TeamPlayersResponse {
  team: {
    id: number
    libelle: string
    numero: number
    codeCompet: string
    codeSaison: string
    codeClub: string
    poule: string
    tirage: number
  }
  competition: {
    code: string
    libelle: string
    verrou: boolean
    codeNiveau: string
    statut: string
  }
  players: Player[]
  lastUpdate?: {
    date: string
    user: string
    action: string
  }
}
```

#### POST /admin/teams/:teamId/players/add

**Description**: Ajoute un joueur à la composition d'équipe

**Request Body:**
```typescript
{
  // Mode 1: Joueur existant
  matric: number
  numero?: number
  capitaine?: '-' | 'C' | 'E' | 'A' | 'X'

  // Mode 2: Création joueur non-licencié (Profil ≤ 4)
  createNew?: boolean
  nom?: string        // Obligatoire
  prenom?: string     // Obligatoire
  sexe?: 'M' | 'F'   // Obligatoire
  naissance?: string  // YYYY-MM-DD (facultatif)
  numicf?: number     // N° licence ICF (facultatif, stocké dans kp_licence.Reserve)
  arbitre?: '' | 'REG' | 'NAT' | 'INT' | 'OTM' | 'JO'  // Qualification arbitre (facultatif)
  niveau?: '' | 'A' | 'B' | 'C' | 'S'                   // Niveau arbitre (facultatif, S = Stagiaire)
}
```

**Response:**
```typescript
{ success: boolean, matric?: number }
```

**Validation:**
- Vérifier que matric existe dans kp_licence (sauf si createNew)
- Si compétition N* ou CF*: valider pagaie, certificat, surclassement
- Générer matric >= 2000000 si createNew
- Vérifier doublon (nom + prénom + club)

**Détection de doublons (frontend) :**
- Lors de la saisie du nom et prénom dans l'onglet "Créer un nouveau joueur", une recherche debounced (500ms) est effectuée via `/admin/operations/autocomplete/players` dès que nom ET prénom ont ≥ 2 caractères
- Si des joueurs correspondants existent, un bandeau d'avertissement ambre s'affiche sous les champs nom/prénom
- Chaque joueur trouvé est **cliquable** : au clic, bascule automatiquement sur l'onglet "Ajouter un joueur existant" et pré-sélectionne le joueur dans l'autocomplete
- L'avertissement n'est pas bloquant : la création reste possible malgré les doublons détectés

**Création joueur (createNew = true):**
- Insère dans `kp_licence` avec :
  - `Matric` auto-incrémenté (>= 2000000)
  - `Origine` = saison de la compétition
  - `Numero_club` et `Club` = club de l'équipe en cours
  - `Reserve` = numicf (numéro licence ICF, facultatif)
  - `Sexe` obligatoire
- Si `arbitre` renseigné, insère aussi dans `kp_arbitre` avec :
  - Qualification : REG (Régional), NAT (National), INT (International), OTM (Officiel Table de Marque), JO (Jeune Officiel)
  - Niveau : A, B, C, S (Stagiaire)
  - Flags `regional`, `interregional`, `national`, `international` déduits de la qualification

#### DELETE /admin/teams/:teamId/players

**Description**: Supprime des joueurs en masse

**Request Body:**
```typescript
{ matricIds: number[] }
```

**Response:**
```typescript
{ success: boolean, deleted: number }
```

#### PATCH /admin/teams/:teamId/players/:matric

**Description**: Édition inline d'un joueur

**Request Body:**
```typescript
{
  numero?: number
  capitaine?: '-' | 'C' | 'E' | 'A' | 'X'
}
```

**Response:**
```typescript
{ success: boolean }
```

#### POST /admin/teams/:teamId/players/copy

**Description**: Copie la composition depuis une autre compétition/saison

**Request Body:**
```typescript
{
  sourceCompetition: string
  sourceSeason: string
}
```

**Response:**
```typescript
{ success: boolean, copied: number }
```

**Logique (remplacement complet):**
```sql
-- 1. Supprimer TOUS les joueurs existants de l'équipe cible
DELETE FROM kp_competition_equipe_joueur
WHERE Id_equipe = :teamId

-- 2. Copier depuis source (même club + même Numero d'équipe)
INSERT INTO kp_competition_equipe_joueur
(Id_equipe, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine)
SELECT :teamId, Matric, Nom, Prenom, Sexe, Categ, Numero, Capitaine
FROM kp_competition_equipe_joueur
WHERE Id_equipe = (
  SELECT Id FROM kp_competition_equipe
  WHERE Code_compet = :sourceCompetition
    AND Code_saison = :sourceSeason
    AND Code_club = (SELECT Code_club FROM kp_competition_equipe WHERE Id = :teamId)
    AND Numero = (SELECT Numero FROM kp_competition_equipe WHERE Id = :teamId)
)
```

**Important**: La copie est un **remplacement complet** : tous les joueurs existants sont supprimés avant d'insérer les joueurs sources. Le filtre par `Numero` garantit que seule l'équipe portant le même numéro (équipe 1, équipe 2...) au sein du même club est proposée comme source.

#### GET /admin/teams/:teamId/compositions

**Description**: Récupère les compositions disponibles pour copie (filtrées par même club ET même Numero d'équipe)

**Query Params:**
- `season`: Code saison (optionnel, défaut = saison actuelle)

**Filtrage**: Seules les équipes du même club ayant le même `Numero` dans `kp_competition_equipe` sont retournées. Cela garantit que l'équipe 1 d'un club ne voit que les compositions de l'équipe 1, pas celles de l'équipe 2.

**Response:**
```typescript
{
  compositions: Array<{
    competitionCode: string
    competitionLibelle: string
    season: string
    teamId: number
    playerCount: number
  }>
}
```

---

### 5.2 Match Mode Endpoints

#### GET /admin/matches/:matchId/players

**Description**: Récupère la composition d'une équipe pour un match

**Query Params:**
- `teamCode`: 'A' | 'B'

**Response:**
```typescript
interface MatchPlayersResponse {
  match: {
    id: number
    idJournee: number
    dateMatch: string
    heureMatch: string
    terrain: string
    numeroOrdre: number
    validation: boolean
    libelle: string
  }
  team: {
    id: number
    libelle: string
    codeCompet: string
    codeSaison: string
    codeClub: string
  }
  competition: {
    code: string
    libelle: string
    codeNiveau: string
  }
  players: Player[]
}
```

#### POST /admin/matches/:matchId/players/initialize

**Description**: Initialise la composition match depuis la composition équipe

**Request Body:**
```typescript
{ teamCode: 'A' | 'B' }
```

**Response:**
```typescript
{ success: boolean, added: number }
```

**Logique:**
```sql
-- 1. Récupérer Id_equipe depuis match
SELECT Id_equipeA, Id_equipeB FROM kp_match WHERE Id = :matchId

-- 2. Copier joueurs (exclut E, A, X)
INSERT INTO kp_match_joueur (Id_match, Matric, Numero, Equipe, Capitaine)
SELECT :matchId, Matric, Numero, :teamCode, Capitaine
FROM kp_competition_equipe_joueur
WHERE Id_equipe = :teamId
  AND Capitaine NOT IN ('E', 'A', 'X')
```

#### POST /admin/matches/:matchId/players/add

**Description**: Ajoute un joueur au match

**Request Body:**
```typescript
{
  matric: number
  teamCode: 'A' | 'B'
  numero?: number
  capitaine?: '-' | 'C' | 'E'
}
```

**Response:**
```typescript
{ success: boolean }
```

**Validation:**
- Vérifier que matric existe dans composition équipe
- Vérifier que matric n'a pas statut E, A, ou X dans équipe

#### DELETE /admin/matches/:matchId/players

**Description**: Supprime des joueurs du match

**Request Body:**
```typescript
{
  matricIds: number[]
  teamCode: 'A' | 'B'
}
```

**Response:**
```typescript
{ success: boolean, deleted: number }
```

#### DELETE /admin/matches/:matchId/players/clear

**Description**: Vide toute la composition match

**Request Body:**
```typescript
{ teamCode: 'A' | 'B' }
```

**Response:**
```typescript
{ success: boolean }
```

#### PATCH /admin/matches/:matchId/players/:matric

**Description**: Édition inline joueur match

**Request Body:**
```typescript
{
  numero?: number
  capitaine?: '-' | 'C' | 'E'
  teamCode: 'A' | 'B'
}
```

**Response:**
```typescript
{ success: boolean }
```

#### POST /admin/matches/:matchId/players/copy-to-day

**Description**: Copie vers tous matchs de l'équipe dans la même journée

**Request Body:**
```typescript
{
  teamCode: 'A' | 'B'
  journeeId: number
}
```

**Response:**
```typescript
{ success: boolean, copiedToMatches: number }
```

**Logique:**
```sql
-- Pour chaque match non validé de la même équipe dans la journée
INSERT INTO kp_match_joueur (Id_match, Matric, Numero, Equipe, Capitaine)
SELECT m.Id, mj.Matric, mj.Numero, mj.Equipe, mj.Capitaine
FROM kp_match m
CROSS JOIN kp_match_joueur mj
WHERE m.Id_journee = :journeeId
  AND m.Validation = 'N'
  AND m.Id != :matchId
  AND (m.Id_equipeA = :teamId OR m.Id_equipeB = :teamId)
  AND mj.Id_match = :matchId
  AND mj.Equipe = :teamCode
```

#### POST /admin/matches/:matchId/players/copy-to-competition

**Description**: Copie vers tous matchs de l'équipe dans la compétition

**Request Body:**
```typescript
{ teamCode: 'A' | 'B' }
```

**Response:**
```typescript
{ success: boolean, copiedToMatches: number }
```

**Validation:**
- Profil ≤ 4 requis
- Copie uniquement vers matchs non validés

#### GET /admin/matches/:matchId/copyable-matches

**Description**: Liste des matchs vers lesquels copier

**Query Params:**
- `teamCode`: 'A' | 'B'
- `scope`: 'day' | 'competition'

**Response:**
```typescript
{
  matches: Array<{
    id: number
    date: string
    heure: string
    terrain: string
    numeroOrdre: number
    adversaire: string
  }>
}
```

---

## 6. Types TypeScript

### 6.1 Player Interface

```typescript
export interface Player {
  // Identity
  matric: number
  nom: string
  prenom: string
  sexe: 'M' | 'F'
  categ: string
  naissance: string | null

  // Team composition
  numero: number
  capitaine: '-' | 'C' | 'E' | 'A' | 'X'

  // License info
  origine: string // season of license
  numeroClub: string
  clubLibelle: string

  // Pagaie (paddle)
  pagaieECA: string // PAGR, PAGN, PAGBL, PAGV, PAGJ, PAGB
  pagaieEVI: string
  pagaieMER: string
  pagaieLabel: string // Combined: "Rouge", "Noire", etc.
  pagaieValide: number // 0=invalid, 1=ECA, 2=EVI, 3=MER

  // Certificates
  certifCK: 'OUI' | 'NON'
  certifAPS: 'OUI' | 'NON'
  dateCertifCK: string | null
  dateCertifAPS: string | null

  // Referee
  arbitre: string // 'REG', 'IR', 'NAT', 'INT', 'OTM', 'JO'
  niveau: string // referee level

  // Surclassement (age overclassing)
  dateSurclassement: string | null

  // ICF number (international)
  icf: number | null
}
```

### 6.1b Affichage du numéro de licence

La colonne "Licence" affiche :
- Le numéro de licence ICF (`icf`, champ `Reserve` de `kp_licence`) s'il existe
- Sinon le numéro de licence national (`matric`)
- Si la saison de licence (`origine`) est inférieure à la saison de travail de la compétition (`codeSaison`), la saison est affichée entre parenthèses après le numéro

**Exemples :**
| icf | matric | origine | codeSaison | Affichage |
|-----|--------|---------|------------|-----------|
| 12345 | 100200 | 2026 | 2026 | `12345` |
| 12345 | 100200 | 2025 | 2026 | `12345 (2025)` |
| null | 100200 | 2026 | 2026 | `100200` |
| null | 100200 | 2024 | 2026 | `100200 (2024)` |

### 6.2 Team & Competition Info

```typescript
export interface TeamInfo {
  id: number
  libelle: string
  numero: number
  codeCompet: string
  codeSaison: string
  codeClub: string
  clubLibelle: string
  poule: string
  tirage: number
  logo: string | null
}

export interface CompetitionInfo {
  code: string
  libelle: string
  verrou: boolean
  codeNiveau: string
  statut: string
}

export interface MatchInfo {
  id: number
  idJournee: number
  dateMatch: string
  heureMatch: string
  terrain: string
  numeroOrdre: number
  validation: boolean
  libelle: string
}
```

### 6.3 Form Data

```typescript
// Team Mode: Add player
export interface AddPlayerFormData {
  mode: 'existing' | 'create'

  // Existing player
  matric?: number

  // Create new (Matric >= 2000000, insère dans kp_licence + kp_arbitre)
  nom?: string          // Obligatoire
  prenom?: string       // Obligatoire
  sexe?: 'M' | 'F'     // Obligatoire
  naissance?: string    // YYYY-MM-DD (facultatif)
  numicf?: number       // N° licence ICF (facultatif → kp_licence.Reserve)

  // Common
  numero?: number
  capitaine?: '-' | 'C' | 'E' | 'A' | 'X'

  // Optional referee (si renseigné → insère dans kp_arbitre)
  arbitre?: '' | 'REG' | 'NAT' | 'INT' | 'OTM' | 'JO'
  niveau?: '' | 'A' | 'B' | 'C' | 'S'  // S = Stagiaire
}

// Team Mode: Copy composition
export interface CopyCompositionFormData {
  sourceCompetition: string
  sourceSeason: string
}

// Match Mode: Add player
export interface MatchAddPlayerFormData {
  matric: number
  numero?: number
  capitaine?: '-' | 'C' | 'E'
}

// Match Mode: Copy to matches
export interface CopyToMatchesFormData {
  scope: 'day' | 'competition'
  selectedMatchIds?: number[]
}
```

---

## 7. Structure de la Page

### 7.1 Header Contextuel

**Team Mode:**
```vue
<div class="mb-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
  <div class="flex items-center gap-3 mb-2">
    <UIcon name="i-heroicons-clipboard-document-list" class="w-6 h-6 text-blue-600" />
    <h1 class="text-xl font-bold text-gray-900">
      {{ t('presence.title_team') }}
    </h1>
  </div>

  <div class="text-sm text-gray-700">
    <div class="flex items-center gap-2">
      <UIcon name="i-heroicons-users" class="w-4 h-4" />
      <span class="font-semibold">{{ team.libelle }}</span>
      <span class="text-gray-500">({{ competition.code }}-{{ team.codeSaison }})</span>
    </div>

    <div v-if="isLocked" class="flex items-center gap-2 mt-2 text-amber-700">
      <UIcon name="i-heroicons-lock-closed" class="w-4 h-4" />
      <span>{{ t('presence.competition_locked') }}</span>
    </div>

    <div v-if="isNationalCompetition" class="flex items-center gap-2 mt-2 text-blue-700">
      <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4" />
      <span>{{ t('presence.national_validation_required') }}</span>
    </div>
  </div>
</div>
```

**Match Mode:**
```vue
<div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg">
  <div class="flex items-center gap-3 mb-2">
    <UIcon name="i-heroicons-clipboard-document-check" class="w-6 h-6 text-green-600" />
    <h1 class="text-xl font-bold text-gray-900">
      {{ t('presence.title_match') }}
    </h1>
  </div>

  <div class="text-sm text-gray-700">
    <div class="flex flex-wrap items-center gap-3">
      <div class="flex items-center gap-1">
        <UIcon name="i-heroicons-calendar" class="w-4 h-4" />
        <span>{{ formatDate(match.dateMatch) }}</span>
      </div>
      <div class="flex items-center gap-1">
        <UIcon name="i-heroicons-clock" class="w-4 h-4" />
        <span>{{ match.heureMatch }}</span>
      </div>
      <div class="flex items-center gap-1">
        <UIcon name="i-heroicons-map-pin" class="w-4 h-4" />
        <span>{{ match.terrain }}</span>
      </div>
      <div class="flex items-center gap-1">
        <span class="font-mono text-gray-500">#{{ match.numeroOrdre }}</span>
      </div>
    </div>

    <div class="flex items-center gap-2 mt-2">
      <span class="font-semibold">{{ teamCode === 'A' ? t('common.team_a') : t('common.team_b') }}:</span>
      <span>{{ team.libelle }}</span>
      <span class="text-gray-500">({{ competition.code }})</span>
    </div>

    <div v-if="isLocked" class="flex items-center gap-2 mt-2 text-green-700">
      <UIcon name="i-heroicons-check-circle" class="w-4 h-4" />
      <span>{{ t('presence.match_validated') }}</span>
    </div>
  </div>
</div>
```

### 7.2 Barre d'Outils

**Team Mode:**
```vue
<AdminToolbar
  v-model:search="search"
  :search-placeholder="t('presence.search_player')"
  :add-label="t('presence.add_player')"
  :show-add="canEdit"
  :show-bulk-delete="canEdit"
  :bulk-delete-label="t('common.delete_selected')"
  :selected-count="selectedPlayerIds.length"
  @add="openAddModal"
  @bulk-delete="confirmBulkDelete"
>
  <template #left>
    <button
      v-if="canCopy"
      class="px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg"
      @click="openCopyModal"
    >
      <UIcon name="i-heroicons-document-duplicate" class="w-4 h-4 inline mr-1" />
      {{ t('presence.copy_from') }}
    </button>

    <button
      v-if="canEdit && authStore.profile <= 4"
      class="px-3 py-2 text-sm font-medium text-gray-700 bg-gray-50 hover:bg-gray-100 rounded-lg"
      @click="openSearchLicense"
    >
      <UIcon name="i-heroicons-magnifying-glass" class="w-4 h-4 inline mr-1" />
      {{ t('presence.search_license') }}
    </button>
  </template>

  <template #after-search>
    <div class="flex items-center gap-2">
      <a
        :href="`${backendBaseUrl}/admin/FeuillePresence.php?equipe=${teamId}`"
        target="_blank"
        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg"
      >
        <UIcon name="i-heroicons-document-text" class="w-4 h-4 inline mr-1" />
        {{ t('presence.pdf_fr') }}
      </a>
      <a
        :href="`${backendBaseUrl}/admin/FeuillePresenceEN.php?equipe=${teamId}`"
        target="_blank"
        class="px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 rounded-lg"
      >
        <UIcon name="i-heroicons-document-text" class="w-4 h-4 inline mr-1" />
        {{ t('presence.pdf_en') }}
      </a>
    </div>
  </template>
</AdminToolbar>
```

**Match Mode:**
```vue
<AdminToolbar
  v-model:search="search"
  :search-placeholder="t('presence.search_player')"
  :add-label="t('presence.add_player')"
  :show-add="canEdit"
  :show-bulk-delete="canEdit"
  :selected-count="selectedPlayerIds.length"
  @add="openAddModal"
  @bulk-delete="confirmBulkDelete"
>
  <template #left>
    <button
      v-if="canEdit && players.length === 0"
      class="px-3 py-2 text-sm font-medium text-white bg-green-600 hover:bg-green-700 rounded-lg"
      @click="initializeFromTeam"
    >
      <UIcon name="i-heroicons-arrow-down-tray" class="w-4 h-4 inline mr-1" />
      {{ t('presence.initialize_from_team') }}
    </button>

    <button
      v-if="canEdit"
      class="px-3 py-2 text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 rounded-lg"
      @click="confirmClearAll"
    >
      <UIcon name="i-heroicons-trash" class="w-4 h-4 inline mr-1" />
      {{ t('presence.clear_all') }}
    </button>

    <button
      v-if="canCopy"
      class="px-3 py-2 text-sm font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded-lg"
      @click="openCopyToMatchesModal"
    >
      <UIcon name="i-heroicons-document-duplicate" class="w-4 h-4 inline mr-1" />
      {{ t('presence.copy_to_matches') }}
    </button>
  </template>
</AdminToolbar>
```

### 7.3 Tableau des Joueurs

**Desktop (hidden on mobile):**
```vue
<div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th v-if="canEdit" class="w-10 px-3 py-3">
          <input
            v-model="selectAll"
            type="checkbox"
            class="rounded border-gray-300"
            @change="toggleSelectAll"
          />
        </th>
        <th class="w-16 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
        <th class="w-12 px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cap</th>
        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.last_name') }}</th>
        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.first_name') }}</th>
        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.license') }}</th>
        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.club') }}</th>
        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.category') }}</th>
        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.paddle') }}</th>
        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.certificate') }}</th>
        <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.referee') }}</th>
        <th v-if="canEdit" class="w-16 px-3 py-3"></th>
      </tr>
    </thead>

    <tbody class="bg-white divide-y divide-gray-200">
      <!-- Active players (-, C) -->
      <tr
        v-for="player in activePlayers"
        :key="player.matric"
        class="hover:bg-gray-50"
        :class="{ 'bg-yellow-50': player.capitaine === 'C' }"
      >
        <td v-if="canEdit" class="px-3 py-4">
          <input
            v-model="selectedPlayerIds"
            type="checkbox"
            :value="player.matric"
            class="rounded border-gray-300"
          />
        </td>

        <!-- Numero (inline edit) -->
        <td class="px-3 py-4 text-sm text-gray-900">
          <span
            v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'numero'"
            :class="canEdit ? 'editable-cell' : ''"
            @click="canEdit && startEdit(player, 'numero')"
          >
            {{ player.numero || '-' }}
          </span>
          <input
            v-else
            :id="`inline-edit-${player.matric}-numero`"
            v-model.number="editingValue"
            type="number"
            min="0"
            max="99"
            class="w-16 px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
            @keydown="handleInlineKeydown"
            @blur="saveInlineEdit"
          />
        </td>

        <!-- Capitaine (inline edit) -->
        <td class="px-3 py-4 text-sm">
          <span
            v-if="editingCell?.matric !== player.matric || editingCell?.field !== 'capitaine'"
            :class="[
              canEdit ? 'editable-cell' : '',
              `colorCap${player.capitaine}`
            ]"
            @click="canEdit && startEdit(player, 'capitaine')"
          >
            {{ player.capitaine }}
          </span>
          <select
            v-else
            :id="`inline-edit-${player.matric}-capitaine`"
            v-model="editingValue"
            class="px-2 py-1 border border-blue-400 rounded text-sm focus:ring-2 focus:ring-blue-500"
            @change="saveInlineEdit"
            @blur="saveInlineEdit"
          >
            <option value="-">-</option>
            <option value="C">C</option>
            <option value="E">E</option>
            <option v-if="mode === 'team'" value="A">A</option>
            <option v-if="mode === 'team'" value="X">X</option>
          </select>
        </td>

        <td class="px-3 py-4 text-sm font-medium text-gray-900">{{ player.nom }}</td>
        <td class="px-3 py-4 text-sm text-gray-900">{{ player.prenom }}</td>
        <td class="px-3 py-4 text-sm text-gray-500 font-mono">{{ player.matric }}</td>
        <td class="px-3 py-4 text-sm text-gray-500">{{ player.clubLibelle }}</td>
        <td class="px-3 py-4 text-sm text-gray-500">{{ player.categ }}-{{ player.sexe }}</td>

        <!-- Pagaie with validation -->
        <td class="px-3 py-4 text-sm">
          <span
            v-if="player.pagaieValide === 0"
            class="text-red-600"
            :title="t('presence.invalid_paddle')"
          >
            ({{ player.pagaieLabel }})
          </span>
          <span v-else class="text-gray-700">
            {{ player.pagaieLabel }}
          </span>
        </td>

        <!-- Certificate -->
        <td class="px-3 py-4 text-sm">
          <span
            v-if="player.certifCK === 'OUI'"
            class="text-green-600"
          >
            {{ t('common.yes') }}
          </span>
          <span v-else class="text-red-600">
            {{ t('common.no') }}
          </span>
        </td>

        <!-- Referee -->
        <td class="px-3 py-4 text-sm text-gray-500">
          <span v-if="player.arbitre">{{ player.arbitre }}</span>
          <span v-if="player.niveau" class="text-xs">({{ player.niveau }})</span>
        </td>

        <!-- Actions -->
        <td v-if="canEdit" class="px-3 py-4 text-right">
          <button
            class="text-red-600 hover:text-red-800"
            @click="deletePlayer(player.matric)"
          >
            <UIcon name="i-heroicons-trash" class="w-5 h-5" />
          </button>
        </td>
      </tr>

      <!-- Separator if inactive players exist -->
      <tr v-if="inactivePlayers.length > 0" class="bg-gray-100">
        <td :colspan="canEdit ? 12 : 11" class="px-3 py-2 text-xs text-gray-500 text-center">
          {{ t('presence.inactive_players') }}
        </td>
      </tr>

      <!-- Inactive players (E, A, X) - same editable structure as active players -->
      <tr
        v-for="player in inactivePlayers"
        :key="player.matric"
        class="hover:bg-gray-50 opacity-60"
      >
        <!-- Same structure as active players with inline editing for numero/capitaine -->
        <!-- Coaches (E), Referees (A) and Inactive (X) are editable when not locked -->
      </tr>
    </tbody>
  </table>

  <!-- Footer -->
  <div class="px-4 py-3 bg-gray-50 border-t border-gray-200 text-sm text-gray-600">
    <div class="flex items-center justify-between">
      <div>
        {{ t('presence.total_players', { count: players.length }) }}
        <span v-if="lastUpdate" class="ml-4 text-xs text-gray-500">
          {{ t('presence.last_update') }}: {{ formatDate(lastUpdate.date) }} - {{ lastUpdate.user }}
        </span>
      </div>
    </div>
  </div>
</div>
```

**Mobile Cards (only on mobile):**
```vue
<AdminCardList
  class="lg:hidden"
  :loading="loading && players.length === 0"
  :empty="players.length === 0"
>
  <AdminCard
    v-for="player in players"
    :key="player.matric"
    :selected="selectedPlayerIds.includes(player.matric)"
    :show-checkbox="canEdit"
    @toggle-select="toggleSelect(player.matric)"
  >
    <template #header>
      <div class="flex items-center gap-2">
        <span class="font-bold">{{ player.nom }} {{ player.prenom }}</span>
        <span
          class="px-2 py-0.5 text-xs rounded"
          :class="player.capitaine === 'C' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-600'"
        >
          {{ player.capitaine }}
        </span>
      </div>
    </template>

    <div class="space-y-1 text-sm">
      <div class="flex items-center gap-2">
        <span class="text-gray-500">{{ t('common.number') }}:</span>
        <span class="font-medium">{{ player.numero || '-' }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-gray-500">{{ t('common.license') }}:</span>
        <span class="font-mono">{{ player.matric }}</span>
      </div>
      <div class="flex items-center gap-2">
        <span class="text-gray-500">{{ t('common.paddle') }}:</span>
        <span :class="player.pagaieValide === 0 ? 'text-red-600' : ''">
          {{ player.pagaieLabel }}
        </span>
      </div>
    </div>

    <template #footer-right>
      <AdminActionButton
        v-if="canEdit"
        icon="heroicons:trash-solid"
        color="red"
        @click="deletePlayer(player.matric)"
      >
        {{ t('common.delete') }}
      </AdminActionButton>
    </template>
  </AdminCard>
</AdminCardList>
```

---

## 8. Modals

### 8.1 Team Mode: Ajouter Joueur

```vue
<AdminModal
  :open="addModalOpen"
  :title="t('presence.add_player')"
  max-width="lg"
  @close="addModalOpen = false"
>
  <!-- Tabs -->
  <div class="flex border-b border-gray-200 mb-4">
    <button
      class="px-4 py-2 text-sm font-medium border-b-2"
      :class="addMode === 'existing' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
      @click="addMode = 'existing'"
    >
      {{ t('presence.add_existing_player') }}
    </button>
    <button
      v-if="authStore.profile <= 4"
      class="px-4 py-2 text-sm font-medium border-b-2"
      :class="addMode === 'create' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
      @click="addMode = 'create'"
    >
      {{ t('presence.create_new_player') }}
    </button>
  </div>

  <!-- Error message -->
  <div v-if="formError" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
    <UIcon name="i-heroicons-exclamation-triangle" class="w-5 h-5 inline mr-2" />
    {{ formError }}
  </div>

  <!-- Existing player form -->
  <form v-if="addMode === 'existing'" @submit.prevent="addExistingPlayer" class="space-y-4">
    <!-- Player search autocomplete (shared component) -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ t('presence.search_player') }}
      </label>
      <AdminPlayerAutocomplete
        v-model="selectedPlayer"
        :placeholder="t('common.search_player_placeholder')"
        :disabled="isLocked"
      />
    </div>

    <!-- Selected player info -->
    <div v-if="selectedPlayer" class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
      <div class="grid grid-cols-2 gap-2 text-sm">
        <div><span class="font-medium">{{ t('common.license') }}:</span> {{ selectedPlayer.matric }}</div>
        <div><span class="font-medium">{{ t('common.name') }}:</span> {{ selectedPlayer.nom }} {{ selectedPlayer.prenom }}</div>
        <div><span class="font-medium">{{ t('common.club') }}:</span> {{ selectedPlayer.clubLibelle }}</div>
        <div><span class="font-medium">{{ t('common.category') }}:</span> {{ selectedPlayer.categ }}-{{ selectedPlayer.sexe }}</div>
        <div><span class="font-medium">{{ t('common.paddle') }}:</span> {{ selectedPlayer.pagaieLabel }}</div>
        <div><span class="font-medium">{{ t('common.certificate') }}:</span> {{ selectedPlayer.certifCK }}</div>
      </div>

      <!-- Validation warnings for national competitions -->
      <div v-if="isNationalCompetition && !isPlayerValid(selectedPlayer)" class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-red-800 text-sm">
        <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 inline mr-1" />
        {{ t('presence.player_not_valid') }}
        <ul class="mt-1 ml-5 list-disc text-xs">
          <li v-if="selectedPlayer.origine < competition.codeSaison">{{ t('presence.invalid_license_season') }}</li>
          <li v-if="selectedPlayer.certifCK !== 'OUI'">{{ t('presence.invalid_certificate') }}</li>
          <li v-if="selectedPlayer.pagaieValide === 0">{{ t('presence.invalid_paddle') }}</li>
          <li v-if="needsSurclassement && !selectedPlayer.dateSurclassement">{{ t('presence.missing_surclassement') }}</li>
        </ul>
      </div>
    </div>

    <!-- Numero and Capitaine -->
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.number') }}
        </label>
        <input
          v-model.number="formData.numero"
          type="number"
          min="0"
          max="99"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.status') }}
        </label>
        <select
          v-model="formData.capitaine"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option value="-">{{ t('presence.status_player') }}</option>
          <option value="C">{{ t('presence.status_captain') }}</option>
          <option value="E">{{ t('presence.status_coach') }}</option>
          <option value="A">{{ t('presence.status_referee') }}</option>
          <option value="X">{{ t('presence.status_inactive') }}</option>
        </select>
      </div>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4 border-t">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        @click="addModalOpen = false"
      >
        {{ t('common.cancel') }}
      </button>
      <button
        type="submit"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
        :disabled="!selectedPlayer || formSaving"
      >
        {{ t('common.add') }}
      </button>
    </div>
  </form>

  <!-- Create new player form -->
  <form v-else-if="addMode === 'create'" @submit.prevent="createNewPlayer" class="space-y-4">
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.last_name') }} *
        </label>
        <input
          v-model="formData.nom"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.first_name') }} *
        </label>
        <input
          v-model="formData.prenom"
          type="text"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>

    <div class="grid grid-cols-3 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.gender') }} *
        </label>
        <select
          v-model="formData.sexe"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option value="M">{{ t('common.male') }}</option>
          <option value="F">{{ t('common.female') }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.birth_date') }} *
        </label>
        <input
          v-model="formData.naissance"
          type="date"
          required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.number') }}
        </label>
        <input
          v-model.number="formData.numero"
          type="number"
          min="0"
          max="99"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>

    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.status') }}
        </label>
        <select
          v-model="formData.capitaine"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option value="-">{{ t('presence.status_player') }}</option>
          <option value="C">{{ t('presence.status_captain') }}</option>
          <option value="E">{{ t('presence.status_coach') }}</option>
          <option value="A">{{ t('presence.status_referee') }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.icf_number') }}
        </label>
        <input
          v-model.number="formData.numicf"
          type="number"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>

    <!-- Referee info (optional) -->
    <div class="grid grid-cols-2 gap-4">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.referee_type') }}
        </label>
        <select
          v-model="formData.arbitre"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        >
          <option value="">-</option>
          <option value="REG">{{ t('referee.regional') }}</option>
          <option value="IR">{{ t('referee.interregional') }}</option>
          <option value="NAT">{{ t('referee.national') }}</option>
          <option value="INT">{{ t('referee.international') }}</option>
          <option value="OTM">{{ t('referee.otm') }}</option>
          <option value="JO">{{ t('referee.jo') }}</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">
          {{ t('common.referee_level') }}
        </label>
        <input
          v-model="formData.niveau"
          type="text"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        />
      </div>
    </div>

    <!-- Warning -->
    <div v-if="isNationalCompetition" class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
      <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 inline mr-1" />
      {{ t('presence.create_not_allowed_national') }}
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4 border-t">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        @click="addModalOpen = false"
      >
        {{ t('common.cancel') }}
      </button>
      <button
        type="submit"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
        :disabled="isNationalCompetition || formSaving"
      >
        {{ t('common.create') }}
      </button>
    </div>
  </form>
</AdminModal>
```

### 8.2 Team Mode: Copier Composition

```vue
<AdminModal
  :open="copyModalOpen"
  :title="t('presence.copy_from')"
  max-width="md"
  @close="copyModalOpen = false"
>
  <form @submit.prevent="copyComposition" class="space-y-4">
    <!-- Error -->
    <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
      {{ formError }}
    </div>

    <!-- Source season -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ t('presence.source_season') }}
      </label>
      <select
        v-model="copyFormData.sourceSeason"
        required
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        @change="loadSourceCompetitions"
      >
        <option :value="team.codeSaison">{{ team.codeSaison }} ({{ t('common.current') }})</option>
        <option :value="String(parseInt(team.codeSaison) - 1)">{{ parseInt(team.codeSaison) - 1 }}</option>
        <option :value="String(parseInt(team.codeSaison) - 2)">{{ parseInt(team.codeSaison) - 2 }}</option>
      </select>
    </div>

    <!-- Source competition -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ t('presence.source_competition') }}
      </label>
      <select
        v-model="copyFormData.sourceCompetition"
        required
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        :disabled="loadingCompositions"
      >
        <option value="">{{ t('common.select') }}</option>
        <option
          v-for="comp in availableCompositions"
          :key="comp.competitionCode"
          :value="comp.competitionCode"
        >
          {{ comp.competitionLibelle }} ({{ comp.playerCount }} {{ t('common.players') }})
        </option>
      </select>
    </div>

    <!-- Warning -->
    <div v-if="copyFormData.sourceCompetition" class="p-3 bg-amber-50 border border-amber-200 rounded-lg text-amber-800 text-sm">
      <UIcon name="i-heroicons-exclamation-triangle" class="w-4 h-4 inline mr-1" />
      {{ t('presence.copy_warning') }}
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4 border-t">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        @click="copyModalOpen = false"
      >
        {{ t('common.cancel') }}
      </button>
      <button
        type="submit"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
        :disabled="!copyFormData.sourceCompetition || formSaving"
      >
        {{ t('common.copy') }}
      </button>
    </div>
  </form>
</AdminModal>
```

### 8.3 Match Mode: Ajouter Joueur

```vue
<AdminModal
  :open="addModalOpen"
  :title="t('presence.add_player')"
  max-width="md"
  @close="addModalOpen = false"
>
  <form @submit.prevent="addPlayerToMatch" class="space-y-4">
    <!-- Error -->
    <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
      {{ formError }}
    </div>

    <!-- Player selection (from team roster, excluding E/A/X and already added) -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ t('presence.select_player') }}
      </label>
      <select
        v-model="formData.matric"
        required
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
        @change="onPlayerSelect"
      >
        <option value="">{{ t('common.select') }}</option>
        <option
          v-for="player in availableTeamPlayers"
          :key="player.matric"
          :value="player.matric"
        >
          {{ player.nom }} {{ player.prenom }} - #{{ player.numero }} - {{ player.categ }}-{{ player.sexe }}
        </option>
      </select>
    </div>

    <!-- Numero (pre-filled from team roster) -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ t('common.number') }}
      </label>
      <input
        v-model.number="formData.numero"
        type="number"
        min="0"
        max="99"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
      />
    </div>

    <!-- Capitaine (-, C, E only) -->
    <div>
      <label class="block text-sm font-medium text-gray-700 mb-1">
        {{ t('common.status') }}
      </label>
      <select
        v-model="formData.capitaine"
        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
      >
        <option value="-">{{ t('presence.status_player') }}</option>
        <option value="C">{{ t('presence.status_captain') }}</option>
        <option value="E">{{ t('presence.status_coach') }}</option>
      </select>
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4 border-t">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        @click="addModalOpen = false"
      >
        {{ t('common.cancel') }}
      </button>
      <button
        type="submit"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
        :disabled="!formData.matric || formSaving"
      >
        {{ t('common.add') }}
      </button>
    </div>
  </form>
</AdminModal>
```

### 8.4 Match Mode: Copier vers Matchs

```vue
<AdminModal
  :open="copyToMatchesModalOpen"
  :title="t('presence.copy_to_matches')"
  max-width="md"
  @close="copyToMatchesModalOpen = false"
>
  <!-- Tabs -->
  <div class="flex border-b border-gray-200 mb-4">
    <button
      class="px-4 py-2 text-sm font-medium border-b-2"
      :class="copyScope === 'day' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
      @click="copyScope = 'day'"
    >
      {{ t('presence.copy_to_same_day') }}
    </button>
    <button
      v-if="authStore.profile <= 4"
      class="px-4 py-2 text-sm font-medium border-b-2"
      :class="copyScope === 'competition' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500'"
      @click="copyScope = 'competition'"
    >
      {{ t('presence.copy_to_competition') }}
    </button>
  </div>

  <form @submit.prevent="copyToMatches" class="space-y-4">
    <!-- Error -->
    <div v-if="formError" class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-800 text-sm">
      {{ formError }}
    </div>

    <!-- Match list with checkboxes -->
    <div v-if="copyableMatches.length > 0" class="max-h-96 overflow-y-auto border border-gray-200 rounded-lg">
      <label
        v-for="match in copyableMatches"
        :key="match.id"
        class="flex items-center gap-3 px-3 py-2 hover:bg-gray-50 border-b border-gray-100 last:border-b-0"
      >
        <input
          v-model="selectedMatchIds"
          type="checkbox"
          :value="match.id"
          class="rounded border-gray-300"
        />
        <div class="flex-1 text-sm">
          <div class="font-medium">{{ formatDate(match.date) }} - {{ match.heure }}</div>
          <div class="text-gray-500">{{ match.terrain }} - vs {{ match.adversaire }}</div>
        </div>
      </label>
    </div>

    <div v-else class="p-4 text-center text-sm text-gray-500">
      {{ t('presence.no_copyable_matches') }}
    </div>

    <!-- Summary -->
    <div v-if="selectedMatchIds.length > 0" class="p-3 bg-blue-50 border border-blue-200 rounded-lg text-blue-800 text-sm">
      {{ t('presence.copy_to_count', { count: selectedMatchIds.length }) }}
    </div>

    <!-- Actions -->
    <div class="flex justify-end gap-2 pt-4 border-t">
      <button
        type="button"
        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
        @click="copyToMatchesModalOpen = false"
      >
        {{ t('common.cancel') }}
      </button>
      <button
        type="submit"
        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700"
        :disabled="selectedMatchIds.length === 0 || formSaving"
      >
        {{ t('common.copy') }}
      </button>
    </div>
  </form>
</AdminModal>
```

---

## 9. Permissions et Verrouillage

### 9.1 Matrice de Permissions

**Team Mode:**

| Action | Profil requis | Condition supplémentaire |
|--------|---------------|--------------------------|
| Voir liste | ≤ 10 | - |
| Éditer inline (numero, capitaine) | ≤ 8 | Verrou = 'N' |
| Ajouter joueur existant | ≤ 8 | Verrou = 'N' |
| Créer joueur non-licencié | ≤ 4 | Verrou = 'N' ET compétition non N*/CF* |
| Supprimer joueurs | ≤ 8 | Verrou = 'N' |
| Copier composition | ≤ 4 | Verrou = 'N' |
| Recherche licence avancée | ≤ 2 | - |
| Voir dernière modification | ≤ 10 | - |
| Accès PDF | ≤ 10 | - |

**Match Mode:**

| Action | Profil requis | Condition supplémentaire |
|--------|---------------|--------------------------|
| Voir liste | ≤ 10 | - |
| Éditer inline (numero, capitaine) | ≤ 9 | Validation = 'N' |
| Initialiser depuis équipe | ≤ 9 | Validation = 'N' |
| Ajouter joueur | ≤ 9 | Validation = 'N' |
| Supprimer joueurs | ≤ 9 | Validation = 'N' |
| Vider composition | ≤ 9 | Validation = 'N' |
| Copier vers journée | ≤ 6 | Validation = 'N' |
| Copier vers compétition | ≤ 4 | Validation = 'N' |

### 9.2 Computed Permissions

```typescript
// composables/usePresencePermissions.ts
export function usePresencePermissions(mode: 'team' | 'match', isLocked: boolean) {
  const authStore = useAuthStore()

  const canView = computed(() => authStore.profile <= 10)

  const canEdit = computed(() => {
    if (isLocked) return false
    if (mode === 'team') return authStore.profile <= 8
    if (mode === 'match') return authStore.profile <= 9
    return false
  })

  const canDelete = computed(() => canEdit.value)

  const canCopy = computed(() => {
    if (isLocked) return false
    if (mode === 'team') return authStore.profile <= 4
    if (mode === 'match') return authStore.profile <= 6 // day copy
    return false
  })

  const canCopyToCompetition = computed(() => {
    if (isLocked) return false
    if (mode === 'match') return authStore.profile <= 4
    return false
  })

  const canCreatePlayer = computed(() => {
    if (mode !== 'team') return false
    if (isLocked) return false
    return authStore.profile <= 4
  })

  const canSearchLicense = computed(() => {
    if (mode !== 'team') return false
    return authStore.profile <= 2
  })

  return {
    canView,
    canEdit,
    canDelete,
    canCopy,
    canCopyToCompetition,
    canCreatePlayer,
    canSearchLicense,
  }
}
```

---

## 10. Validation et Feedback

### 10.1 Validations Backend

**Team Mode - Ajouter Joueur:**
```php
// Validation pagaie + certificat si N* ou CF*
if (in_array(substr($codeCompet, 0, 1), ['N']) || substr($codeCompet, 0, 2) === 'CF') {
    if ($licence->Origine < $codeSaison) {
        throw new ValidationException('Saison de licence invalide');
    }
    if ($licence->Etat_certificat_CK !== 'OUI') {
        throw new ValidationException('Certificat médical manquant');
    }
    if (in_array($licence->Pagaie_ECA, ['', 'PAGJ', 'PAGB'])) {
        throw new ValidationException('Pagaie couleur invalide');
    }

    // Surclassement si nécessaire
    $surclNecessaire = in_array($codeCompet, ['N1D', 'N1F', 'N1H', 'N2', ...]);
    if ($surclNecessaire && !in_array($categ, ['JUN', 'SEN', 'V1', ...]) && !$dateSurclassement) {
        throw new ValidationException('Surclassement manquant');
    }
}
```

**Match Mode - Ajouter Joueur:**
```php
// Vérifier existence dans composition équipe
$player = $em->getRepository(CompetitionEquipeJoueur::class)
    ->findOneBy(['idEquipe' => $teamId, 'matric' => $matric]);

if (!$player) {
    throw new ValidationException('Joueur non trouvé dans composition équipe');
}

// Interdire statuts E, A, X
if (in_array($player->getCapitaine(), ['E', 'A', 'X'])) {
    throw new ValidationException('Impossible d\'ajouter un joueur avec statut E, A ou X');
}
```

### 10.2 Messages Toast

```typescript
// Success messages
toast.add({
  title: t('presence.player_added'),
  color: 'success',
  timeout: 3000
})

toast.add({
  title: t('presence.players_deleted', { count: deletedCount }),
  color: 'success',
  timeout: 3000
})

toast.add({
  title: t('presence.composition_copied', { count: copiedCount }),
  color: 'success',
  timeout: 3000
})

toast.add({
  title: t('presence.composition_initialized', { count: addedCount }),
  color: 'success',
  timeout: 3000
})

// Error messages
toast.add({
  title: t('common.error'),
  description: t('presence.add_player_failed'),
  color: 'error',
  timeout: 5000
})

toast.add({
  title: t('common.error'),
  description: error.message,
  color: 'error',
  timeout: 5000
})
```

### 10.3 Journalisation (kp_journal)

**Team Mode:**

| Action | Actions | Journal |
|--------|---------|---------|
| Ajouter joueur | 'Ajout titulaire' | 'Equipe : {idEquipe} - Joueur : {matric}' |
| Supprimer joueur | 'Suppression titulaire' | 'Equipe : {idEquipe} - Joueur : {matric}' |
| Éditer inline | 'Modification kp_competition_equipe_joueur' | 'Equipe : {idEquipe} - Champ : {field} - Valeur : {value}' |
| Copier composition | 'Copie composition équipe' | 'Source : {sourceCompet}-{sourceSeason} - Cible : {targetTeam}' |

**Match Mode:**

| Action | Actions | Journal |
|--------|---------|---------|
| Ajouter joueur | 'Ajout joueur' | 'Match:{idMatch} - Equipe:{teamCode} - Joueur:{matric}' |
| Supprimer joueurs | 'Suppression joueurs match' | 'joueurs : {matricList}' |
| Initialiser | 'Ajout titulaires match' | 'Equipe : {idEquipe}' |
| Vider composition | 'Suppression joueurs match' | 'Match:{idMatch} - Equipe:{teamCode} - Tous' |
| Copier vers journée | 'Copie Compo sur Journée' | 'Equipe : {idEquipe} - Journée : {idJournee}' |
| Copier vers compétition | 'Copie Compo sur Compet' | 'Equipe : {idEquipe} - Compétition : {codeCompet}' |
| Éditer inline | 'Modification kp_match_joueur' | 'Match:{idMatch} - Joueur:{matric} - Champ:{field}' |

**Insertion:**
```php
$journal = new Journal();
$journal->setDates(new \DateTime());
$journal->setUsers($user->getCode());
$journal->setActions('Ajout titulaire');
$journal->setSaisons($codeSaison);
$journal->setCompetitions($codeCompet);
$journal->setJournal("Equipe : {$idEquipe} - Joueur : {$matric}");
$em->persist($journal);
$em->flush();
```

---

## 11. Migration depuis Legacy

### 11.1 Mapping Endpoints

**Team Mode:**

| Legacy PHP | API2 Endpoint | Méthode |
|------------|---------------|---------|
| `GestionEquipeJoueur.php` (load) | `GET /admin/teams/:teamId/players` | GET |
| `GestionEquipeJoueur.php?Cmd=Add` | `POST /admin/teams/:teamId/players/add` | POST |
| `GestionEquipeJoueur.php?Cmd=Add2` | `POST /admin/teams/:teamId/players/add` | POST |
| `GestionEquipeJoueur.php?Cmd=Remove` | `DELETE /admin/teams/:teamId/players` | DELETE |
| `GestionEquipeJoueur.php?Cmd=Find` | `RechercheLicence.php` (legacy redirect) | - |
| `UpdateCellJQ.php` | `PATCH /admin/teams/:teamId/players/:matric` | PATCH |
| `CopyTeamComposition.php` | `POST /admin/teams/:teamId/players/copy` | POST |
| `GetTeamCompetitions.php` | `GET /admin/teams/:teamId/compositions` | GET |

**Match Mode:**

| Legacy PHP | API2 Endpoint | Méthode |
|------------|---------------|---------|
| `GestionMatchEquipeJoueur.php` (load) | `GET /admin/matches/:matchId/players` | GET |
| `GestionMatchEquipeJoueur.php?Cmd=AddJoueurTitulaire` | `POST /admin/matches/:matchId/players/initialize` | POST |
| `GestionMatchEquipeJoueur.php?Cmd=Add2` | `POST /admin/matches/:matchId/players/add` | POST |
| `GestionMatchEquipeJoueur.php?Cmd=Remove` | `DELETE /admin/matches/:matchId/players` | DELETE |
| `GestionMatchEquipeJoueur.php?Cmd=DelJoueurs` | `DELETE /admin/matches/:matchId/players/clear` | DELETE |
| `UpdateCellJQ.php` | `PATCH /admin/matches/:matchId/players/:matric` | PATCH |
| `GestionMatchEquipeJoueur.php?Cmd=copieCompoEquipeJournee` | `POST /admin/matches/:matchId/players/copy-to-day` | POST |
| `GestionMatchEquipeJoueur.php?Cmd=copieCompoEquipeCompet` | `POST /admin/matches/:matchId/players/copy-to-competition` | POST |

### 11.2 Liens PDF (Legacy)

Les liens PDF restent sur le backend PHP legacy car la génération PDF utilise mPDF et nécessite beaucoup de logique métier spécifique.

Les fichiers PHP legacy ont été mis à jour pour accepter les noms de paramètres utilisés par app4 en plus des anciens :
- `compet` (app4) en plus de `Compet` (legacy)
- `season` (app4) en plus de `S` (legacy)
- `team` (app4) en plus de `equipe` (legacy)

```typescript
const backendBaseUrl = useRuntimeConfig().public.backendBaseUrl

// Team Mode - app4 utilise des paramètres nommés différemment
const pdfLinks = {
  fr: `${backendBaseUrl}/admin/FeuillePresence.php?team=${teamId}&compet=${codeCompet}&season=${codeSaison}`,
  en: `${backendBaseUrl}/admin/FeuillePresenceEN.php?team=${teamId}&compet=${codeCompet}&season=${codeSaison}`,
  photo: `${backendBaseUrl}/admin/FeuillePresencePhoto.php?team=${teamId}&compet=${codeCompet}&season=${codeSaison}`,
  visa: `${backendBaseUrl}/admin/FeuillePresenceVisa.php?team=${teamId}&compet=${codeCompet}&season=${codeSaison}`,
}
```

---

## 12. Pinia Store

```typescript
// stores/presenceStore.ts
import { defineStore } from 'pinia'
import type { Player, TeamInfo, CompetitionInfo, MatchInfo } from '~/types/presence'

export const usePresenceStore = defineStore('presence', {
  state: () => ({
    // Mode detection
    mode: null as 'team' | 'match' | null,
    teamId: null as number | null,
    matchId: null as number | null,
    teamCode: null as 'A' | 'B' | null,

    // Context
    team: null as TeamInfo | null,
    competition: null as CompetitionInfo | null,
    match: null as MatchInfo | null,

    // Players
    players: [] as Player[],
    lastUpdate: null as { date: string; user: string; action: string } | null,

    // UI state
    loading: false,
    selectedPlayerIds: [] as number[],
    selectAll: false,

    // Inline editing
    editingCell: null as { matric: number; field: 'numero' | 'capitaine' } | null,
    editingValue: '',

    // Modals
    addModalOpen: false,
    copyModalOpen: false,
    copyToMatchesModalOpen: false,

    // Lock status
    isLocked: false,
  }),

  getters: {
    // Permission checks
    canEdit(state): boolean {
      const authStore = useAuthStore()
      if (state.isLocked) return false
      if (state.mode === 'team') return authStore.profile <= 8
      if (state.mode === 'match') return authStore.profile <= 9
      return false
    },

    canDelete(): boolean {
      return this.canEdit
    },

    canCopy(state): boolean {
      const authStore = useAuthStore()
      if (state.isLocked) return false
      if (state.mode === 'team') return authStore.profile <= 4
      if (state.mode === 'match') return authStore.profile <= 6
      return false
    },

    canCopyToCompetition(state): boolean {
      const authStore = useAuthStore()
      if (state.isLocked) return false
      if (state.mode === 'match') return authStore.profile <= 4
      return false
    },

    // Player filters
    activePlayers(state): Player[] {
      return state.players.filter(p => !['E', 'A', 'X'].includes(p.capitaine))
    },

    inactivePlayers(state): Player[] {
      return state.players.filter(p => ['E', 'A', 'X'].includes(p.capitaine))
    },

    // National competition detection
    isNationalCompetition(state): boolean {
      if (!state.competition) return false
      const code = state.competition.code
      return code.startsWith('N') || code.startsWith('CF')
    },
  },

  actions: {
    // Initialize
    async initTeamMode(teamId: number) {
      this.mode = 'team'
      this.teamId = teamId
      await this.loadPlayers()
    },

    async initMatchMode(matchId: number, teamCode: 'A' | 'B') {
      this.mode = 'match'
      this.matchId = matchId
      this.teamCode = teamCode
      await this.loadPlayers()
    },

    // Load data
    async loadPlayers() {
      const api = useApi()
      this.loading = true

      try {
        if (this.mode === 'team' && this.teamId) {
          const data = await api.get<TeamPlayersResponse>(`/admin/teams/${this.teamId}/players`)
          this.team = data.team
          this.competition = data.competition
          this.players = data.players
          this.lastUpdate = data.lastUpdate || null
          this.isLocked = data.competition.verrou
        } else if (this.mode === 'match' && this.matchId && this.teamCode) {
          const data = await api.get<MatchPlayersResponse>(
            `/admin/matches/${this.matchId}/players`,
            { teamCode: this.teamCode }
          )
          this.match = data.match
          this.team = data.team
          this.competition = data.competition
          this.players = data.players
          this.isLocked = data.match.validation
        }
      } catch (error) {
        console.error('Failed to load players:', error)
        throw error
      } finally {
        this.loading = false
      }
    },

    // Team Mode: Add player
    async addPlayer(data: AddPlayerFormData) {
      if (this.mode !== 'team' || !this.teamId) return

      const api = useApi()
      await api.post(`/admin/teams/${this.teamId}/players/add`, data)
      await this.loadPlayers()
    },

    // Match Mode: Initialize from team
    async initializeFromTeam() {
      if (this.mode !== 'match' || !this.matchId || !this.teamCode) return

      const api = useApi()
      const result = await api.post(`/admin/matches/${this.matchId}/players/initialize`, {
        teamCode: this.teamCode
      })
      await this.loadPlayers()

      return result.added
    },

    // Match Mode: Add player to match
    async addPlayerToMatch(data: MatchAddPlayerFormData) {
      if (this.mode !== 'match' || !this.matchId || !this.teamCode) return

      const api = useApi()
      await api.post(`/admin/matches/${this.matchId}/players/add`, {
        ...data,
        teamCode: this.teamCode
      })
      await this.loadPlayers()
    },

    // Delete players
    async removeSelectedPlayers() {
      if (this.selectedPlayerIds.length === 0) return

      const api = useApi()

      if (this.mode === 'team' && this.teamId) {
        await api.del(`/admin/teams/${this.teamId}/players`, {
          matricIds: this.selectedPlayerIds
        })
      } else if (this.mode === 'match' && this.matchId && this.teamCode) {
        await api.del(`/admin/matches/${this.matchId}/players`, {
          matricIds: this.selectedPlayerIds,
          teamCode: this.teamCode
        })
      }

      this.selectedPlayerIds = []
      this.selectAll = false
      await this.loadPlayers()
    },

    // Match Mode: Clear all
    async clearAllPlayers() {
      if (this.mode !== 'match' || !this.matchId || !this.teamCode) return

      const api = useApi()
      await api.del(`/admin/matches/${this.matchId}/players/clear`, {
        teamCode: this.teamCode
      })
      await this.loadPlayers()
    },

    // Inline edit
    async updatePlayer(matric: number, field: 'numero' | 'capitaine', value: any) {
      const api = useApi()

      if (this.mode === 'team' && this.teamId) {
        await api.patch(`/admin/teams/${this.teamId}/players/${matric}`, {
          [field]: value
        })
      } else if (this.mode === 'match' && this.matchId && this.teamCode) {
        await api.patch(`/admin/matches/${this.matchId}/players/${matric}`, {
          [field]: value,
          teamCode: this.teamCode
        })
      }

      // Update local state
      const player = this.players.find(p => p.matric === matric)
      if (player) {
        player[field] = value
      }
    },

    // Team Mode: Copy composition
    async copyComposition(data: CopyCompositionFormData) {
      if (this.mode !== 'team' || !this.teamId) return

      const api = useApi()
      const result = await api.post(`/admin/teams/${this.teamId}/players/copy`, data)
      await this.loadPlayers()

      return result.copied
    },

    // Match Mode: Copy to matches
    async copyToMatches(data: CopyToMatchesFormData) {
      if (this.mode !== 'match' || !this.matchId || !this.teamCode) return

      const api = useApi()
      const endpoint = data.scope === 'day'
        ? `/admin/matches/${this.matchId}/players/copy-to-day`
        : `/admin/matches/${this.matchId}/players/copy-to-competition`

      const result = await api.post(endpoint, {
        teamCode: this.teamCode,
        ...(data.scope === 'day' && this.match ? { journeeId: this.match.idJournee } : {})
      })

      return result.copiedToMatches
    },

    // Selection
    toggleSelectAll() {
      if (this.selectAll) {
        this.selectedPlayerIds = this.players.map(p => p.matric)
      } else {
        this.selectedPlayerIds = []
      }
    },

    toggleSelect(matric: number) {
      const index = this.selectedPlayerIds.indexOf(matric)
      if (index > -1) {
        this.selectedPlayerIds.splice(index, 1)
      } else {
        this.selectedPlayerIds.push(matric)
      }
      this.selectAll = this.selectedPlayerIds.length === this.players.length
    },
  },
})
```

---

## 13. Tests et Validation

### 13.1 Scénarios de Test - Team Mode

**1. Chargement initial**
- ✅ Affiche joueurs triés (-, C en premier, puis E, A, X)
- ✅ Affiche séparateur avant joueurs inactifs
- ✅ Affiche indicateur verrouillage si Verrou = 'O'
- ✅ Affiche dernière modification (date, utilisateur, action)
- ✅ Affiche info licence (saison, pagaie, certificat)
- ✅ Affiche surclassement (icône #S avec date)

**2. Édition inline**
- ✅ Clic sur numéro → input number (0-99)
- ✅ Clic sur capitaine → select (-, C, E, A, X)
- ✅ Validation en temps réel
- ✅ Bloqué si competition verrouillée

**3. Ajout joueur - Existant**
- ✅ Autocomplete par nom/prénom/matric
- ✅ Affichage info licence complète
- ✅ Validation pagaie + certificat si N*/CF*
- ✅ Warning si validation échoue
- ✅ Bloqué si competition verrouillée

**4. Ajout joueur - Création**
- ✅ Génération matric >= 2000000
- ✅ Vérification doublon (nom + prénom + club)
- ✅ Détection doublons en temps réel (debounced 500ms, bandeau ambre)
- ✅ Doublons cliquables → bascule sur onglet "Joueur existant" avec pré-sélection
- ✅ Saisie nom/prénom forcée en majuscules
- ✅ Champ licence ICF limité aux chiffres uniquement
- ✅ Arbitre et niveau optionnels
- ✅ Numéro ICF optionnel
- ✅ Bloqué si compétition N*/CF*
- ✅ Profil ≤ 4 requis

**5. Suppression**
- ✅ Suppression individuelle
- ✅ Sélection multiple + suppression en masse
- ✅ Confirmation avant suppression
- ✅ Bloqué si competition verrouillée

**6. Copie composition**
- ✅ Sélection saison source (actuelle + 2 précédentes)
- ✅ Sélection compétition source (même numéro équipe)
- ✅ Affichage nombre de joueurs à copier
- ✅ Suppression joueurs existants avant copie
- ✅ Journalisation
- ✅ Profil ≤ 4 requis

**7. Liens PDF**
- ✅ Feuille présence FR
- ✅ Feuille présence EN
- ✅ Feuille photo
- ✅ Feuille contrôle

### 13.2 Scénarios de Test - Match Mode

**1. Chargement initial**
- ✅ Affiche contexte match (date, heure, terrain, numéro)
- ✅ Affiche équipe (A ou B)
- ✅ Affiche joueurs filtrés (pas E/A/X si initialisé depuis équipe)
- ✅ Affiche indicateur validation si match validé

**2. Initialisation depuis équipe**
- ✅ Bouton visible si composition vide
- ✅ Copie uniquement joueurs avec statut -, C
- ✅ Exclut E, A, X
- ✅ Copie numéros depuis équipe
- ✅ Bloqué si match validé

**3. Ajout joueur**
- ✅ Liste uniquement joueurs de l'équipe
- ✅ Exclut joueurs déjà dans match
- ✅ Exclut joueurs E, A, X
- ✅ Pré-remplit numéro depuis équipe
- ✅ Statut limité à -, C, E
- ✅ Bloqué si match validé

**4. Édition inline**
- ✅ Clic sur numéro → input number
- ✅ Clic sur capitaine → select (-, C, E uniquement)
- ✅ Validation en temps réel
- ✅ Bloqué si match validé

**5. Suppression**
- ✅ Suppression individuelle
- ✅ Sélection multiple + suppression
- ✅ Vider toute la composition
- ✅ Confirmation avant suppression
- ✅ Bloqué si match validé

**6. Copie vers matchs - Même journée**
- ✅ Liste matchs de l'équipe dans même journée
- ✅ Exclut matchs déjà validés
- ✅ Sélection multiple
- ✅ Affichage nombre de matchs ciblés
- ✅ Journalisation
- ✅ Profil ≤ 6 requis

**7. Copie vers matchs - Compétition**
- ✅ Liste tous matchs de l'équipe dans compétition
- ✅ Exclut matchs déjà validés
- ✅ Sélection multiple
- ✅ Warning si nombreux matchs
- ✅ Journalisation
- ✅ Profil ≤ 4 requis

---

## 14. Fichiers Critiques

### 14.1 Frontend (App4)

```
sources/app4/
├── pages/
│   └── presence/
│       ├── team/
│       │   └── [teamId].vue              # ✅ Team composition page (implemented)
│       └── match/
│           └── [matchId]/
│               └── team/
│                   └── [teamCode].vue    # 🚧 Match composition page (stub)
├── stores/
│   └── presenceStore.ts                  # ✅ Unified presence store
├── types/
│   ├── index.ts                          # ✅ PlayerAutocomplete (shared)
│   └── presence.ts                       # ✅ TypeScript interfaces
├── components/
│   └── admin/
│       └── PlayerAutocomplete.vue        # ✅ Reusable player search (shared with RC)
└── composables/
    └── usePresencePermissions.ts         # ✅ Permission checks
```

**Note**: Les modals (ajout joueur, copie composition) sont intégrées directement dans `[teamId].vue` plutôt que dans des composants séparés. Le composant `AdminPlayerAutocomplete` est partagé entre la page Présence et la page RC.

### 14.2 Backend (API2)

```
sources/api2/src/Controller/
├── AdminPresenceController.php           # ✅ Team mode endpoints (implemented)
└── AdminOperationsController.php         # ✅ Player autocomplete (multi-word search)
```

**Note**: L'implémentation backend utilise des requêtes DBAL directes plutôt que des entités Doctrine. Les services spécialisés (validation, copie) ne sont pas nécessaires à ce stade.

### 14.3 Legacy PHP (pour référence)

```
sources/
├── admin/
│   ├── GestionEquipeJoueur.php           # Team composition (legacy)
│   ├── GestionMatchEquipeJoueur.php      # Match composition (legacy)
│   ├── CopyTeamComposition.php           # Copy composition (legacy)
│   ├── GetTeamCompetitions.php           # Get available compositions
│   ├── UpdateCellJQ.php                  # Inline editing (legacy)
│   ├── FeuillePresence.php               # PDF FR
│   ├── FeuillePresenceEN.php             # PDF EN
│   ├── FeuillePresencePhoto.php          # PDF Photo
│   └── FeuillePresenceVisa.php           # PDF Visa
├── smarty/templates/
│   ├── GestionEquipeJoueur.tpl           # Team UI (legacy)
│   └── GestionMatchEquipeJoueur.tpl      # Match UI (legacy)
└── js/
    ├── GestionEquipeJoueur.js            # Team JS (legacy)
    └── GestionMatchEquipeJoueur.js       # Match JS (legacy)
```

---

## 15. Traductions i18n

### 15.1 Clés Françaises

```json
{
  "presence": {
    "title_team": "Feuille de Présence : Équipe",
    "title_match": "Feuille de Présence : Match",
    "competition_locked": "Compétition verrouillée",
    "match_validated": "Match validé",
    "national_validation_required": "Validation nationale requise (pagaie, certificat, surclassement)",
    "search_player": "Rechercher un joueur...",
    "add_player": "Ajouter un joueur",
    "copy_from": "Copier depuis...",
    "search_license": "Recherche licence",
    "pdf_fr": "Feuille FR",
    "pdf_en": "Feuille EN",
    "initialize_from_team": "Initialiser depuis équipe",
    "clear_all": "Vider tout",
    "copy_to_matches": "Copier vers matchs",
    "inactive_players": "Joueurs inactifs (E, A, X)",
    "total_players": "{count} joueur(s)",
    "last_update": "Dernière modification",
    "add_existing_player": "Ajouter joueur existant",
    "create_new_player": "Créer nouveau joueur",
    "search_placeholder": "Nom, prénom ou numéro de licence",
    "player_not_valid": "Ce joueur n'est pas en règle pour cette compétition",
    "invalid_license_season": "Saison de licence invalide",
    "invalid_certificate": "Certificat médical manquant",
    "invalid_paddle": "Pagaie couleur invalide",
    "missing_surclassement": "Surclassement manquant",
    "status_player": "Joueur",
    "status_captain": "Capitaine",
    "status_coach": "Entraîneur (non joueur)",
    "status_referee": "Arbitre (non joueur)",
    "status_inactive": "Inactif",
    "create_not_allowed_national": "Création de joueurs non autorisée pour les compétitions nationales",
    "source_season": "Saison source",
    "source_competition": "Compétition source",
    "copy_warning": "Attention : cela supprimera les joueurs existants",
    "select_player": "Sélectionner un joueur",
    "copy_to_same_day": "Même journée",
    "copy_to_competition": "Toute la compétition",
    "no_copyable_matches": "Aucun match disponible pour copie",
    "copy_to_count": "Copie vers {count} match(s)",
    "player_added": "Joueur ajouté avec succès",
    "players_deleted": "{count} joueur(s) supprimé(s)",
    "composition_copied": "{count} joueurs copiés",
    "composition_initialized": "Composition initialisée ({count} joueurs)",
    "add_player_failed": "Impossible d'ajouter le joueur",
    "duplicate_warning": "Joueur(s) existant(s) correspondant(s) :",
    "icf_number": "Licence ICF",
    "icf_number_placeholder": "Numéro ICF",
    "sex": "Sexe",
    "birth_date": "Date de naissance",
    "referee_qualification": "Qualification arbitre",
    "referee_level": "Niveau arbitre",
    "referee_level_trainee": "Stagiaire",
    "referee_reg": "Régional",
    "referee_nat": "National",
    "referee_int": "International",
    "referee_otm": "Officiel Table de Marque",
    "referee_jo": "Jeune Officiel"
  },
  "referee": {
    "regional": "Régional",
    "interregional": "Interrégional",
    "national": "National",
    "international": "International",
    "otm": "OTM",
    "jo": "JO"
  }
}
```

---

## 16. Résumé

Cette spécification définit une page unifiée de gestion des feuilles de présence pour App4, capable de gérer à la fois :

1. **Team Mode** : Composition d'équipe pour une saison/compétition
2. **Match Mode** : Composition d'équipe pour un match spécifique

**Avantages de l'approche unifiée :**
- ✅ Réduction de duplication de code (~80% de fonctionnalités communes)
- ✅ Interface cohérente entre les deux modes
- ✅ Maintenance simplifiée (un seul store, un seul ensemble de composants)
- ✅ Expérience utilisateur améliorée (patterns familiers)

**Fonctionnalités clés :**
- Édition inline (numéro, capitaine) avec feedback visuel
- Validation automatique pour compétitions nationales (N*, CF*)
- Gestion des statuts (-, C, E, A, X) avec restrictions par mode
- Copie de compositions (entre compétitions/saisons ou vers matchs)
- Permissions basées sur profil utilisateur
- Journalisation complète des opérations
- Support mobile avec cartes responsive

**Statut d'implémentation :**

| Composant | Statut | Notes |
|-----------|--------|-------|
| Team Mode - Backend (AdminPresenceController) | ✅ Implémenté | GET/POST/PATCH/DELETE + copy + compositions |
| Team Mode - Frontend ([teamId].vue) | ✅ Implémenté | Liste, édition inline, ajout, copie, suppression, PDF |
| Team Mode - Store (presenceStore.ts) | ✅ Implémenté | Actions avec apiInstance pattern |
| Team Mode - Permissions (usePresencePermissions.ts) | ✅ Implémenté | canEdit, canDelete, canCopy, canCreate |
| Composant PlayerAutocomplete | ✅ Implémenté | Partagé entre Présence et RC |
| Player autocomplete multi-mots | ✅ Implémenté | "nom prenom" ou "prenom nom" |
| Legacy PDF (FeuillePresence*.php) | ✅ Mis à jour | Accepte paramètres app4 (team, compet, season) |
| Coaches/Referees/Inactive éditable | ✅ Implémenté | Inline editing numero + capitaine pour E, A, X |
| Copie par Numero d'équipe | ✅ Implémenté | Filtre par même Numero dans kp_competition_equipe |
| Copie = remplacement complet | ✅ Implémenté | DELETE existants puis INSERT source |
| Détection doublons création | ✅ Implémenté | Debounced 500ms, bandeau ambre, cliquable → bascule onglet existant |
| Recherche par licence ICF | ✅ Implémenté | Autocomplete + recherche licence par Reserve (ICF) |
| Affichage licence ICF | ✅ Implémenté | ICF-{num} si dispo, sinon Matric, (saison) si ancienne |
| Saisie nom/prénom majuscules | ✅ Implémenté | Conversion temps réel à la saisie |
| Champ ICF chiffres uniquement | ✅ Implémenté | Filtrage non-digits à la saisie |
| Création joueur → kp_licence + kp_arbitre | ✅ Implémenté | Insertion licence + arbitre si qualification renseignée |
| Match Mode - Backend | 🔲 À faire | Endpoints match non implémentés |
| Match Mode - Frontend | 🔲 À faire | Page stub uniquement |

**Prochaines étapes :**
1. Implémenter les endpoints Match Mode dans AdminPresenceController
2. Développer la page match/[matchId]/team/[teamCode].vue
3. Implémenter les fonctionnalités spécifiques match (initialiser, vider, copier vers matchs)
4. Tests et validation

---

**Fin de la spécification**
