# Spécification - Page Schéma de Compétition

## Statut : 📋 À implémenter

## 1. Vue d'ensemble

La page Schéma de compétition affiche une visualisation en lecture seule de l'organisation d'une compétition : phases de poules avec classements publiés, et phases éliminatoires avec résultats de matchs sous forme de brackets. Le nombre de colonnes s'adapte dynamiquement au nombre d'étapes (tours) de la compétition.

**Route** : `/gamedays/schema`

**Accès** :
- Profil ≤ 10 : Lecture seule (tous les utilisateurs authentifiés)

**Page PHP Legacy** : `GestionSchema.php` + `GestionSchema.tpl` + `GestionSchema.js`

**Implémentation Nuxt** : `sources/app4/pages/gamedays/schema.vue`

**Contexte de travail** : Utilise le `workContextStore` global (saison) + sélection de compétition unique (`pageCompetitionCode`, même pattern que Rankings et Teams)

**Nature** : Page en **lecture seule** — aucune opération d'écriture.

---

## 2. Fonctionnalités

### 2.1 Affichage général

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Titre de la compétition (Libelle + Soustitre2) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Badge saison | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Badge nombre total de matchs | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | Toggle affichage nombre de matchs par phase | ≤ 10 | Utile | ✅ Conserver |
| 5 | Toggle affichage horaires par phase | ≤ 10 | Utile | ✅ Conserver |
| 6 | Sélecteur de compétition unique (filtré par contexte de travail) | ≤ 10 | Essentielle | ✅ Conserver |
| 7 | Mise en surbrillance d'une équipe au survol (toutes occurrences) | ≤ 10 | Essentielle | ✅ Conserver (amélioré) |

