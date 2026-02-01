# Spécification - Page Compétitions

## 1. Vue d'ensemble

La page Compétitions permet de gérer les compétitions d'une saison : création, modification, paramétrage des options d'affichage, gestion des images (bandeau, logo, sponsor), et configuration des classements.

**Route** : `/competitions`

**Accès** :
- Profil ≤ 10 : Lecture seule
- Profil ≤ 4 : Toggle publication
- Profil ≤ 3 : Ajout/Modification/Verrouillage
- Profil ≤ 2 : Suppression
- Profil = 9 : Redirigé vers SelectFeuille.php

**Page PHP Legacy** : `GestionCompetition.php`

---

## 2. Fonctionnalités

### 2.1 Liste des compétitions

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Liste par saison avec filtres | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Filtrer par niveau (INT/NAT/REG) | ≤ 10 | Utile | ✅ Conserver |
| 3 | Filtrer par type (N, CF, section) | ≤ 10 | Utile | ✅ Conserver |
| 4 | Groupement par section | ≤ 10 | Essentielle | ✅ Conserver |
| 5 | Afficher nb matchs | ≤ 10 | Utile | ✅ Conserver |
| 6 | Afficher présence RC | ≤ 10 | Utile | ✅ Conserver |
| 7 | Toggle publication (O/N) | ≤ 4 | Essentielle | ✅ Conserver |
| 8 | Toggle verrou FDM | ≤ 3 | Essentielle | ✅ Conserver |
| 9 | Statut compétition (ATT/ON/END) | ≤ 3 | Essentielle | ✅ Conserver |

### 2.2 Création de compétition

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Code unique | ≤ 2 | Essentielle | ✅ Conserver |
| 2 | Niveau (INT/NAT/REG) | ≤ 3 | Essentielle | ✅ Conserver |
| 3 | Labels (principal, public, catégorie) | ≤ 3 | Essentielle | ✅ Conserver |
| 4 | Groupe et ordre | ≤ 3 | Essentielle | ✅ Conserver |
| 5 | Type (CHPT/CP/MULTI) | ≤ 3 | Essentielle | ✅ Conserver |
| 6 | Grille points (MULTI) | ≤ 2 | Spécialisé | ✅ Conserver |
| 7 | Compétitions sources (MULTI) | ≤ 2 | Spécialisé | ✅ Conserver |
| 8 | Type classement (MULTI) | ≤ 2 | Spécialisé | ✅ Conserver |
| 9 | Tour/Phase | ≤ 3 | Essentielle | ✅ Conserver |
| 10 | Qualifiés/Éliminés | ≤ 3 | Essentielle | ✅ Conserver |
| 11 | Points (4-2-1-0 ou 3-1-0-0) | ≤ 3 | Essentielle | ✅ Conserver |
| 12 | Goal average (général/particulier) | ≤ 3 | Essentielle | ✅ Conserver |
| 13 | Images (bandeau/logo/sponsor) | ≤ 3 | Essentielle | ✅ Conserver |
| 14 | Options affichage (checkboxes) | ≤ 2 | Essentielle | ✅ Conserver |
| 15 | Création journée initiale | ≤ 3 | Utile | ✅ Conserver |

### 2.3 Modification de compétition

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Modifier tous champs (sauf code) | ≤ 3 | Essentielle | ✅ Conserver |
| 2 | Commentaires privés | ≤ 3 | Utile | ✅ Conserver |
| 3 | Mettre à jour images | ≤ 3 | Essentielle | ✅ Conserver |

### 2.4 Liens vers autres pages

| # | Lien | Page cible | Profil | Décision |
|---|------|-----------|--------|----------|
| 1 | Copie de structure | GestionCopieCompetition | ≤ 3 | ✅ Conserver |
| 2 | Gestion des RC | GestionRc | ≤ 2 | ✅ Conserver |
| 3 | Documents | GestionDoc | ≤ 10 | ✅ Conserver |

---

## 3. Structure de la Page

