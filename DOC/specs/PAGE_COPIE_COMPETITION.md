# Spécification - Page Recherche / Copie de Système de Jeu

## Statut : ✅ Implémenté

## 1. Vue d'ensemble

La page Recherche / Copie de système de jeu permet de rechercher des schémas de compétitions existantes (structures de journées/phases et matchs), de les consulter, et de copier leur structure vers une compétition cible. L'objectif est de réutiliser des formats de compétitions éprouvés (nombre de poules, phases éliminatoires, enchaînement des matchs) sans avoir à tout recréer manuellement.

**Route** : `/competitions/copy`

**Accès** :
- Profil ≤ 3 : Accès complet (recherche, consultation, copie)

**Page PHP Legacy** : `GestionCopieCompetition.php` + `GestionCopieCompetition.tpl` + `GestionCopieCompetition.js`

**Implémentation Nuxt** : `sources/app4/pages/competitions/copy.vue`

**Contexte de travail** : Utilise le `workContextStore` global (saison) pour pré-sélectionner la saison destination

**Point d'entrée** : Accessible depuis la page Compétitions (`/competitions`) via un bouton "Copier une structure"

---

## 2. Fonctionnalités

### 2.1 Recherche de schémas

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Filtre par nombre d'équipes (champ numérique) | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Filtre par type de compétition (CHPT / CP / Tous) | ≤ 3 | Essentielle | ✅ Nouveau (legacy = CP uniquement) |
| 3 | Tri par saison (desc) ou par nombre de matchs (desc) | ≤ 3 | Essentielle | ✅ Conserver |
| 4 | Bouton "Rechercher" pour lancer la recherche | ≤ 3 | Essentielle | ✅ Conserver |

### 2.2 Tableau des résultats

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Colonne Saison | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Colonne Code compétition | ≤ 3 | Essentielle | ✅ Conserver |
| 3 | Colonne Niveau (INT/NAT/REG) | ≤ 3 | Essentielle | ✅ Conserver |
| 4 | Colonne Libellé (titre principal ou sous-titre selon `Titre_actif`) | ≤ 3 | Essentielle | ✅ Conserver |
| 5 | Colonne Nb terrains (`COUNT(DISTINCT Terrain)` des matchs) | ≤ 3 | Utile | ✅ Nouveau |
| 6 | Colonne Nb tours (`COUNT(DISTINCT Etape)` des journées) | ≤ 3 | Utile | ✅ Nouveau |
| 7 | Colonne Nb équipes | ≤ 3 | Essentielle | ✅ Conserver |
| 8 | Colonne Nb matchs | ≤ 3 | Essentielle | ✅ Conserver |
| 9 | Colonne Matchs encodés (O/N) : présence de libellés entre crochets `[...]` | ≤ 3 | Utile | ✅ Nouveau |
| 10 | Colonne Commentaires (icône info + édition via modale) | ≤ 3 | Utile | ✅ Amélioré |
| 11 | Lien vers le schéma (`/gamedays/schema?competition=X&season=Y`) | ≤ 3 | Essentielle | ✅ Conserver |
| 12 | Bouton "Basculer" vers la compétition (avec confirm JS) | ≤ 3 | Essentielle | ✅ Amélioré (confirmation) |
| 13 | Bouton "Copier vers" par ligne (ouvre le formulaire de copie) | ≤ 3 | Essentielle | ✅ Nouveau |

### 2.3 Édition des commentaires

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Clic sur l'icône commentaire ouvre une modale d'édition | ≤ 3 | Utile | ✅ Nouveau |
| 2 | Champ textarea dans la modale | ≤ 3 | Utile | ✅ Nouveau |
| 3 | Sauvegarde via API PATCH | ≤ 3 | Utile | ✅ Nouveau |