### 2.2 Mode CP (Coupe / Tournoi)

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Colonnes par étape (nombre dynamique selon les données) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Phases de type C (Poules) : tableau classement (#, Équipes, Pts, J, +/-) | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Phases de type E (Élimination) : paires de matchs avec scores | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | Code couleur vainqueur (bleu) / perdant (gris) | ≤ 10 | Essentielle | ✅ Conserver |
| 5 | Numéro de match affiché (#125, etc.) | ≤ 10 | Essentielle | ✅ Conserver |
| 6 | Composition des poules quand pas de classement publié | ≤ 10 | Essentielle | ✅ Conserver |
| 7 | Lignes vides basées sur Nbequipes quand aucune donnée | ≤ 10 | Utile | ✅ Conserver |
| 8 | Noms d'équipes placeholder depuis les libellés de match | ≤ 10 | Essentielle | ✅ Conserver |

### 2.3 Mode CHPT (Championnat)

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Matchs groupés par journée | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | En-tête journée : lieu, département, dates | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Grille de matchs (3 colonnes desktop, 1 mobile) | ≤ 10 | Essentielle | ✅ Conserver |
| 4 | Code couleur vainqueur / perdant | ≤ 10 | Essentielle | ✅ Conserver |

### 2.4 Fonctionnalités legacy supprimées ou simplifiées

| # | Fonctionnalité legacy | Décision | Raison |
|---|----------------------|----------|--------|
| 1 | Navigation inter-compétitions (arrayNavGroup) | ❌ Supprimé | Remplacé par le sélecteur de compétition workContext |
| 2 | Filtre par événement (event ID) | ❌ Supprimé | Simplification — affichage complet |
| 3 | Filtre par étape/round | ❌ Supprimé | Simplification — affichage complet |
| 4 | Filtre par journée spécifique (J=id) | ❌ Supprimé | Rarement utilisé |

---

## 3. Structure de la Page

### 3.1 Vue Desktop — Mode CP

```
┌────────────────────────────────────────────────────────────────────────────┐
│  AdminWorkContextSummary                                                    │
│  📅 Saison: 2025 │ 🔽 Périmètre: Section 1 (8 compétitions)  [Modifier]   │
├────────────────────────────────────────────────────────────────────────────┤
│                                                                             │
│  ┌─ Sélecteur de compétition ──────────────────────────────────────────┐   │
│  │ [▼ ECM - ECA European Championships Men]   INT  CP  🟢ON           │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─ Header ────────────────────────────────────────────────────────────┐   │
│  │ ECA European Championships - Avranches (FRA) - Men                  │   │
│  │                                           [2025]  [45 Matchs]       │   │
│  │ [☑ Matchs]  [☑ Horaires]                                           │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌── Étape 1 ──┬── Étape 2 ──────┬── Étape 3 ─┬── Étape 4 ─┬── Étape 5 ─┐│
│  │             │                  │             │            │             ││
│  │ Group MA    │ Classifying 1-6  │ Repechage   │ QF         │ SF          ││
│  │ 6M 09:30   │ 15:30-17:00      │ 09:30       │ 12:30      │ 09:15       ││
│  │ ┌─────────┐│ ┌──────────────┐ │ ┌─────────┐ │ ┌────────┐ │ ┌────────┐  ││
│  │ │# Éq P J±││ │DEN Men    6  │ │ │ITA  7   │ │ │ESP  5  │ │ │ESP  3  │  ││
│  │ │1 DEN 9 8││ │POR Men    1  │ │ │NED  1   │ │ │DEN  4  │ │ │SUI  2  │  ││
│  │ │2 FRA 4 1││ │ #125         │ │ │ #128     │ │ │ #132   │ │ │ #142   │  ││
│  │ │3 NED 4 0││ │              │ │ │         │ │ │        │ │ │        │  ││
│  │ │4 BEL 0-9││ │GER Men    3  │ │ │ESP  2   │ │ │SUI  3  │ │ │ITA  3  │  ││
│  │ └─────────┘│ │SUI Men    2  │ │ │SWE  0   │ │ │POR  2  │ │ │FRA  2  │  ││
│  │             │ │ #126         │ │ │ #129     │ │ │ #133   │ │ │ #143   │  ││
│  │ Group MB    │ │              │ │             │ │        │ │          ││
│  │ 6M 10:15   │ │GBR Men    4  │ │             │ │ITA  2  │ │ Final    ││
│  │ ┌─────────┐│ │FRA Men    3  │ │             │ │GBR  1  │ │ 14:30    ││
│  │ │# Éq P J±││ │ #127         │ │             │ │ #134   │ │ ┌──────┐ ││
│  │ │1 GER 9 +││ └──────────────┘ │             │ │        │ │ │ESP  3│ ││
│  │ │2 POR 6 -││                  │             │ │FRA  4  │ │ │ITA  2│ ││
│  │ │3 ESP 1 -││ Group MD         │             │ │GER  0  │ │ │ #145 │ ││
│  │ │4 POL 1 -││ 3M 12:30-17:45  │             │ │ #135   │ │ └──────┘ ││
│  │ └─────────┘│ ┌─────────┐     │             │          │ │          ││
│  │ ...         │ │# Éq P J±│     │             │ Class9-12│ │ 3rd place││
│  └─────────────┴──────────────────┴─────────────┴────────────┴───────────┘│
│                                                                             │
└────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Vue Desktop — Mode CHPT

```
┌────────────────────────────────────────────────────────────────────────────┐
│  AdminWorkContextSummary                                                    │
├────────────────────────────────────────────────────────────────────────────┤
│  ┌─ Sélecteur de compétition ──────────────────────────────────────────┐   │
│  │ [▼ N1H - Nationale 1 Hommes]   NAT  CHPT  🟢ON                     │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─ Header ────────────────────────────────────────────────────────────┐   │
│  │ Nationale 1 Hommes                            [2025]  [20 Matchs]   │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─ Journée 1 ────────────────────────────────────────────────────────┐   │
│  │ Paris (75) 01/03/2025 - 02/03/2025                                  │   │
│  │ ┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐      │   │
│  │ │ TeamA  3  1 TeamB │ │ TeamC  2  0 TeamD│ │ TeamE  1  1 TeamF│      │   │
│  │ └──────────────────┘ └──────────────────┘ └──────────────────┘      │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
│  ┌─ Journée 2 ────────────────────────────────────────────────────────┐   │
│  │ Lyon (69) 15/03/2025 - 16/03/2025                                   │   │
│  │ ┌──────────────────┐ ┌──────────────────┐ ┌──────────────────┐      │   │
│  │ │ ...              │ │ ...              │ │ ...              │      │   │
│  │ └──────────────────┘ └──────────────────┘ └──────────────────┘      │   │
│  └─────────────────────────────────────────────────────────────────────┘   │
│                                                                             │
└────────────────────────────────────────────────────────────────────────────┘
```

### 3.3 Vue Mobile

**Mode CP** : Conteneur avec scroll horizontal pour les colonnes d'étapes. Hauteur fixe avec overflow-x auto.

**Mode CHPT** : Matchs empilés en 1 colonne (col-xs-12).

```
┌───────────────────────────────────────┐
│  📅 2025 │ Section 1 │ [Modifier]      │
├───────────────────────────────────────┤
│  [▼ ECM - ECA European ...]  CP 🟢ON  │
├───────────────────────────────────────┤
│  ECA European Championships Men        │
│  [2025] [45 Matchs]                    │
│  [☑ Matchs] [☑ Horaires]              │
├───────────────────────────────────────┤
│  ← Scroll horizontal →                │
│  ┌──────┬──────┬──────┬──────┬──────┐ │
│  │Étape1│Étape2│Étape3│Étape4│Étape5│ │
│  │ ...  │ ...  │ ...  │ ...  │ ...  │ │
│  └──────┴──────┴──────┴──────┴──────┘ │
└───────────────────────────────────────┘
```

---

## 4. Détail des données affichées

### 4.1 Mode CP — Phases de type C (Poules/Classement)

Chaque phase de type C affiche un mini-tableau de classement :

| Colonne | Description |
|---------|-------------|
| # | Classement (`Clt_publi`) |
| Équipes | Libellé de l'équipe |
| Pts | Points publiés (`Pts_publi / 100`) |
| J | Matchs joués publiés (`J_publi`) |
| +/- | Différence publiée (`Diff_publi`) |

**En-tête de phase** :
- Nom de la phase
- Nombre de matchs (optionnel, si toggle activé)
- Plage horaire debut-fin (optionnel, si toggle activé)

**Fallback quand pas de classement publié** :
1. Si des matchs existent → afficher la composition des poules (équipes extraites des matchs, triées par tirage)
2. Si pas de matchs → afficher N lignes vides (N = `Nbequipes` de la journée)

### 4.2 Mode CP — Phases de type E (Élimination)

Chaque phase de type E affiche des paires de matchs :

```
        #125
[Vainqueur  score]  ← fond bleu
[Perdant    score]  ← fond gris
```

- Le vainqueur (score le plus élevé) est affiché en premier avec un fond bleu
- Le perdant est affiché en second avec un fond gris
- En cas d'égalité, les deux équipes sont en gris dans l'ordre A/B
- Le numéro de match (`Numero_ordre`) est affiché à gauche de la paire
- Les scores non validés (`Validation != 'O'`) ne sont pas affichés

### 4.3 Mode CHPT — Matchs par journée

Chaque journée affiche :
- **En-tête** : Lieu (Département) Date_debut - Date_fin
- **Grille de matchs** : 3 colonnes desktop, 2 tablette, 1 mobile

Chaque match :
```
[ÉquipeA  scoreA    scoreB  ÉquipeB]
```
- Le vainqueur est mis en bleu, le perdant en gris
- Égalité : les deux en gris

### 4.4 Colonnes dynamiques (Mode CP)

Le nombre de colonnes correspond au nombre de valeurs distinctes de `Etape` dans les phases.

**Implémentation** : CSS Grid avec `grid-template-columns: repeat(N, 1fr)` où N = nombre d'étapes.

Les phases sont positionnées dans la colonne correspondant à leur `Etape`. Au sein d'une colonne, les phases sont ordonnées par `Niveau DESC`, `Date_debut DESC`, `Phase ASC`.

### 4.5 Mise en surbrillance des équipes

Au survol d'un nom d'équipe, toutes les occurrences de cette équipe sur la page sont mises en surbrillance (fond coloré distinct). Implémentation via un état réactif Vue `hoveredTeam` et des classes CSS conditionnelles.

### 4.6 Noms d'équipes placeholder

Quand `Id_equipeA <= 1` ou `Id_equipeB <= 1` (équipe non encore déterminée), le libellé du match (`kp_match.Libelle`) est parsé pour extraire les noms placeholder (ex: "Winner QF1 vs Winner QF2"). Ce parsing est effectué côté serveur dans l'API.

---

## 5. Sélecteur de compétition

**Composant** : `<AdminCompetitionSingleSelect />` (partagé avec Rankings, Équipes et Documents)

Le sélecteur :
- Affiche les compétitions disponibles depuis le contexte de travail (`workContext.competitionCodes`)
- Auto-sélectionne la première compétition si aucune sélection
- Persiste la sélection en localStorage (`kpi_admin_work_page_competition`)
- La sélection est partagée entre les pages Rankings, Équipes, Documents, et Schéma

Affiche des badges à droite :
- Badge niveau (INT/NAT/REG) coloré
- Badge type (CHPT/CP)
- Badge statut (ATT/ON/END)

---

## 6. Composants Vue

```
sources/app4/pages/gamedays/
  schema.vue                        # Page principale

sources/app4/components/schema/
  SchemaHeader.vue                  # Titre compétition, badges, toggles
  SchemaCpLayout.vue                # Layout CP en colonnes par étape
  SchemaCpPoolTable.vue             # Tableau de classement d'une poule (Type C)
  SchemaCpBracketMatch.vue          # Paire de match éliminatoire (Type E)
  SchemaChptLayout.vue              # Layout CHPT par journées
  SchemaChptGameday.vue             # Carte d'une journée avec grille de matchs
  SchemaMatchResult.vue             # Affichage d'un résultat de match individuel
```

---

## 7. Types TypeScript

```typescript
// types/schema.ts

export interface SchemaResponse {
  competition: SchemaCompetition
  stages: number                       // Nombre d'étapes distinctes
  totalMatches: number
  phases: SchemaPhase[]
}

export interface SchemaCompetition {
  code: string
  season: string
  libelle: string
  soustitre: string | null
  soustitre2: string | null
  codeTypeclt: 'CHPT' | 'CP'
  codeNiveau: 'INT' | 'NAT' | 'REG'
  codeRef: string
  titreActif: boolean
  qualifies: number
  elimines: number
}

export interface SchemaPhase {
  idJournee: number
  phase: string
  etape: number
  niveau: number
  type: 'C' | 'E'                     // C = classement/poule, E = élimination
  nbequipes: number
  dateDebut: string | null
  dateFin: string | null
  lieu: string | null
  departement: string | null
  nbMatchs: number
  startTime: string | null            // Heure du premier match
  endTime: string | null              // Heure du dernier match
  ranking: SchemaPhaseTeam[] | null   // Null si pas de classement publié
  poolTeams: SchemaPoolTeam[] | null  // Composition poule (fallback si pas de ranking)
  matches: SchemaMatch[]
}

export interface SchemaPhaseTeam {
  id: number
  libelle: string
  codeClub: string
  clt: number
  pts: number                          // Déjà divisé par 100
  j: number
  diff: number
}

export interface SchemaPoolTeam {
  id: number
  libelle: string
  tirage: number
}

export interface SchemaMatch {
  id: number
  numeroOrdre: number | null
  equipeA: string
  equipeB: string
  scoreA: string | null                // Null si non validé
  scoreB: string | null                // Null si non validé
  idEquipeA: number
  idEquipeB: number
}
```

---

## 8. Endpoint API2

### 8.1 Lecture du schéma

```
GET /admin/schema
```

**Profil** : ≤ 10

**Query Parameters** :

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `season` | string | Oui | Code saison |
| `competition` | string | Oui | Code compétition |

**Réponse** : `200 OK`

```json
{
  "competition": {
    "code": "ECM",
    "season": "2025",
    "libelle": "ECA European Championships Men",
    "soustitre": null,
    "soustitre2": "Avranches (FRA)",
    "codeTypeclt": "CP",
    "codeNiveau": "INT",
    "codeRef": "ECM",
    "titreActif": true,
    "qualifies": 3,
    "elimines": 0
  },
  "stages": 6,
  "totalMatches": 45,
  "phases": [
    {
      "idJournee": 1234,
      "phase": "Group MA",
      "etape": 1,
      "niveau": 5,
      "type": "C",
      "nbequipes": 4,
      "dateDebut": "2025-05-15",
      "dateFin": "2025-05-18",
      "lieu": "Avranches",
      "departement": "50",
      "nbMatchs": 6,
      "startTime": "09:30",
      "endTime": "08:45",
      "ranking": [
        {
          "id": 100,
          "libelle": "DEN Men",
          "codeClub": "DEN01",
          "clt": 1,
          "pts": 9,
          "j": 3,
          "diff": 8
        }
      ],
      "poolTeams": null,
      "matches": [
        {
          "id": 500,
          "numeroOrdre": 1,
          "equipeA": "DEN Men",
          "equipeB": "FRA Men",
          "scoreA": "3",
          "scoreB": "1",
          "idEquipeA": 100,
          "idEquipeB": 101
        }
      ]
    },
    {
      "idJournee": 1240,
      "phase": "Semi-final",
      "etape": 5,
      "niveau": 2,
      "type": "E",
      "nbequipes": 4,
      "dateDebut": "2025-05-20",
      "dateFin": "2025-05-20",
      "lieu": "Avranches",
      "departement": "50",
      "nbMatchs": 2,
      "startTime": "09:15",
      "endTime": "09:15",
      "ranking": null,
      "poolTeams": null,
      "matches": [
        {
          "id": 642,
          "numeroOrdre": 142,
          "equipeA": "ESP Men",
          "equipeB": "SUI Men",
          "scoreA": "3",
          "scoreB": "2",
          "idEquipeA": 110,
          "idEquipeB": 115
        }
      ]
    }
  ]
}
```

### 8.2 Logique de l'endpoint

L'endpoint agrège 5 requêtes SQL en une seule réponse (basées sur les requêtes de `GestionSchema.php`) :

1. **Infos compétition** : `kp_competition` pour le code, libellé, type, niveau, etc.
2. **Journées/phases** : `kp_journee` avec `COUNT(kp_match)`, excluant les phases 'Break'/'Pause' et celles sans matchs. Triées par `Etape ASC, Niveau DESC, Date_debut DESC, Phase ASC`.
3. **Classement publié par phase** : `kp_competition_equipe_journee` avec champs `_publi`, joint à `kp_competition_equipe` et `kp_club`. Trié par `Clt_publi ASC, Diff_publi DESC`.
4. **Matchs par phase** : `kp_match` avec équipes (LEFT JOIN `kp_competition_equipe`). Scores masqués si `Validation != 'O'`. Trié par `Date_match, Heure_match, Numero_ordre`.
5. **Composition des poules** (fallback) : Équipes extraites des matchs des phases de type C, triées par `Tirage`.

**Heures début/fin** : `startTime` = heure du premier match, `endTime` = heure du dernier match de la phase.

**Noms placeholder** : Quand `Id_equipeA <= 1` ou `Id_equipeB <= 1`, le libellé du match est parsé côté serveur pour générer les noms d'équipes (même logique que `utyEquipesAffectAuto()`).

**Points** : Les `Pts_publi` sont retournés divisés par 100 (l'API fait la division).

---

## 9. Schéma Base de Données

### Tables lues (lecture seule)

#### kp_competition (champs utilisés)

| Colonne | Type | Description |
|---------|------|-------------|
| `Code` | varchar(12) | Code compétition |
| `Code_saison` | char(4) | Code saison |
| `Libelle` | varchar(80) | Nom de la compétition |
| `Soustitre` | varchar(80) | Sous-titre |
| `Soustitre2` | varchar(80) | Sous-titre 2 (lieu) |
| `Code_typeclt` | varchar(8) | Type : `CHPT`, `CP` |
| `Code_niveau` | char(3) | Niveau : `INT`, `NAT`, `REG` |
| `Code_ref` | varchar(10) | Groupe de référence |
| `Titre_actif` | char(1) | Afficher le titre (`O`/`N`) |
| `Qualifies` | int | Nombre de qualifiés |
| `Elimines` | int | Nombre d'éliminés |

#### kp_journee (champs utilisés)

| Colonne | Type | Description |
|---------|------|-------------|
| `Id` | int | ID de la journée |
| `Code_competition` | varchar(12) | Code compétition |
| `Code_saison` | char(4) | Code saison |
| `Phase` | varchar(30) | Nom de la phase |
| `Etape` | smallint | Numéro d'étape (tour) |
| `Niveau` | smallint | Niveau de la phase |
| `Type` | char(1) | `C` = classement, `E` = élimination |
| `Nbequipes` | smallint | Nombre d'équipes attendues |
| `Date_debut` | date | Date de début |
| `Date_fin` | date | Date de fin |
| `Lieu` | varchar(40) | Lieu |
| `Departement` | varchar(3) | Département/Pays |

#### kp_competition_equipe_journee (champs `_publi` utilisés)

| Colonne | Type | Description |
|---------|------|-------------|
| `Id` | int | FK vers `kp_competition_equipe.Id` |
| `Id_journee` | int | FK vers `kp_journee.Id` |
| `Clt_publi` | smallint | Classement publié |
| `Pts_publi` | smallint | Points publiés (× 100) |
| `J_publi` | smallint | Matchs joués publiés |
| `Diff_publi` | smallint | Différence publiée |

#### kp_match (champs utilisés)

| Colonne | Type | Description |
|---------|------|-------------|
| `Id` | int | ID du match |
| `Id_journee` | int | FK vers `kp_journee.Id` |
| `Numero_ordre` | int | Numéro d'ordre du match |
| `Date_match` | date | Date du match |
| `Heure_match` | varchar(6) | Heure de début |
| `Id_equipeA` | int | FK vers `kp_competition_equipe.Id` |
| `Id_equipeB` | int | FK vers `kp_competition_equipe.Id` |
| `ScoreA` | varchar(4) | Score équipe A |
| `ScoreB` | varchar(4) | Score équipe B |
| `Validation` | char(1) | `O` = validé |
| `Libelle` | varchar(30) | Libellé (pour noms placeholder) |

#### kp_competition_equipe (champs utilisés)

| Colonne | Type | Description |
|---------|------|-------------|
| `Id` | int | ID de l'équipe compétition |
| `Libelle` | varchar(40) | Nom de l'équipe |
| `Code_club` | varchar(6) | Code du club |
| `Tirage` | tinyint | Numéro de tirage (ordre dans la poule) |

---

## 10. Traductions i18n

### Français (fr.json)

```json
{
  "schema": {
    "title": "Schéma de compétition",
    "matches_count": "{count} Match | {count} Matchs",
    "show_match_count": "Matchs",
    "show_time_intervals": "Horaires",
    "no_competition": "Sélectionnez une compétition pour afficher le schéma",
    "no_data": "Aucune donnée disponible pour cette compétition",
    "table": {
      "rank": "#",
      "teams": "Équipes",
      "pts": "Pts",
      "played": "J",
      "diff": "+/-"
    }
  }
}
```

### Anglais (en.json)

```json
{
  "schema": {
    "title": "Competition schema",
    "matches_count": "{count} Match | {count} Matches",
    "show_match_count": "Matches",
    "show_time_intervals": "Time slots",
    "no_competition": "Select a competition to display the schema",
    "no_data": "No data available for this competition",
    "table": {
      "rank": "#",
      "teams": "Teams",
      "pts": "Pts",
      "played": "P",
      "diff": "+/-"
    }
  }
}
```

---

## 11. Sécurité

### Contrôles d'accès

| Action | Profil requis |
|--------|---------------|
| Consulter le schéma | ≤ 10 |

### Validations backend

- Vérification JWT (middleware auth)
- Vérification que l'utilisateur a accès à la compétition demandée (`Filtre_competition` du `kp_user`)
- Aucune opération d'écriture — page 100% lecture seule

---

## 12. Notes de migration

### 12.1 Différences avec le legacy

| Aspect | Legacy (PHP) | App4 (Nuxt) |
|--------|-------------|-------------|
| Sélection compétition | Variables de session + paramètres URL | `workContextStore.pageCompetitionCode` |
| Navigation inter-compétitions | `arrayNavGroup` custom | Sélecteur de compétition standard (déjà existant) |
| Filtres événement/round | Paramètres URL | Supprimés (affichage complet) |
| Filtre journée (J=id) | Paramètre URL | Supprimé |
| Surbrillance équipe | jQuery `.mouseenter()` + `btn-danger` | Vue reactif `hoveredTeam` + classes CSS |
| Largeur colonnes | Bootstrap `col-md-{12/N}` + cas spécial 5 étapes | CSS Grid `repeat(N, 1fr)` |
| Responsive | Non | Scroll horizontal mobile (CP) |
| Ouverture | Nouvelle fenêtre (`target="_blank"`) | Navigation in-app dans le layout admin |
| Données | 5 requêtes SQL séparées | 1 endpoint API2 agrégé |

### 12.2 Accès depuis la page Journées/Phases

La page `gamedays/index.vue` doit ajouter un lien/bouton vers `/gamedays/schema`. Ce lien est visible quand au moins une compétition est sélectionnée.

**Emplacement** : Dans la barre d'outils, à côté des autres liens (Matchs, etc.).

### 12.3 Lien avec les specs existantes

- **PAGE_JOURNEES_PHASES.md** : Feature #19 "Lien Schéma de compétition" — passe de ⏳ Différé à ✅ Conserver (navigation vers `/gamedays/schema`)
- **PAGE_CLASSEMENT.md** : Les données affichées utilisent les mêmes champs `_publi` du classement publié

---

**Document créé le** : 2026-02-24
**Dernière mise à jour** : 2026-02-24
**Statut** : 📋 À implémenter (backend API2 + frontend App4)
**Auteur** : Claude Code
