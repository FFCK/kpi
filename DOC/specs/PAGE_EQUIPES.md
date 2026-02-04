# Spécification - Page Équipes

## 1. Vue d'ensemble

La page Équipes permet de gérer les équipes inscrites à une compétition : ajout (manuel ou depuis l'historique), édition des propriétés (logo, couleurs), gestion des poules et du tirage au sort, suppression, duplication depuis une autre compétition, et initialisation des titulaires.

**Route** : `/teams`

**Accès** :
- Profil ≤ 10 : Lecture seule
- Profil ≤ 6 : Édition inline (poule/tirage)
- Profil ≤ 4 : Tirage au sort, initialisation titulaires, verrouillage
- Profil ≤ 3 : Ajout/Suppression/Duplication
- Profil ≤ 2 : Édition propriétés (logo/couleurs), mise à jour logos, contrôle

**Page PHP Legacy** : `GestionEquipe.php` + `GestionEquipe.tpl` + `GestionEquipe.js`

---

## 2. Fonctionnalités

### 2.1 Sélection de compétition

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Sélecteur de compétition (filtré par contexte de travail) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Badges info compétition (type, niveau, statut) | ≤ 10 | Utile | ✅ Conserver |
| 3 | Indicateur verrouillage compétition | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | Toggle verrouillage compétition | ≤ 4 | Essentielle | ✅ Conserver |

### 2.2 Liste des équipes

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Tableau des équipes groupé par poule | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Afficher logo équipe | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Afficher couleurs équipe (color1/color2/colortext) | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | Afficher nombre de matchs | ≤ 10 | Utile | ✅ Conserver |
| 5 | Afficher club de l'équipe | ≤ 10 | Essentielle | ✅ Conserver |
| 6 | Édition inline Poule (A-Z) | ≤ 6 | Essentielle | ✅ Conserver |
| 7 | Édition inline Tirage (0-99) | ≤ 6 | Essentielle | ✅ Conserver |
| 8 | Sélection multiple (checkboxes) | ≤ 3 | Essentielle | ✅ Conserver |
| 9 | Compteur total d'équipes | ≤ 10 | Utile | ✅ Conserver |

### 2.3 Ajout d'équipes

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Création manuelle (nouveau nom + sélection club) | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Ajout depuis historique (recherche dans kp_equipe) | ≤ 3 | Essentielle | ✅ Conserver |
| 3 | Sélection multiple depuis historique | ≤ 3 | Utile | ✅ Conserver |
| 4 | Filtres cascadés CR → CD → Club pour sélection club | ≤ 3 | Utile | ✅ Conserver |
| 5 | Autocomplete recherche club (nom/code) | ≤ 3 | Essentielle | ✅ Conserver (nouveau) |
| 6 | Copie de composition joueurs (depuis compétition précédente) | ≤ 3 | Spécialisé | ✅ Conserver |
| 7 | Attribution poule et tirage lors de l'ajout | ≤ 3 | Utile | ✅ Conserver |
| 8 | Séparation historique France / International | ≤ 3 | Utile | ✅ Conserver |

### 2.4 Modification d'équipes

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Modifier logo (chemin fichier) | ≤ 2 | Essentielle | ✅ Conserver |
| 2 | Modifier couleurs (color1, color2, colortext) | ≤ 2 | Essentielle | ✅ Conserver |
| 3 | Propager couleurs aux compétitions suivantes | ≤ 2 | Spécialisé | ✅ Conserver |
| 4 | Propager couleurs aux compétitions précédentes | ≤ 2 | Spécialisé | ✅ Conserver |
| 5 | Propager couleurs à toutes les équipes du club | ≤ 2 | Spécialisé | ✅ Conserver |

### 2.5 Suppression

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Suppression individuelle | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Suppression en masse (bulk delete) | ≤ 3 | Essentielle | ✅ Conserver |
| 3 | Sélectionner tout / Désélectionner tout | ≤ 3 | Utile | ✅ Conserver |

### 2.6 Duplication

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Dupliquer équipes depuis une compétition source | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Mode "Remplacer et dupliquer" (vider puis copier) | ≤ 3 | Spécialisé | ✅ Conserver |
| 3 | Copie des compositions joueurs lors de la duplication | ≤ 3 | Spécialisé | ✅ Conserver |

### 2.7 Opérations spéciales

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Tirage au sort (attribution poule + ordre) | ≤ 4 | Essentielle | ✅ Conserver |
| 2 | Initialiser titulaires pour toute la compétition | ≤ 4 | Spécialisé | ✅ Conserver |
| 3 | Mise à jour automatique des logos (scan fichiers) | ≤ 2 | Spécialisé | ✅ Conserver |

### 2.8 Liens et documents

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Lien vers gestion joueurs (par équipe) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Feuille de présence FR (PDF) | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Feuille de présence EN (PDF) | ≤ 10 | Utile | ✅ Conserver |
| 4 | Feuille de présence par catégorie (PDF) | ≤ 2 | Spécialisé | ✅ Conserver |
| 5 | Feuille de présence photo (PDF) | ≤ 10 | Utile | ✅ Conserver |
| 6 | Fiche contrôle (PDF) | ≤ 2 | Spécialisé | ✅ Conserver |

---

## 3. Structure de la Page

```
┌─────────────────────────────────────────────────────────────────────────┐
│  AdminWorkContextSummary                                                │
│  📅 Saison: 2026 │ 🔽 Périmètre: Groupe N1H (2 compétitions) [Modifier]│
├─────────────────────────────────────────────────────────────────────────┤
│  Header : Gestion des équipes                                            │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─ Sélecteur de compétition ──────────────────────────────────────────┐│
│  │ [▼ N1H - Nationale 1 Masculine ...]  NAT  CHPT  🟢ON  🔒Verrouillé ││
│  └─────────────────────────────────────────────────────────────────────┘│
│                                                                          │
│  ┌─ Barre d'outils ───────────────────────────────────────────────────┐│
│  │ [+ Ajouter] [Dupliquer] [Init titulaires] [MAJ logos]              ││
│  │ [☑ Tout] [Supprimer sélection (3)]              Total: 12 équipes  ││
│  └─────────────────────────────────────────────────────────────────────┘│
│                                                                          │
│  ── Poule A ──────────────────────────────────────────────────────────  │
│  ┌───┬──────┬───┬─────┬──────────────────┬──────┬──────┬──────┬───────┐│
│  │ ☐ │Poule │ # │Logo │ Équipe           │Couls │ Club │Matchs│Actions││
│  ├───┼──────┼───┼─────┼──────────────────┼──────┼──────┼──────┼───────┤│
│  │ ☐ │  A   │ 1 │ 🖼  │ Acigné KP        │ ■■   │75001 │  6   │✏️👥🗑️││
│  │ ☐ │  A   │ 2 │ 🖼  │ Strasbourg ASPTT │ ■■   │67003 │  6   │✏️👥🗑️││
│  └───┴──────┴───┴─────┴──────────────────┴──────┴──────┴──────┴───────┘│
│                                                                          │
│  ── Poule B ──────────────────────────────────────────────────────────  │
│  ┌───┬──────┬───┬─────┬──────────────────┬──────┬──────┬──────┬───────┐│
│  │ ☐ │  B   │ 1 │ 🖼  │ Fontenay KP      │ ■■   │94002 │  6   │✏️👥🗑️││
│  └───┴──────┴───┴─────┴──────────────────┴──────┴──────┴──────┴───────┘│
│                                                                          │
│  TOTAL = 12 Équipes                                                      │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### 3.1 Sélecteur de compétition

Même pattern que la page Documents : dropdown `<select>` rempli depuis `workContext.contextCompetitions`, groupé par section. Affiche des badges à droite :
- Badge niveau (INT/NAT/REG) coloré
- Badge type (CHPT/CP/MULTI)
- Badge statut (ATT/ON/END) cliquable (profil ≤ 4)
- Indicateur verrouillage 🔒/🔓 cliquable (profil ≤ 4)

### 3.2 Barre d'outils

Boutons conditionnels selon profil :
- **[+ Ajouter]** (profil ≤ 3) → ouvre modal d'ajout
- **[Dupliquer]** (profil ≤ 3) → ouvre modal de duplication
- **[Init titulaires]** (profil ≤ 4) → confirmation puis action
- **[MAJ logos]** (profil ≤ 2) → confirmation puis action
- **[☑ Tout / ☐ Rien]** (profil ≤ 3) → sélection/désélection
- **[🗑️ Supprimer sélection (N)]** (profil ≤ 3) → suppression en masse
- **Total : N équipes** (lecture seule)

### 3.3 Colonnes du tableau

| Colonne | Description | Éditable | Profil |
|---------|-------------|----------|--------|
| ☐ | Checkbox sélection | - | ≤ 3 |
| Poule | Lettre de poule (A-Z) | Inline | ≤ 6 |
| # Tirage | Numéro d'ordre (0-99) | Inline | ≤ 6 |
| Logo | Image 25px du logo équipe | - | - |
| Équipe | Libellé de l'équipe | - | - |
| Couleurs | Aperçu color1/color2/colortext | - | - |
| Club | Code club | - | - |
| Matchs | Nombre de matchs joués | - | - |
| Actions | Boutons d'action | - | Variable |

### 3.4 Actions par ligne

| Action | Icône | Description | Profil |
|--------|-------|-------------|--------|
| Éditer propriétés | ✏️ | Ouvre modal édition (logo, couleurs) | ≤ 2 |
| Voir joueurs | 👥 | Navigue vers page gestion joueurs | ≤ 10 |
| PDF Présence | 📄 | Génère feuille de présence (legacy PDF) | ≤ 10 |
| PDF Contrôle | 🛡️ | Génère fiche contrôle (legacy PDF) | ≤ 2 |
| Supprimer | 🗑️ | Supprime l'équipe (confirmation requise) | ≤ 3 |

### 3.5 Groupement par poule

Les équipes sont groupées visuellement par poule avec un en-tête de séparation pour chaque poule (A, B, C...). Les équipes sans poule sont listées sous un en-tête "Sans poule". Tri au sein de chaque poule par Tirage puis Libelle.

---

## 4. Modals

### 4.1 Modal Ajout d'équipe

Deux onglets/modes dans la même modal :

#### Mode 1 : Création manuelle

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| libelle | text(30) | Oui | Non vide |
| club | autocomplete + filtres | Oui | Club existant |
| poule | text(1) | Non | A-Z majuscule |
| tirage | number | Non | 0-99 |

**Filtres club** (section dépliable) :
| Champ | Type | Description |
|-------|------|-------------|
| Comité régional | select | Liste kp_cr, filtre les CD |
| Comité départemental | select | Cascadé depuis CR, filtre les clubs |
| Club | select / autocomplete | Cascadé depuis CD, ou recherche libre |

#### Mode 2 : Depuis historique

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| Recherche équipe | autocomplete | Oui | Équipe existante dans kp_equipe |
| Poule | text(1) | Non | A-Z majuscule |
| Tirage | number | Non | 0-99 |
| Copier composition | checkbox + select | Non | Sélection compétition source |

**Liste historique** :
- Deux sections : 🇫🇷 France (code région ≠ 98) et 🌍 International (code région = 98)
- Recherche en temps réel (filtre texte)
- Sélection multiple autorisée (checkboxes)
- Non affiché pour compétitions de type POOL

### 4.2 Modal Édition propriétés équipe

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| Nom équipe | text (lecture seule) | - | - |
| Logo | text(50) | Non | Chemin fichier |
| Couleur principale (color1) | color picker | Non | Format hex |
| Couleur secondaire (color2) | color picker | Non | Format hex |
| Couleur texte (colortext) | color picker | Non | Format hex |
| Aperçu | preview | - | Rendu visuel des couleurs |

**Options de propagation** (checkboxes) :
| Option | Description |
|--------|-------------|
| Compétitions suivantes | Appliquer aux prochaines compétitions de cette équipe |
| Compétitions précédentes | Appliquer aux compétitions passées de cette équipe |
| Toutes les équipes du club | Appliquer à toutes les équipes du même club (⚠️ dangereux) |

### 4.3 Modal Duplication

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| Compétition source | select | Oui | Compétition existante de la même saison |
| Mode | radio | Oui | "Ajouter aux existantes" / "Remplacer (vider puis copier)" |
| Copier compositions | checkbox | Non | Copier aussi les joueurs |

### 4.4 Modal Confirmation suppression

Modals de confirmation standard (AdminConfirmModal) pour :
- Suppression individuelle
- Suppression en masse
- Initialisation titulaires
- Mise à jour logos
- Mode "Remplacer et dupliquer"

---

## 5. Endpoints API2

### 5.1 Lecture

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| GET | `/admin/competition-teams` | Liste des équipes d'une compétition | ≤ 10 |
| GET | `/admin/teams/search` | Autocomplete équipes historiques | ≤ 3 |
| GET | `/admin/teams/{numero}/compositions` | Compositions disponibles pour copie | ≤ 3 |
| GET | `/admin/clubs/search` | Autocomplete clubs (nom/code) | ≤ 3 |
| GET | `/admin/regional-committees` | Liste des comités régionaux | ≤ 3 |
| GET | `/admin/departmental-committees` | Liste des comités départementaux | ≤ 3 |
| GET | `/admin/clubs` | Liste des clubs (filtrée par CR/CD) | ≤ 3 |

### 5.2 Écriture

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| POST | `/admin/competition-teams` | Ajouter équipe(s) à une compétition | ≤ 3 |
| PUT | `/admin/competition-teams/{id}` | Modifier une équipe (libellé) | ≤ 3 |
| DELETE | `/admin/competition-teams/{id}` | Supprimer une équipe | ≤ 3 |
| POST | `/admin/competition-teams/bulk-delete` | Suppression en masse | ≤ 3 |
| PATCH | `/admin/competition-teams/{id}/pool-draw` | Modifier poule et tirage | ≤ 6 |
| PATCH | `/admin/competition-teams/{id}/colors` | Modifier logo et couleurs | ≤ 2 |
| POST | `/admin/competition-teams/duplicate` | Dupliquer depuis compétition source | ≤ 3 |
| POST | `/admin/competition-teams/update-logos` | Mise à jour automatique des logos | ≤ 2 |
| POST | `/admin/competition-teams/init-starters` | Initialiser les titulaires | ≤ 4 |

### 5.3 Paramètres de requête

**GET /admin/competition-teams**

| Param | Type | Requis | Description |
|-------|------|--------|-------------|
| season | string | Oui | Code saison |
| competition | string | Oui | Code compétition |

Réponse :
```json
{
  "teams": [
    {
      "id": 12345,
      "libelle": "Acigné KP",
      "codeClub": "75001",
      "clubLibelle": "CK Acigné",
      "numero": 456,
      "poule": "A",
      "tirage": 1,
      "logo": "logo-acigne.png",
      "color1": "#FF0000",
      "color2": "#0000FF",
      "colortext": "#FFFFFF",
      "nbMatchs": 6
    }
  ],
  "competition": {
    "code": "N1H",
    "libelle": "Nationale 1 Masculine",
    "codeNiveau": "NAT",
    "codeTypeclt": "CHPT",
    "statut": "ON",
    "verrou": true
  },
  "total": 12
}
```

**GET /admin/teams/search**

| Param | Type | Requis | Description |
|-------|------|--------|-------------|
| q | string | Oui | Terme de recherche (min 2 caractères) |
| limit | number | Non | Max résultats (défaut: 20) |

Réponse :
```json
[
  {
    "numero": 456,
    "libelle": "Acigné KP",
    "codeClub": "75001",
    "clubLibelle": "CK Acigné",
    "international": false
  }
]
```

**GET /admin/teams/{numero}/compositions**

| Param | Type | Requis | Description |
|-------|------|--------|-------------|
| season | string | Oui | Saison en cours |

Réponse :
```json
[
  {
    "season": "2025",
    "competition": "N1H",
    "competitionLibelle": "Nationale 1 Masculine",
    "playerCount": 12
  }
]
```

**POST /admin/competition-teams**

Body (création manuelle) :
```json
{
  "season": "2026",
  "competition": "N1H",
  "mode": "manual",
  "libelle": "Nouvelle Équipe",
  "codeClub": "75001",
  "poule": "A",
  "tirage": 5
}
```

Body (depuis historique) :
```json
{
  "season": "2026",
  "competition": "N1H",
  "mode": "history",
  "teamNumbers": [456, 789],
  "poule": "A",
  "tirage": 0,
  "copyComposition": {
    "season": "2025",
    "competition": "N1H"
  }
}
```

**POST /admin/competition-teams/duplicate**

Body :
```json
{
  "season": "2026",
  "targetCompetition": "N1H",
  "sourceCompetition": "N1H-2025",
  "sourceSeason": "2025",
  "mode": "append",
  "copyPlayers": true
}
```

**PATCH /admin/competition-teams/{id}/pool-draw**

Body :
```json
{
  "poule": "B",
  "tirage": 3
}
```

**PATCH /admin/competition-teams/{id}/colors**

Body :
```json
{
  "logo": "logo-acigne.png",
  "color1": "#FF0000",
  "color2": "#0000FF",
  "colortext": "#FFFFFF",
  "propagateNext": true,
  "propagatePrevious": false,
  "propagateClub": false
}
```

---

## 6. Schéma de données

### 6.1 Table kp_equipe (équipes historiques)

| Colonne | Type | Description |
|---------|------|-------------|
| Numero | smallint(6) | PK, auto-increment |
| Libelle | varchar(30) | Nom de l'équipe |
| Code_club | varchar(6) | FK → kp_club(Code) |
| color1 | varchar(30) | Couleur principale (hex) |
| color2 | varchar(30) | Couleur secondaire (hex) |
| colortext | varchar(30) | Couleur texte (hex) |
| logo | varchar(50) | Chemin du logo |

### 6.2 Table kp_competition_equipe (équipes par compétition)

| Colonne | Type | Description |
|---------|------|-------------|
| Id | int(10) unsigned | PK, auto-increment |
| Code_compet | varchar(12) | FK → kp_competition(Code) |
| Code_saison | char(4) | FK → kp_competition(Code_saison) |
| Libelle | varchar(40) | Nom de l'équipe dans la compétition |
| Code_club | varchar(6) | FK → kp_club(Code) |
| Numero | smallint(6) | FK → kp_equipe(Numero) |
| Poule | varchar(3) | Lettre de poule (A, B, etc.) |
| Tirage | tinyint(4) | Numéro tirage au sort |
| logo | varchar(50) | Logo de l'équipe |
| color1 | varchar(30) | Couleur principale |
| color2 | varchar(30) | Couleur secondaire |
| colortext | varchar(30) | Couleur texte |
| Id_dupli | int(11) | Référence de duplication |
| Pts, Clt, J, G, N, P, F, Plus, Moins, Diff | smallint | Stats classement (calculé) |
| PtsNiveau, CltNiveau | double/smallint | Classement ajusté |
| *_publi | smallint/double | Stats publiées (miroir des stats) |

### 6.3 Table kp_competition_equipe_joueur (joueurs par équipe)

| Colonne | Type | Description |
|---------|------|-------------|
| Id_equipe | int(10) unsigned | PK, FK → kp_competition_equipe(Id) |
| Matric | int(11) unsigned | PK, FK → kp_licence(Matric) |
| Nom | varchar(30) | Nom du joueur |
| Prenom | varchar(30) | Prénom du joueur |
| Sexe | char(1) | Genre (M/F) |
| Categ | varchar(8) | Code catégorie |
| Numero | smallint(6) | Numéro de maillot |
| Capitaine | char(1) | Capitaine ('-' = non) |

### 6.4 Table kp_club (clubs)

| Colonne | Type | Description |
|---------|------|-------------|
| Code | varchar(6) | PK, Code club |
| Libelle | varchar(100) | Nom du club |
| Officiel | char(1) | Statut officiel |
| Code_comite_dep | varchar(6) | FK → kp_cd(Code) |
| Coord | varchar(50) | Contact |
| Postal | varchar(100) | Adresse |
| www | varchar(60) | Site web |
| email | varchar(60) | Email |

### 6.5 Tables auxiliaires

- **kp_cr** : Comités régionaux (Code PK, Libelle, Region)
- **kp_cd** : Comités départementaux (Code PK, Libelle, Code_comite_reg FK → kp_cr)
- **kp_licence** : Licenciés (Matric PK, Naissance, etc.) — pour recalcul catégories
- **kp_categorie** : Catégories d'âge (Annee_deb, Annee_fin, Code_categ, Sexe)

---

## 7. Composants Vue

### 7.1 Structure des fichiers

```
sources/app4/pages/teams/
├── index.vue                        # Page principale (remplace LegacyRedirect)

sources/app4/types/
├── teams.ts                         # Types TypeScript pour les équipes
```

**Note** : La page est auto-suffisante dans `index.vue` (comme les autres pages app4 existantes). Des composants enfants pourront être extraits si la complexité le justifie.

### 7.2 Types TypeScript

```typescript
// types/teams.ts

export interface CompetitionTeam {
  id: number
  libelle: string
  codeClub: string
  clubLibelle: string
  numero: number
  poule: string
  tirage: number
  logo: string | null
  color1: string | null
  color2: string | null
  colortext: string | null
  nbMatchs: number
}

export interface CompetitionTeamInfo {
  code: string
  libelle: string
  codeNiveau: 'INT' | 'NAT' | 'REG'
  codeTypeclt: 'CHPT' | 'CP' | 'MULTI'
  statut: 'ATT' | 'ON' | 'END'
  verrou: boolean
}

export interface CompetitionTeamsResponse {
  teams: CompetitionTeam[]
  competition: CompetitionTeamInfo
  total: number
}

export interface HistoricalTeam {
  numero: number
  libelle: string
  codeClub: string
  clubLibelle: string
  international: boolean
}

export interface TeamComposition {
  season: string
  competition: string
  competitionLibelle: string
  playerCount: number
}

export interface TeamFormData {
  mode: 'manual' | 'history'
  // Manual mode
  libelle: string
  codeClub: string
  // History mode
  teamNumbers: number[]
  // Common
  poule: string
  tirage: number
  // Copy composition
  copyComposition: {
    season: string
    competition: string
  } | null
}

export interface TeamColorsFormData {
  logo: string
  color1: string
  color2: string
  colortext: string
  propagateNext: boolean
  propagatePrevious: boolean
  propagateClub: boolean
}

export interface DuplicateFormData {
  sourceCompetition: string
  sourceSeason: string
  mode: 'append' | 'replace'
  copyPlayers: boolean
}

export interface RegionalCommittee {
  code: string
  libelle: string
}

export interface DepartmentalCommittee {
  code: string
  libelle: string
  codeComiteReg: string
}

export interface Club {
  code: string
  libelle: string
  codeComiteDep: string
}
```

### 7.3 Traductions i18n

Clés à ajouter dans `fr.json` et `en.json` sous la section `teams` :

```json
{
  "teams": {
    "title": "Gestion des équipes",
    "select_competition": "Sélectionner une compétition",
    "total": "Total : {count} équipe(s)",
    "pool_header": "Poule {letter}",
    "no_pool": "Sans poule",
    "empty": "Aucune équipe inscrite dans cette compétition",
    "add": "Ajouter une équipe",
    "add_manual": "Création manuelle",
    "add_history": "Depuis l'historique",
    "duplicate": "Dupliquer",
    "init_starters": "Init. titulaires",
    "update_logos": "MAJ logos",
    "delete_selected": "Supprimer la sélection",
    "columns": {
      "poule": "Poule",
      "tirage": "Tirage",
      "logo": "Logo",
      "equipe": "Équipe",
      "couleurs": "Couleurs",
      "club": "Club",
      "matchs": "Matchs",
      "actions": "Actions"
    },
    "form": {
      "libelle": "Nom de l'équipe",
      "club": "Club",
      "poule": "Poule",
      "tirage": "Tirage",
      "search_team": "Rechercher une équipe...",
      "search_club": "Rechercher un club...",
      "france": "France",
      "international": "International",
      "copy_composition": "Copier la composition joueurs",
      "select_source": "Sélectionner la compétition source",
      "filter_cr": "Comité régional",
      "filter_cd": "Comité départemental",
      "filter_club": "Club",
      "all": "Tous"
    },
    "edit": {
      "title": "Propriétés de l'équipe",
      "logo": "Logo (chemin fichier)",
      "color1": "Couleur principale",
      "color2": "Couleur secondaire",
      "colortext": "Couleur texte",
      "preview": "Aperçu",
      "propagate_next": "Appliquer aux compétitions suivantes",
      "propagate_previous": "Appliquer aux compétitions précédentes",
      "propagate_club": "Appliquer à toutes les équipes du club"
    },
    "duplicate_modal": {
      "title": "Dupliquer les équipes",
      "source_competition": "Compétition source",
      "mode_append": "Ajouter aux équipes existantes",
      "mode_replace": "Vider et remplacer",
      "copy_players": "Copier aussi les compositions joueurs",
      "warning_replace": "Attention : toutes les équipes actuelles seront supprimées !"
    },
    "confirm_delete": "Supprimer l'équipe \"{name}\" ?",
    "confirm_delete_multiple": "Supprimer {count} équipe(s) sélectionnée(s) ?",
    "confirm_init_starters": "Initialiser les titulaires pour toute la compétition ? La compétition sera automatiquement verrouillée.",
    "confirm_update_logos": "Scanner et mettre à jour les logos de toutes les équipes ?",
    "success_added": "Équipe(s) ajoutée(s) avec succès",
    "success_updated": "Équipe modifiée avec succès",
    "success_deleted": "Équipe(s) supprimée(s) avec succès",
    "success_duplicated": "Équipes dupliquées avec succès",
    "success_init_starters": "Titulaires initialisés avec succès",
    "success_update_logos": "Logos mis à jour avec succès",
    "error_load": "Erreur lors du chargement des équipes",
    "error_save": "Erreur lors de l'enregistrement",
    "error_delete": "Erreur lors de la suppression",
    "players": "Joueurs",
    "presence_sheet": "Feuille de présence",
    "control_sheet": "Fiche contrôle",
    "locked": "Compétition verrouillée",
    "unlocked": "Compétition déverrouillée"
  }
}
```

---

## 8. Édition inline

L'édition inline des champs Poule et Tirage directement dans le tableau est une fonctionnalité clé de cette page. Implémentation :

1. **Click** sur la cellule Poule ou Tirage → la cellule passe en mode édition (input)
2. **Validation** :
   - Poule : une lettre majuscule A-Z, ou vide
   - Tirage : nombre entier 0-99
3. **Sauvegarde** : `PATCH /admin/competition-teams/{id}/pool-draw` sur blur ou Enter
4. **Annulation** : Escape restaure la valeur précédente
5. **Feedback** : toast success/error après sauvegarde

---

## 9. Documents PDF (liens legacy)

Les documents PDF sont générés par le backend legacy. La page app4 fournit des liens vers ces fichiers :

| Document | URL Legacy | Paramètres |
|----------|-----------|------------|
| Feuille de présence FR | `FeuillePresence.php` | `?equipe={id}` ou `?S={season}&Compet={code}` |
| Feuille de présence EN | `FeuillePresenceEN.php` | idem |
| Feuille de présence catégorie | `FeuillePresenceCat.php` | idem |
| Feuille de présence photo | `FeuillePresencePhoto.php` | idem |
| Fiche contrôle | `FeuillePresenceVisa.php` | idem |
| Liste des équipes | `FeuilleGroups.php` | `?S={season}&Compet={code}` |

Les URLs sont construites avec `legacyBase` (config.public.legacyBaseUrl) comme sur la page Documents.

---

## 10. Sécurité

### 10.1 Validation côté serveur

- Vérification que la compétition appartient à la saison demandée
- Vérification que l'utilisateur a accès à cette compétition (filtres)
- Code club doit exister dans kp_club
- Numéro d'équipe historique doit exister dans kp_equipe
- Poule : 1 caractère A-Z ou vide
- Tirage : entier 0-99
- Suppression impossible si l'équipe a des matchs joués (nbMatchs > 0)

### 10.2 Audit

Toutes les actions sont journalisées dans kp_journal :
- "Ajout equipe" (création)
- "Suppression equipes" (suppression)
- "Duplication equipes" (duplication)
- "Tirage au sort" (modification poule/tirage)
- "Update logo equipes" (mise à jour logos)
- "Init titulaires" (initialisation titulaires)

---

## 11. Notes de migration

### 11.1 Page joueurs (GestionEquipeJoueur.php)

Le lien "Voir joueurs" naviguera vers la future page `/players?team={id}` quand elle sera migrée. En attendant, le lien pointera vers la page legacy `GestionEquipeJoueur.php?idEquipe={id}`.

### 11.2 Compétition POOL

Le legacy supporte une compétition spéciale "POOL" pour les arbitres. L'historique des équipes n'est pas affiché pour ce type. Ce comportement est à conserver.

### 11.3 Fichiers de logo

Les logos sont stockés dans :
- `img/KIP/logo/{code}-logo.png` (clubs français)
- `img/Nations/{code}.png` (équipes nationales)

La fonctionnalité "MAJ logos" scanne ces répertoires et met à jour les chemins en base. Les fichiers de cache JSON sont générés dans `live/cache/logos/`.

### 11.4 Catégories joueurs

Lors de la copie de composition, les catégories des joueurs sont recalculées en fonction de l'année de naissance et des tranches définies dans kp_categorie. Le backend gère ce recalcul automatiquement.

---

**Document créé le** : 2026-02-04
**Statut** : 📝 Spécification prête pour implémentation