```
┌─────────────────────────────────────────────────────────────────────────┐
│  Header : Gestion des compétitions                                       │
├─────────────────────────────────────────────────────────────────────────┤
│  Filtres :                                                               │
│  ┌──────────────┐  ┌────────────────┐  ┌──────────────────────┐         │
│  │ Saison: 2025 │  │ Niveau: Tous ▼ │  │ Type: Toutes ▼       │         │
│  └──────────────┘  └────────────────┘  └──────────────────────┘         │
│                                                                          │
│  [+ Ajouter compétition]                                                 │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  === Section: National ===                                               │
│  ┌───┬────────┬─────┬────────────────────────────┬────────┬─────────────┐
│  │👁️ │ Code   │ Niv │ Libellé                    │ Groupe │ Type │Statut│
│  ├───┼────────┼─────┼────────────────────────────┼────────┼─────────────┤
│  │ 🟢│ N1M    │ NAT │ 📋 Nationale 1 Masculine   │ N1     │ CHPT │ ON   │
│  │ 🟢│ N1F    │ NAT │ 📋 Nationale 1 Féminine    │ N1     │ CHPT │ ON   │
│  │ 🔴│ N2M-PH │ NAT │ 📋 Nationale 2 Masculine   │ N2     │ CP   │ ATT  │
│  └───┴────────┴─────┴────────────────────────────┴────────┴─────────────┘
│                                                                          │
│  === Section: Coupe de France ===                                        │
│  ┌───┬────────┬─────┬────────────────────────────┬────────┬─────────────┐
│  │👁️ │ Code   │ Niv │ Libellé                    │ Groupe │ Type │Statut│
│  ├───┼────────┼─────┼────────────────────────────┼────────┼─────────────┤
│  │ 🟢│ CFM    │ NAT │ 📋 Coupe de France Masc.   │ CF     │ CP   │ ON   │
│  └───┴────────┴─────┴────────────────────────────┴────────┴─────────────┘
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### 3.1 Colonnes du tableau

| Colonne | Description | Actions |
|---------|-------------|---------|
| 👁️ Publication | Icône vert=publié, rouge=privé | Click pour toggle (profil ≤4) |
| Code | Code unique de la compétition | Lien vers Documents |
| ✏️ | Modifier | Ouvre modal d'édition (profil ≤3) |
| Niv | Niveau (INT/NAT/REG) | - |
| RC | Indicateur référent assigné | Lien vers GestionRc |
| Libellé | Nom de la compétition | Tooltip avec détails |
| Groupe | Code du groupe | - |
| Tour | Numéro de tour/phase | - |
| Type | CHPT/CP/MULTI | - |
| Statut | ATT/ON/END | Click pour changer (profil ≤3) |
| Équipes | Nombre d'équipes inscrites | - |
| 🔒 Verrou | Verrouiller les FDM | Click pour toggle (profil ≤3) |
| Matchs | Nombre de matchs | - |
| 🗑️ | Supprimer | Confirmation requise (profil ≤2) |

---

## 4. Modal Création/Édition

### 4.1 Champs du formulaire

| Champ | Type | Requis | Validation | Profil édition |
|-------|------|--------|------------|----------------|
| code | text(12) | Oui | Unique par saison | ≤2 (création only) |
| code_niveau | select | Oui | INT/NAT/REG | ≤3 |
| libelle | text(50) | Oui | - | ≤2 |
| soustitre | text(80) | Non | Titre public | ≤3 |
| soustitre2 | text(80) | Non | Catégorie | ≤3 |
| code_ref | select | Oui | Groupe existant | ≤3 |
| group_order | number(1) | Non | Ordre dans groupe | ≤3 |
| code_typeclt | select | Oui | CHPT/CP/MULTI | ≤3 |
| points_grid | json | Si MULTI | Grille JSON | ≤2 |
| multi_competitions | json[] | Si MULTI | Codes compétitions | ≤2 |
| ranking_structure_type | select | Si MULTI | team/club/cd/cr/nation | ≤2 |
| code_tour | select | Oui | 1-6 ou 10 (Finale) | ≤3 |
| qualifies | number(2) | Non | Défaut: 3 | ≤3 |
| elimines | number(2) | Non | Défaut: 0 | ≤3 |
| points | radio | Oui | 4-2-1-0 ou 3-1-0-0 | ≤3 |
| goalaverage | radio | Oui | gen/part | ≤3 |
| web | text(80) | Non | URL site web | ≤3 |
| bandeau_link | url | Non | Image 2480x250 | ≤3 |
| logo_link | url | Non | Image logo | ≤3 |
| sponsor_link | url | Non | Image 2480x250 | ≤2 |
| en_actif | checkbox | Non | Compétition en anglais | ≤2 |
| titre_actif | checkbox | Non | Utiliser Label 1 | ≤2 |
| bandeau_actif | checkbox | Non | Afficher bandeau | ≤2 |
| logo_actif | checkbox | Non | Afficher logo | ≤2 |
| sponsor_actif | checkbox | Non | Afficher sponsor | ≤2 |
| kpi_ffck_actif | checkbox | Non | Afficher logo KPI/FFCK | ≤2 |
| statut | select | Oui | ATT/ON/END | ≤3 |
| publication | checkbox | Non | Publier | ≤2 |
| commentaires_compet | textarea | Non | Notes privées | ≤3 |

### 4.2 Section création journée (optionnel)

Lors de la création d'une nouvelle compétition, option d'ajouter une journée initiale :

| Champ | Type | Requis | Validation |
|-------|------|--------|------------|
| titre_journee | text | Non | Nom de la journée |
| date_debut | date | Non | Format FR/EN |
| date_fin | date | Non | ≥ date_debut |
| lieu | text | Non | - |
| departement | text(3) | Non | Code département |
| publier_journee | checkbox | Non | - |

---

## 5. Type MULTI - Configuration spécifique

### 5.1 Grille de points

Format JSON définissant les points par classement :
```json
{"1":10, "2":6, "3":4, "4":3, "5":2, "6":1, "default":0}
```

Interface d'édition :
- Éditeur visuel avec ligne par position
- Valeur "default" pour positions non listées

### 5.2 Compétitions sources

Select multiple groupé par section permettant de sélectionner les compétitions dont les classements seront agrégés.

### 5.3 Type de classement

| Type | Description |
|------|-------------|
| team | Par équipe (défaut) |
| club | Par club (agrège les équipes du même club) |
| cd | Par Comité Départemental |
| cr | Par Comité Régional |
| nation | Par nation (international) |

---

## 6. Endpoints API2

### 6.1 Lecture

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| GET | `/admin/seasons` | Liste des saisons | ≤10 |
| GET | `/admin/competitions` | Liste des compétitions | ≤10 |
| GET | `/admin/competitions/{code}` | Détail d'une compétition | ≤10 |
| GET | `/admin/groups` | Liste des groupes | ≤10 |

### 6.2 Écriture

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| POST | `/admin/competitions` | Créer compétition | ≤3 |
| PUT | `/admin/competitions/{code}` | Modifier compétition | ≤3 |
| DELETE | `/admin/competitions/{code}` | Supprimer compétition | ≤2 |
| PATCH | `/admin/competitions/{code}/publish` | Toggle publication | ≤4 |
| PATCH | `/admin/competitions/{code}/lock` | Toggle verrou FDM | ≤3 |
| PATCH | `/admin/competitions/{code}/status` | Changer statut | ≤3 |

### 6.3 Paramètres de requête

**GET /admin/competitions**

| Param | Type | Description |
|-------|------|-------------|
| season | string | Code saison (requis) |
| level | string | Filtre niveau (INT/NAT/REG) |
| section | int | Filtre section |
| type | string | Filtre type (N/CF/M) |

---

## 7. Schéma de données

### 7.1 Table kp_competition

| Colonne | Type | Description |
|---------|------|-------------|
| Code | varchar(12) | PK, Code unique |
| Code_saison | varchar(10) | PK, Saison |
| Code_niveau | varchar(3) | INT/NAT/REG |
| Libelle | varchar(50) | Nom principal |
| Soustitre | varchar(80) | Titre public |
| Soustitre2 | varchar(80) | Catégorie |
| Web | varchar(80) | URL site web |
| BandeauLink | varchar(255) | URL/chemin bandeau |
| LogoLink | varchar(255) | URL/chemin logo |
| SponsorLink | varchar(255) | URL/chemin sponsor |
| ToutGroup | char(1) | Obsolète |
| TouteSaisons | char(1) | Obsolète |
| En_actif | char(1) | Anglais actif |
| Titre_actif | char(1) | Label 1 actif |
| Bandeau_actif | char(1) | Bandeau actif |
| Logo_actif | char(1) | Logo actif |
| Sponsor_actif | char(1) | Sponsor actif |
| Kpi_ffck_actif | char(1) | Logo KPI/FFCK actif |
| Code_ref | varchar(20) | FK vers kp_groupe |
| GroupOrder | int | Ordre dans le groupe |
| Code_typeclt | varchar(5) | CHPT/CP/MULTI |
| points_grid | text | JSON grille points MULTI |
| multi_competitions | text | JSON compétitions MULTI |
| ranking_structure_type | varchar(10) | Type classement MULTI |
| Code_tour | int | Tour/Phase (1-10) |
| Qualifies | int | Nb équipes qualifiées |
| Elimines | int | Nb équipes éliminées |
| Points | varchar(10) | Barème points |
| goalaverage | varchar(10) | Type goal average |
| Statut | varchar(3) | ATT/ON/END |
| Publication | char(1) | O/N |
| Verrou | char(1) | Verrou FDM |
| Nb_equipes | int | Calculé |
| commentairesCompet | text | Notes privées |

### 7.2 Table kp_groupe

| Colonne | Type | Description |
|---------|------|-------------|
| id | int | PK |
| Groupe | varchar(20) | Code groupe (unique) |
| Libelle | varchar(50) | Nom français |
| Libelle_en | varchar(50) | Nom anglais |
| section | int | Section (1=Inter, 2=Nat...) |
| ordre | int | Ordre dans section |
| Code_niveau | varchar(3) | Niveau par défaut |

---

## 8. Composants Vue

### 8.1 Structure des fichiers

```
sources/app4/pages/competitions/
├── index.vue                 # Page principale