### 2.4 Formulaire de copie

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Modale de copie ouverte par le bouton "Copier vers" d'une ligne | ≤ 3 | Essentielle | ✅ Nouveau (legacy = panneau latéral fixe) |
| 2 | Affichage récapitulatif de l'origine (saison, compétition, type, nb équipes, nb matchs) | ≤ 3 | Essentielle | ✅ Conserver |
| 3 | Affichage structure des journées/phases de l'origine | ≤ 3 | Essentielle | ✅ Conserver |
| 4 | Sélection saison destination (dropdown) | ≤ 3 | Essentielle | ✅ Conserver |
| 5 | Sélection compétition destination (dropdown groupé par section) | ≤ 3 | Essentielle | ✅ Conserver |
| 6 | Affichage récapitulatif de la destination (type, nb équipes, qualifiés, éliminés) | ≤ 3 | Essentielle | ✅ Conserver |
| 7 | Champ Date début (pré-rempli depuis l'origine, modifiable, `%` = individuel) | ≤ 3 | Essentielle | ✅ Conserver |
| 8 | Champ Date fin (pré-rempli depuis l'origine, modifiable, `%` = individuel) | ≤ 3 | Essentielle | ✅ Conserver |
| 9 | Champ Lieu (pré-rempli, modifiable, `%` = individuel) | ≤ 3 | Essentielle | ✅ Conserver |
| 10 | Champ Département (pré-rempli, modifiable, `%` = individuel) | ≤ 3 | Essentielle | ✅ Conserver |
| 11 | Champ Nom journée (pré-rempli, modifiable, `%` = individuel) | ≤ 3 | Essentielle | ✅ Conserver |
| 12 | Champ Plan d'eau (pré-rempli, modifiable, `%` = individuel) | ≤ 3 | Utile | ✅ Conserver |
| 13 | Champ Club organisateur (pré-rempli, modifiable, `%` = individuel) | ≤ 3 | Utile | ✅ Conserver |
| 14 | Champ Responsable R1 (pré-rempli, modifiable, `%` = individuel) | ≤ 3 | Utile | ✅ Conserver |
| 15 | Champ Responsable inscriptions (pré-rempli, modifiable, `%` = individuel) | ≤ 3 | Utile | ✅ Conserver |
| 16 | Champ Délégué fédéral (pré-rempli, modifiable, `%` = individuel) | ≤ 3 | Utile | ✅ Conserver |
| 17 | Option "Encoder les équipes au 1er tour" (checkbox O/N) | ≤ 3 | Essentielle | ✅ Conserver |
| 18 | Bouton "Dupliquer la structure" avec confirmation | ≤ 3 | Essentielle | ✅ Conserver |

### 2.5 Fonctionnalités legacy supprimées ou simplifiées

| # | Fonctionnalité legacy | Décision | Raison |
|---|----------------------|----------|--------|
| 1 | Panneau gauche/droite séparés sur la même page | ❌ Supprimé | Remplacé par tableau + modale de copie |
| 2 | Sélection origine via dropdown (panneau gauche) | ❌ Supprimé | L'origine est sélectionnée via le bouton "Copier vers" dans le tableau |
| 3 | Variables de session pour conserver l'état | ❌ Supprimé | État local Vue + URL query params |
| 4 | Caractère `%` pour reprendre les valeurs individuelles | ✅ Modernisé | Remplacé par un toggle/checkbox "Valeur commune" par champ (vide = reprendre les valeurs individuelles) |

---

## 3. Structure de la Page

### 3.1 Vue Desktop

```
┌────────────────────────────────────────────────────────────────────────────────┐
│  AdminWorkContextSummary                                                        │
│  📅 Saison: 2025 │ 🔽 Périmètre: Toutes compétitions         [Modifier]        │
├────────────────────────────────────────────────────────────────────────────────┤
│                                                                                 │
│  ┌─ PageHeader ──────────────────────────────────────────────────────────────┐  │
│  │ Recherche / Copie de système de jeu                                       │  │
│  └───────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  ┌─ Filtres de recherche ────────────────────────────────────────────────────┐  │
│  │ Nombre d'équipes: [  10  ]   Type: [▼ Tous / CHPT / CP]                  │  │
│  │ Trier par: [▼ Saison / Nb matchs]                          [Rechercher]   │  │
│  └───────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
│  ┌─ Tableau des résultats ───────────────────────────────────────────────────┐  │
│  │ (Certains schémas anciens ne sont peut-être pas totalement encodés)       │  │
│  │                                                                            │  │
│  │ Saison │ Code  │ Niv. │ Libellé         │ Terrains │ Tours │ Éq. │ Matchs │  │
│  │ ───────┼───────┼──────┼─────────────────┼──────────┼───────┼─────┼────────│  │
│  │ 2025   │ N3E   │ NAT  │ Nationale 3     │ 4        │ 2     │ 10  │ 33     │  │
│  │        │       │      │ 1/2 Finales N   │          │       │     │        │  │
│  │ ───────┼───────┼──────┼─────────────────┼──────────┼───────┼─────┼────────│  │
│  │ 2024   │ ECA1W │ INT  │ ECA Cup         │ 3        │ 5     │ 10  │ 30     │  │
│  │        │       │      │ Saint-Omer (62) │          │       │     │        │  │
│  │        │       │      │ Women           │          │       │     │        │  │
│  │                                                                            │  │
│  │ (suite : Encodés, Info, Basculer, Schéma, Copier vers)                    │  │
│  └───────────────────────────────────────────────────────────────────────────┘  │
│                                                                                 │
└────────────────────────────────────────────────────────────────────────────────┘
```

### 3.2 Colonnes du tableau

```
│ Saison │ Code │ Niv. │ Libellé            │ Terrains │ Tours │ Éq. │ Matchs │ Encodés │ Info │ Actions      │
│────────┼──────┼──────┼────────────────────┼──────────┼───────┼─────┼────────┼─────────┼──────┼──────────────│
│ 2025   │ N3E  │ NAT  │ Nationale 3        │ 4        │ 2     │ 10  │ 33     │  ✅     │ 💬   │ 👁 📋 ➡️     │
│        │      │      │ 1/2 Finales Nord   │          │       │     │        │         │      │              │

Légende Actions :
  👁  = Basculer vers (confirm JS, puis navigation /competitions avec changement de workContext)
  📋  = Voir le schéma (/gamedays/schema?competition=X&season=Y, nouvel onglet)
  ➡️  = Copier vers (ouvre la modale de copie)
```

### 3.3 Modale de copie

```
┌────────────────────────────────────────────────────────────────────────────┐
│  Copier la structure de compétition                               [✕]     │
├────────────────────────────────────────────────────────────────────────────┤
│                                                                            │
│  ┌─ Origine (lecture seule) ─────────────────────────────────────────────┐ │
│  │ 2025 │ MCP-Test Local │ CP │ 8 équipes │ 36 matchs                   │ │
│  │                                                                       │ │
│  │ Phases : Group UW | Group UX > QF > Class. 5-8 > 7e place >          │ │
│  │          5e place > SF > 3e place > Finale                            │ │
│  └───────────────────────────────────────────────────────────────────────┘ │
│                                                                            │
│  ┌─ Destination ─────────────────────────────────────────────────────────┐ │
│  │ Saison    : [▼ 2025                      ]                           │ │
│  │ Compétition: [▼ MCP-Test Local - Bac à sable  ]                      │ │
│  │                                                                       │ │
│  │ Type: CP │ Nb équipes: 8 │ Qualifiées: 3 │ Éliminées: 0              │ │
│  └───────────────────────────────────────────────────────────────────────┘ │
│                                                                            │
│  ┌─ Valeurs communes (appliquées à toutes les journées/phases) ──────────┐│
│  │ ℹ️ Laisser vide pour reprendre les valeurs individuelles de chaque     ││
│  │    journée de la compétition d'origine                                 ││
│  │                                                                        ││
│  │ Paramètres calendrier public :                                         ││
│  │ Date début   : [10/09/2025 📅]    Date fin : [13/09/2025 📅]          ││
│  │ Lieu         : [Avranches    ]    Dpt/Pays : [FRA]                    ││
│  │ Nom journée  : [B5_ECA European Championships - U21 Women        ]    ││
│  │ Plan d'eau   : [Stade Nautique de l'Écoparc                      ]    ││
│  │                                                                        ││
│  │ Responsables :                                                         ││
│  │ Organisateur : [CANOE CLUB D'AVRANCHES ]                              ││
│  │ Responsable R1: [Pierre MARTENS (431838)]                             ││
│  │ Resp. insc.  : [Lorrie DELATTRE, Adrien HUREL]                       ││
│  │ Délégué      : [Alberto BARONI (2000220)]                             ││
│  └────────────────────────────────────────────────────────────────────────┘│
│                                                                            │
│  ☐ Encoder les équipes au premier tour (préparer le tirage au sort)       │
│    (Uniquement si ces matchs ne sont pas déjà encodés !)                  │
│                                                                            │
│  [Annuler]                          [Dupliquer la structure des matchs]    │
│                                                                            │
└────────────────────────────────────────────────────────────────────────────┘
```

### 3.4 Modale d'édition des commentaires

```
┌──────────────────────────────────────────────────────┐
│  Commentaires - N3E (2025)                    [✕]    │
├──────────────────────────────────────────────────────┤
│                                                       │
│  ┌─────────────────────────────────────────────────┐ │
│  │ Commentaire textarea multiligne...              │ │
│  │                                                  │ │
│  └─────────────────────────────────────────────────┘ │
│                                                       │
│  [Annuler]                         [Enregistrer]      │
│                                                       │
└──────────────────────────────────────────────────────┘
```

### 3.5 Vue Mobile

```
┌───────────────────────────────────────┐
│  📅 2025 │ Toutes compéts [Modifier]  │
├───────────────────────────────────────┤
│  Recherche de système de jeu          │
├───────────────────────────────────────┤
│  Nb équipes: [10]   Type: [▼ Tous]   │
│  Trier par: [▼ Saison]               │
│  [Rechercher]                         │
├───────────────────────────────────────┤
│  ← Scroll horizontal tableau →       │
│  ┌─────────────────────────────────┐  │
│  │ Saison │ Code │ Libellé │ ...  │  │
│  │ 2025   │ N3E  │ Nat 3   │ ...  │  │
│  └─────────────────────────────────┘  │
└───────────────────────────────────────┘
```

---

## 4. Détail des données

### 4.1 Résultats de recherche

Chaque ligne du tableau contient :

| Champ | Source | Description |
|-------|--------|-------------|
| `season` | `kp_competition.Code_saison` | Code saison |
| `code` | `kp_competition.Code` | Code compétition |
| `codeNiveau` | `kp_competition.Code_niveau` | INT / NAT / REG |
| `libelle` | `kp_competition.Libelle` | Titre principal |
| `soustitre` | `kp_competition.Soustitre` | Sous-titre (affiché si `Titre_actif != 'O'`) |
| `soustitre2` | `kp_competition.Soustitre2` | Sous-titre 2 |
| `titreActif` | `kp_competition.Titre_actif` | Détermine l'affichage du libellé |
| `codeTypeclt` | `kp_competition.Code_typeclt` | CHPT ou CP |
| `codeTour` | `kp_competition.Code_tour` | Tour/Phase (`10` = Final, affiché `F`) |
| `nbEquipes` | `kp_competition.Nb_equipes` | Nombre d'équipes |
| `qualifies` | `kp_competition.Qualifies` | Nombre de qualifiés |
| `elimines` | `kp_competition.Elimines` | Nombre d'éliminés |
| `commentaires` | `kp_competition.commentairesCompet` | Commentaires privés (modifiable) |
| `nbMatchs` | Calculé | `COUNT(m.Id)` des matchs hors phases Break/Pause |
| `nbTerrains` | Calculé | `COUNT(DISTINCT m.Terrain)` des matchs |
| `nbTours` | Calculé | `COUNT(DISTINCT j.Etape)` des journées |
| `matchsEncodes` | Calculé | `true` si majorité des libellés matchs contiennent `[...]` |

### 4.2 Calcul de "Matchs encodés"

Un match est considéré "encodé" si son champ `Libelle` contient un pattern entre crochets (ex: `[T20-T21/W12-L15]`). La compétition est marquée "encodée" si la majorité (> 50%) de ses matchs ont un libellé encodé.

Requête SQL indicative :
```sql
SELECT
  COUNT(CASE WHEN m.Libelle LIKE '%[%]%' THEN 1 END) as nbEncoded,
  COUNT(m.Id) as nbTotal
FROM kp_match m
JOIN kp_journee j ON j.Id = m.Id_journee
WHERE j.Code_competition = ? AND j.Code_saison = ?
  AND j.Phase NOT IN ('Break', 'Pause')
```

Affiché comme O si `nbEncoded > nbTotal / 2`, N sinon.

### 4.3 Détail de la compétition origine (dans la modale de copie)

| Champ | Source | Description |
|-------|--------|-------------|
| `codeTypeclt` | `kp_competition.Code_typeclt` | Type classement |
| `nbEquipes` | `kp_competition.Nb_equipes` | Nombre d'équipes |
| `qualifies` | `kp_competition.Qualifies` | Qualifiés |
| `elimines` | `kp_competition.Elimines` | Éliminés |
| `nbMatchs` | Calculé | Total des matchs |
| `journees` | `kp_journee` | Liste des phases (Niveau, Phase, Lieu) |

### 4.4 Pré-remplissage des champs du formulaire de copie

Les champs du formulaire sont pré-remplis avec les données de la **première journée** de la compétition d'origine :

| Champ | Source |
|-------|--------|
| Date début | `kp_journee.Date_debut` (première journée trouvée) |
| Date fin | `kp_journee.Date_fin` |
| Nom | `kp_journee.Nom` |
| Libellé | `kp_journee.Libelle` |
| Lieu | `kp_journee.Lieu` |
| Plan d'eau | `kp_journee.Plan_eau` |
| Département | `kp_journee.Departement` |
| Responsable insc. | `kp_journee.Responsable_insc` |
| Responsable R1 | `kp_journee.Responsable_R1` |
| Organisateur | `kp_journee.Organisateur` |
| Délégué | `kp_journee.Delegue` |

Si le champ est laissé **vide** par l'utilisateur, la copie reprendra les valeurs individuelles de chaque journée d'origine (comportement legacy du `%`).

---

## 5. Logique de copie

### 5.1 Processus

La copie s'effectue dans une **transaction SQL** :

1. **Pour chaque journée** de la compétition d'origine (triées par `Id`) :
   a. Créer une nouvelle journée dans la compétition destination avec :
      - Les champs communs du formulaire (si renseignés), sinon les valeurs de la journée d'origine
      - Phase, Niveau, Étape, Nbequipes, Type conservés de l'origine
   b. Copier les matchs associés à cette journée :
      - Ajuster les dates des matchs selon le décalage `diffdate` entre la Date_debut du formulaire et la Date_debut de l'origine
      - Conserver Heure_match, Terrain, Numero_ordre, Type

2. **Option "Encoder les équipes au 1er tour"** (si cochée) :
   - Uniquement pour les journées de Niveau ≤ 1
   - Les libellés des matchs sont remplacés par des identifiants de tirage `[Tn/Tm]` basés sur l'ordre des équipes dans la compétition d'origine
   - Permet de préparer le tirage au sort avant de connaître les équipes engagées

3. **Journalisation** : Chaque journée créée est tracée via `utyJournal('Ajout journee', ...)`

### 5.2 Décalage des dates

Si une Date_debut est spécifiée dans le formulaire et qu'une date d'origine est connue :
```
diffdate = Date_debut(formulaire) - Date_debut(première journée origine)
Date_match(copie) = Date_match(origine) + diffdate jours
```

Si pas de date spécifiée, `diffdate = 0` (les dates restent identiques).

---

## 6. Structure de la Page (Composants Vue)

```
sources/app4/pages/competitions/
  copy.vue                            # Page principale

sources/app4/components/competition-copy/
  SchemaSearchFilters.vue             # Filtres de recherche (nb équipes, type, tri)
  SchemaResultsTable.vue              # Tableau des résultats
  SchemaResultRow.vue                 # Ligne du tableau
  CopyModal.vue                       # Modale de copie
  CopyOriginSummary.vue               # Récapitulatif de l'origine
  CopyDestinationSelector.vue         # Sélection destination (saison + compétition)
  CopyFormFields.vue                  # Champs communs du formulaire
  CommentEditModal.vue                # Modale d'édition commentaire
```

---

## 7. Types TypeScript

```typescript
// types/competition-copy.ts

/** Filtres de recherche de schémas */
export interface SchemaSearchFilters {
  nbEquipes: number
  typeCompetition: 'CHPT' | 'CP' | ''    // '' = Tous
  tri: 'saison' | 'matchs'
}

/** Résultat de recherche de schéma */
export interface SchemaSearchResult {
  code: string
  season: string
  codeNiveau: string
  libelle: string
  soustitre: string | null
  soustitre2: string | null
  titreActif: boolean
  codeTypeclt: 'CHPT' | 'CP'
  codeTour: string | null
  nbEquipes: number
  qualifies: number
  elimines: number
  commentaires: string | null
  nbMatchs: number
  nbTerrains: number
  nbTours: number
  matchsEncodes: boolean
}

/** Détail d'une compétition pour la copie */
export interface CompetitionCopyDetail {
  code: string
  season: string
  codeTypeclt: 'CHPT' | 'CP'
  nbEquipes: number
  qualifies: number
  elimines: number
  nbMatchs: number
  soustitre: string | null
  soustitre2: string | null
  commentaires: string | null
  journees: CompetitionCopyJournee[]
  /** Données de la première journée pour pré-remplissage */
  prefill: CompetitionCopyPrefill
}

/** Journée dans le détail de copie */
export interface CompetitionCopyJournee {
  id: number
  phase: string
  niveau: number
  lieu: string | null
}

/** Données de pré-remplissage */
export interface CompetitionCopyPrefill {
  dateDebut: string | null
  dateFin: string | null
  nom: string | null
  libelle: string | null
  lieu: string | null
  planEau: string | null
  departement: string | null
  responsableInsc: string | null
  responsableR1: string | null
  organisateur: string | null
  delegue: string | null
}

/** Entrée d'un dropdown de compétition (groupé par section) */
export interface CompetitionOption {
  code: string
  libelle: string
  codeTypeclt: string
  nbEquipes: number
  qualifies: number
  elimines: number
}

export interface CompetitionOptionGroup {
  label: string
  options: CompetitionOption[]
}

/** Payload de la requête de copie */
export interface CopyCompetitionPayload {
  originSeason: string
  originCompetition: string
  destinationSeason: string
  destinationCompetition: string
  dateDebut: string | null           // null = reprendre les valeurs individuelles
  dateFin: string | null
  nom: string | null
  libelle: string | null
  lieu: string | null
  planEau: string | null
  departement: string | null
  responsableInsc: string | null
  responsableR1: string | null
  organisateur: string | null
  delegue: string | null
  initPremierTour: boolean           // Encoder les équipes au 1er tour
}

/** Réponse de la copie */
export interface CopyCompetitionResponse {
  success: boolean
  journeesCreated: number
  matchsCreated: number
}
```

---

## 8. Endpoints API2

### 8.1 Recherche de schémas

```
GET /admin/competitions/schemas
```

**Profil** : ≤ 3

**Query Parameters** :

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `nbEquipes` | int | Oui | Nombre d'équipes à rechercher |
| `type` | string | Non | `CHPT`, `CP`, ou vide pour tous |
| `tri` | string | Non | `saison` (défaut) ou `matchs` |

**Réponse** : `200 OK`

```json
{
  "schemas": [
    {
      "code": "N3E",
      "season": "2025",
      "codeNiveau": "NAT",
      "libelle": "Nationale 3 - 1/2 Finales Nord",
      "soustitre": null,
      "soustitre2": "1/2 Nord",
      "titreActif": true,
      "codeTypeclt": "CP",
      "codeTour": "2",
      "nbEquipes": 10,
      "qualifies": 3,
      "elimines": 0,
      "commentaires": null,
      "nbMatchs": 33,
      "nbTerrains": 4,
      "nbTours": 2,
      "matchsEncodes": true
    }
  ]
}
```

**Logique** :
1. Rechercher les compétitions avec `Nb_equipes = ?` et `Nb_equipes > 0`
2. Filtrer optionnellement par `Code_typeclt` (si `type` fourni)
3. Pour chaque compétition, compter les matchs (hors phases Break/Pause), les terrains distincts, les étapes distinctes, et vérifier l'encodage
4. Exclure les compétitions sans matchs (`nbMatchs = 0`)
5. Trier par saison DESC (défaut) ou par nbMatchs DESC puis saison DESC

### 8.2 Détail d'une compétition pour la copie

```
GET /admin/competitions/{season}/{code}/copy-detail
```

**Profil** : ≤ 3

**Réponse** : `200 OK`

```json
{
  "code": "MCP-Test",
  "season": "2025",
  "codeTypeclt": "CP",
  "nbEquipes": 8,
  "qualifies": 3,
  "elimines": 0,
  "nbMatchs": 36,
  "soustitre": null,
  "soustitre2": "Bac à sable",
  "commentaires": null,
  "journees": [
    { "id": 1000, "phase": "Group UW", "niveau": 5, "lieu": "Avranches" },
    { "id": 1001, "phase": "Group UX", "niveau": 5, "lieu": "Avranches" },
    { "id": 1002, "phase": "Quarter Final", "niveau": 4, "lieu": "Avranches" }
  ],
  "prefill": {
    "dateDebut": "2025-09-10",
    "dateFin": "2025-09-13",
    "nom": "B5_ECA European Championships - U21 Women",
    "libelle": null,
    "lieu": "Avranches",
    "planEau": "Stade Nautique de l'Écoparc",
    "departement": "FRA",
    "responsableInsc": "Lorrie DELATTRE, Adrien HUREL",
    "responsableR1": "Pierre MARTENS (431838)",
    "organisateur": "CANOE CLUB D'AVRANCHES",
    "delegue": "Alberto BARONI (2000220)"
  }
}
```

### 8.3 Liste des compétitions pour la destination

```
GET /admin/competitions/options
```

**Profil** : ≤ 3

**Query Parameters** :

| Paramètre | Type | Requis | Description |
|-----------|------|--------|-------------|
| `season` | string | Oui | Code saison |

**Réponse** : `200 OK`

```json
{
  "groups": [
    {
      "label": "Competitions_Internationales",
      "options": [
        {
          "code": "ECA1W",
          "libelle": "ECA Cup - Women",
          "codeTypeclt": "CP",
          "nbEquipes": 10,
          "qualifies": 3,
          "elimines": 0
        }
      ]
    }
  ]
}
```

**Note** : Cet endpoint respecte le filtre compétition de l'utilisateur (`Filtre_Competition` du `kp_user`). L'endpoint existant pour la page Compétitions peut être réutilisé s'il retourne les mêmes données groupées par section.

### 8.4 Copier la structure

```
POST /admin/competitions/copy
```

**Profil** : ≤ 3

**Body** :

```json
{
  "originSeason": "2024",
  "originCompetition": "T-COR",
  "destinationSeason": "2025",
  "destinationCompetition": "MCP-Test",
  "dateDebut": "2025-09-10",
  "dateFin": "2025-09-13",
  "nom": "B5_ECA European Championships",
  "libelle": null,
  "lieu": "Avranches",
  "planEau": "Stade Nautique de l'Écoparc",
  "departement": "FRA",
  "responsableInsc": "Lorrie DELATTRE",
  "responsableR1": "Pierre MARTENS",
  "organisateur": "CANOE CLUB D'AVRANCHES",
  "delegue": "Alberto BARONI",
  "initPremierTour": false
}
```

**Réponse succès** : `201 Created`

```json
{
  "success": true,
  "journeesCreated": 8,
  "matchsCreated": 36
}
```

**Réponse erreur** : `500 Internal Server Error`

```json
{
  "error": "La requête ne peut pas être exécutée",
  "detail": "Duplicate entry..."
}
```

**Logique** : Voir section 5 (Logique de copie). L'ensemble de l'opération est dans une transaction.

### 8.5 Modifier les commentaires d'une compétition

```
PATCH /admin/competitions/{season}/{code}/comments
```

**Profil** : ≤ 3

**Body** :

```json
{
  "commentaires": "Schéma optimisé pour 10 équipes avec double élimination"
}
```

**Réponse** : `200 OK`

### 8.6 Liste des saisons

Réutiliser l'endpoint existant ou le workContextStore qui contient déjà les saisons disponibles.

---

## 9. Schéma Base de Données

### Tables lues

#### kp_competition (champs utilisés)

| Colonne | Type | Description |
|---------|------|-------------|
| `Code` | varchar(12) | Code compétition |
| `Code_saison` | char(4) | Code saison |
| `Libelle` | varchar(80) | Nom de la compétition |
| `Soustitre` | varchar(80) | Sous-titre |
| `Soustitre2` | varchar(80) | Sous-titre 2 |
| `Code_typeclt` | varchar(8) | Type : `CHPT`, `CP` |
| `Code_niveau` | char(3) | Niveau : `INT`, `NAT`, `REG` |
| `Code_tour` | varchar(3) | Tour/Phase |
| `Code_ref` | varchar(10) | Groupe de référence |
| `Titre_actif` | char(1) | Afficher le titre (`O`/`N`) |
| `Nb_equipes` | smallint | Nombre d'équipes |
| `Qualifies` | smallint | Nombre de qualifiés |
| `Elimines` | smallint | Nombre d'éliminés |
| `commentairesCompet` | text | Commentaires privés |
| `GroupOrder` | smallint | Ordre dans le groupe |

#### kp_journee (champs utilisés)

| Colonne | Type | Description |
|---------|------|-------------|
| `Id` | int | ID de la journée |
| `Code_competition` | varchar(12) | Code compétition |
| `Code_saison` | char(4) | Code saison |
| `Phase` | varchar(30) | Nom de la phase |
| `Niveau` | smallint | Niveau de la phase |
| `Etape` | smallint | Numéro d'étape/tour |
| `Nbequipes` | smallint | Nombre d'équipes |
| `Type` | char(1) | `C` = classement, `E` = élimination |
| `Date_debut` | date | Date de début |
| `Date_fin` | date | Date de fin |
| `Nom` | varchar(80) | Nom de la journée |
| `Libelle` | varchar(80) | Libellé |
| `Lieu` | varchar(40) | Lieu |
| `Plan_eau` | varchar(60) | Plan d'eau |
| `Departement` | varchar(3) | Département/Pays |
| `Responsable_insc` | varchar(60) | Responsable inscriptions |
| `Responsable_R1` | varchar(60) | Responsable R1 |
| `Organisateur` | varchar(60) | Club organisateur |
| `Delegue` | varchar(60) | Délégué fédéral |

#### kp_match (champs utilisés)

| Colonne | Type | Description |
|---------|------|-------------|
| `Id` | int | ID du match |
| `Id_journee` | int | FK vers `kp_journee.Id` |
| `Libelle` | varchar(30) | Libellé du match (encodage entre crochets) |
| `Date_match` | date | Date du match |
| `Heure_match` | varchar(6) | Heure de début |
| `Terrain` | varchar(3) | Terrain |
| `Numero_ordre` | int | Numéro d'ordre |
| `Type` | char(1) | Type de match |

#### kp_groupe (champs utilisés)

| Colonne | Type | Description |
|---------|------|-------------|
| `Groupe` | varchar(10) | Code groupe |
| `section` | tinyint | Numéro de section (1-5, 100) |
| `ordre` | tinyint | Ordre dans la section |
| `id` | int | ID |

#### kp_competition_equipe (pour l'option "Encoder au 1er tour")

| Colonne | Type | Description |
|---------|------|-------------|
| `Id` | int | ID de l'équipe compétition |
| `Code_compet` | varchar(12) | Code compétition |
| `Code_saison` | char(4) | Code saison |
| `Poule` | varchar(3) | Code poule |
| `Tirage` | tinyint | Numéro de tirage |
| `Libelle` | varchar(40) | Nom de l'équipe |

### Tables modifiées (écriture)

- `kp_journee` : INSERT (création de nouvelles journées)
- `kp_match` : INSERT (copie des matchs avec dates ajustées)
- `kp_competition` : UPDATE (commentaires via PATCH)

---

## 10. Traductions i18n

### Français (fr.json)

```json
{
  "competitionCopy": {
    "title": "Recherche / Copie de système de jeu",
    "search": {
      "nbEquipes": "Nombre d'équipes",
      "type": "Type",
      "typeAll": "Tous",
      "sortBy": "Trier par",
      "sortSeason": "Saison",
      "sortMatches": "Nb matchs",
      "search": "Rechercher",
      "disclaimer": "Certains schémas anciens ne sont peut-être pas totalement encodés"
    },
    "table": {
      "season": "Saison",
      "code": "Code",
      "level": "Niv.",
      "label": "Libellé",
      "pitches": "Terrains",
      "rounds": "Tours",
      "teams": "Éq.",
      "matches": "Matchs",
      "encoded": "Encodés",
      "info": "Info",
      "actions": "Actions",
      "switchTo": "Basculer vers cette compétition",
      "viewSchema": "Voir le schéma",
      "copyTo": "Copier vers",
      "final": "F",
      "noResults": "Aucun résultat"
    },
    "confirm": {
      "switchTo": "Basculer vers la compétition {code} (saison {season}) ?"
    },
    "copy": {
      "title": "Copier la structure de compétition",
      "origin": "Origine",
      "destination": "Destination",
      "season": "Saison",
      "competition": "Compétition",
      "type": "Type",
      "teams": "équipes",
      "matches": "matchs",
      "qualified": "Qualifiées",
      "eliminated": "Éliminées",
      "phases": "Phases",
      "commonValues": "Valeurs communes (appliquées à toutes les journées/phases)",
      "commonValuesHelp": "Laisser vide pour reprendre les valeurs individuelles de chaque journée",
      "publicParams": "Paramètres calendrier public",
      "dateDebut": "Date début",
      "dateFin": "Date fin",
      "lieu": "Lieu",
      "departement": "Département",
      "nom": "Nom journée",
      "planEau": "Plan d'eau",
      "responsables": "Responsables",
      "organisateur": "Club organisateur",
      "responsableR1": "Responsable R1",
      "responsableInsc": "Responsable inscriptions",
      "delegue": "Délégué fédéral",
      "initFirstRound": "Encoder les équipes au premier tour (préparer le tirage au sort)",
      "initFirstRoundWarning": "Uniquement si ces matchs ne sont pas déjà encodés !",
      "submit": "Dupliquer la structure des matchs",
      "cancel": "Annuler",
      "confirmCopy": "Copier la structure de {origin} vers {destination} ?",
      "success": "{journees} journée(s) et {matchs} match(s) créé(s)",
      "error": "Erreur lors de la copie"
    },
    "comments": {
      "title": "Commentaires - {code} ({season})",
      "save": "Enregistrer",
      "cancel": "Annuler",
      "success": "Commentaire enregistré",
      "error": "Erreur lors de l'enregistrement"
    }
  }
}
```

### Anglais (en.json)

```json
{
  "competitionCopy": {
    "title": "Game System Search / Copy",
    "search": {
      "nbEquipes": "Number of teams",
      "type": "Type",
      "typeAll": "All",
      "sortBy": "Sort by",
      "sortSeason": "Season",
      "sortMatches": "Nb games",
      "search": "Search",
      "disclaimer": "Some old schemas may not be fully encoded"
    },
    "table": {
      "season": "Season",
      "code": "Code",
      "level": "Lev.",
      "label": "Label",
      "pitches": "Pitches",
      "rounds": "Rounds",
      "teams": "Teams",
      "matches": "Games",
      "encoded": "Encoded",
      "info": "Info",
      "actions": "Actions",
      "switchTo": "Switch to this competition",
      "viewSchema": "View schema",
      "copyTo": "Copy to",
      "final": "F",
      "noResults": "No results"
    },
    "confirm": {
      "switchTo": "Switch to competition {code} (season {season})?"
    },
    "copy": {
      "title": "Copy competition structure",
      "origin": "Origin",
      "destination": "Destination",
      "season": "Season",
      "competition": "Competition",
      "type": "Type",
      "teams": "teams",
      "matches": "games",
      "qualified": "Qualified",
      "eliminated": "Eliminated",
      "phases": "Phases",
      "commonValues": "Common values (applied to all gamedays/phases)",
      "commonValuesHelp": "Leave empty to keep individual values from each gameday",
      "publicParams": "Public calendar parameters",
      "dateDebut": "Start date",
      "dateFin": "End date",
      "lieu": "Location",
      "departement": "Department",
      "nom": "Gameday name",
      "planEau": "Waterway",
      "responsables": "Officials",
      "organisateur": "Organising club",
      "responsableR1": "Local official R1",
      "responsableInsc": "Registration official",
      "delegue": "Federal delegate",
      "initFirstRound": "Encode teams in first round (prepare draw)",
      "initFirstRoundWarning": "Only if these games are not already encoded!",
      "submit": "Duplicate game structure",
      "cancel": "Cancel",
      "confirmCopy": "Copy structure from {origin} to {destination}?",
      "success": "{journees} gameday(s) and {matchs} game(s) created",
      "error": "Error during copy"
    },
    "comments": {
      "title": "Comments - {code} ({season})",
      "save": "Save",
      "cancel": "Cancel",
      "success": "Comment saved",
      "error": "Error saving comment"
    }
  }
}
```

---

## 11. Sécurité

### Contrôles d'accès

| Action | Profil requis |
|--------|---------------|
| Rechercher des schémas | ≤ 3 |
| Voir le détail d'une compétition | ≤ 3 |
| Copier une structure | ≤ 3 |
| Modifier les commentaires | ≤ 3 |
| Basculer vers une compétition | ≤ 3 |

### Validations backend

- Vérification JWT (middleware auth)
- Vérification du profil ≤ 3 (ROLE_DIVISION)
- La copie vérifie que la compétition destination existe et appartient à une saison valide
- Transaction SQL avec rollback en cas d'erreur
- Le filtre compétition utilisateur (`Filtre_Competition`) s'applique sur les compétitions destination uniquement (pas sur la recherche de schémas, qui doit être ouverte à toutes les saisons/compétitions)

---

## 12. Notes de migration

### 12.1 Différences avec le legacy

| Aspect | Legacy (PHP) | App4 (Nuxt) |
|--------|-------------|-------------|
| Layout | Deux panneaux côte à côte (gauche = copie, droite = recherche) | Page unique avec tableau + modale de copie |
| Sélection origine | Dropdowns saison/compétition manuels | Bouton "Copier vers" sur chaque ligne du tableau |
| Filtre type | CP uniquement (hardcodé) | Sélecteur CHPT / CP / Tous |
| Valeur "individuelle" | Caractère `%` dans les champs | Champ vide = reprendre valeurs individuelles |
| Commentaires | Tooltip lecture seule | Modale d'édition |
| Basculer | Lien direct vers GestionDoc.php | Confirmation JS + navigation in-app |
| État | Variables de session PHP | État local Vue + URL query params |
| Colonnes tableau | Saison, Code, Niv., Libellé, Basculer, Tour, Équipes, Matchs, Info, Schéma | + Terrains, Tours, Matchs encodés, Copier vers |
| Matchs encodés | Non affiché | Nouvelle colonne O/N |

### 12.2 Lien avec les specs existantes

- **PAGE_COMPETITIONS.md** : Feature 2.4 #1 "Copie de structure" → redirige vers cette page (`/competitions/copy`)
- **PAGE_SCHEMA.md** : Le lien "Voir le schéma" dans le tableau redirige vers `/gamedays/schema?competition=X&season=Y`
- **PAGE_JOURNEES_PHASES.md** : La copie crée des journées et des matchs dans les tables `kp_journee` et `kp_match`
- **MENU_REORGANIZATION.md** : Accessible depuis le menu Compétitions ou via un bouton dans la page Compétitions

### 12.3 Point d'entrée dans le menu

L'accès se fait via un bouton dans la page `/competitions` (profil ≤ 3), pas via un item de menu dédié dans le header.

---

**Document créé le** : 2026-03-01
**Dernière mise à jour** : 2026-03-01
**Statut** : ✅ Implémenté (backend API2 + frontend App4)
**Auteur** : Claude Code