sources/app4/components/competitions/
├── CompetitionList.vue       # Tableau des compétitions
├── CompetitionModal.vue      # Modal création/édition
├── CompetitionFilters.vue    # Filtres (saison, niveau, type)
├── CompetitionRow.vue        # Ligne du tableau
├── MultiConfigPanel.vue      # Configuration MULTI
├── PointsGridEditor.vue      # Éditeur grille points
└── InitialGamedayForm.vue    # Formulaire journée initiale
```

### 8.2 État (composables)

```typescript
// composables/useCompetitions.ts
interface Competition {
  code: string
  codeSaison: string
  codeNiveau: 'INT' | 'NAT' | 'REG'
  libelle: string
  soustitre?: string
  soustitre2?: string
  codeRef: string
  groupOrder?: number
  codeTypeclt: 'CHPT' | 'CP' | 'MULTI'
  pointsGrid?: Record<string, number>
  multiCompetitions?: string[]
  rankingStructureType?: 'team' | 'club' | 'cd' | 'cr' | 'nation'
  codeTour: number
  qualifies: number
  elimines: number
  points: '4-2-1-0' | '3-1-0-0'
  goalaverage: 'gen' | 'part'
  statut: 'ATT' | 'ON' | 'END'
  publication: boolean
  verrou: boolean
  nbEquipes: number
  nbMatchs: number
  hasRc: boolean
  section: number
  sectionLabel: string
}
```

---

## 9. Améliorations prévues

| # | Amélioration | Description |
|---|--------------|-------------|
| 1 | Tri colonnes | Clic sur en-tête pour trier |
| 2 | Recherche | Filtrer par libellé/code |
| 3 | Pagination | Si > 50 compétitions |
| 4 | Drag & drop | Réordonner dans le groupe |
| 5 | Actions bulk | Publier/Verrouiller plusieurs |
| 6 | Duplication | Copier une compétition |
| 7 | Historique | Voir modifications |
| 8 | Validation | Formulaire temps réel |
| 9 | Preview images | Aperçu bandeau/logo/sponsor |

---

## 10. Sécurité

### 10.1 Validation côté serveur

- Code unique par saison
- Code_ref doit exister dans kp_groupe
- JSON valide pour points_grid et multi_competitions
- Suppression impossible si journées existent

### 10.2 Audit

Toutes les actions sont journalisées dans kp_journal :
- Ajout Compet
- Modif Competition
- Suppression Compet
- Publication competition
- Verrou Compet

---

## 11. Notes de migration

### 11.1 Dépendances

- GestionRc.php : À migrer en même temps ou après
- GestionCopieCompetition.php : Peut rester legacy avec lien
- GestionGroupe.php : À migrer pour édition des groupes

### 11.2 Images

Les images (bandeau, logo, sponsor) sont stockées en :
- `/img/logo/B-{CODE}-{SAISON}.jpg` (bandeau)
- `/img/logo/L-{CODE}-{SAISON}.jpg` (logo)
- `/img/logo/S-{CODE}-{SAISON}.jpg` (sponsor)

Utiliser l'API d'upload d'images existante de la page Operations.

---

**Document créé le** : 2026-02-01
**Dernière mise à jour** : 2026-02-01
**Statut** : 🚧 Spécifications en cours
