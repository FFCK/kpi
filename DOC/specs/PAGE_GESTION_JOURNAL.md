# Specification - Page Journal d'Activite

## Statut : A VALIDER

## 1. Vue d'ensemble

Page d'administration permettant de consulter le journal d'activite (audit log) de l'application. Toutes les actions effectuees par les administrateurs (connexions, modifications, suppressions, calculs) sont enregistrees dans la table `kp_journal` et consultables via cette page.

**Route** : `/journal`

**Acces** :
- Profil <= 2 : Consultation complete du journal (lecture seule)

**Page PHP Legacy** : `GestionJournal.php` + `GestionJournal.tpl`

**Contexte de travail** : Non applicable (page globale, non liee au workContextStore).

---

## 2. Fonctionnalites

### 2.1 Consultation du journal

| # | Fonctionnalite | Profil | Legacy | Decision |
|---|----------------|--------|--------|----------|
| 1 | Affichage liste des entrees du journal (date, identite, action, detail, competition, journee, match) | <= 2 | Table HTML avec LIMIT | ✅ Conserver - pagination moderne |
| 2 | Filtre par nombre de lignes (25, 50, 100, 200, 500, 1000) | <= 2 | Select dropdown | ✅ Remplacer par pagination standard AdminPagination |
| 3 | Filtre par utilisateur | <= 2 | Select dropdown (LIKE prefix) | ✅ Ameliorer - autocomplete |
| 4 | Filtre par type d'action | <= 2 | Select dropdown avec groupes Ensemble/Detail | ✅ Conserver - select avec optgroups |
| 5 | Filtre par saison | <= 2 | Input text (LIKE prefix) | ✅ Ameliorer - select dropdown des saisons |
| 6 | Filtre par competition | <= 2 | Input text (LIKE prefix) | ✅ Ameliorer - select dropdown des competitions |

### 2.2 Ameliorations par rapport au legacy

| # | Amelioration | Description |
|---|--------------|-------------|
| 1 | Pagination standard | Remplacement du select "Nb de lignes" par `AdminPagination` avec page + limit |
| 2 | Autocomplete utilisateurs | Recherche par nom au lieu d'un select charge de tous les utilisateurs |
| 3 | Select saison | Dropdown des saisons au lieu d'un champ texte libre |
| 4 | Select competition | Dropdown des competitions au lieu d'un champ texte libre |
| 5 | Recherche textuelle | Champ recherche libre pour chercher dans le contenu du journal |
| 6 | Filtre par date | Filtrer par periode (date debut / date fin) |
| 7 | Layout moderne | Filtres dans la toolbar, tableau responsive avec cards sur mobile |
| 8 | Tri par date | Tri par date descendant par defaut (plus recent en premier) |

---

## 3. Decisions de conception

| # | Question | Decision |
|---|----------|----------|
| Q1 | Page en lecture seule ? | **Oui**. Le journal est un audit log immutable. Pas de creation, modification ou suppression d'entrees. |
| Q2 | Acces a quel profil ? | **Profil <= 2** (ROLE_ADMIN). Le legacy utilise `parent::__construct(2)` qui correspond au profil <= 2. |
| Q3 | Filtre utilisateur : select ou autocomplete ? | **Autocomplete** avec recherche dans les utilisateurs ayant des entrees dans le journal. Evite de charger un select volumineux. |
| Q4 | Filtre saison/competition : texte libre ou select ? | **Select dropdown** pour la saison (via `/admin/filters/seasons`). **Input text** pour la competition (comme le legacy, car il s'agit d'un prefix match, pas d'une selection stricte). |
| Q5 | Filtre par date ? | **Oui**, ajouter un filtre par plage de dates (date debut / date fin). Non present dans le legacy mais tres utile pour un journal d'activite. |
| Q6 | Groupes d'actions dans le select | **Conserver** les groupes "Ensemble" (Connexion, Ajout, Modif, Supp, Calcul) et "Detail" (toutes les actions distinctes de la base). Les groupes Ensemble utilisent un prefix match. |
| Q7 | Colonnes affichees | Conserver les 7 colonnes du legacy : Date, Identite, Action, Journal (detail), Competition + Saison, Journee, Match. |
| Q8 | Export du journal ? | **Non** pour la V1. Peut etre ajoute ultérieurement si besoin. |

---

## 4. Structure de la Page

### 4.1 Vue Desktop

```
+---------------------------------------------------------------------------+
|  Journal d'activite                                                        |
+---------------------------------------------------------------------------+
| AdminToolbar                                                               |
| [Utilisateur v] [Action v] [Saison v] [Compet: ___] [Du ___] [Au ___]    |
|                                              [Recherche...              ]  |
+---------------------------------------------------------------------------+
| Table                                                                      |
| Date          | Identite         | Action                    | Detail     |
|               |                  |                           |            |
| 15/02/26      | Laurent Garrigue | Update logo equipes       |            |
| a 20:46       |                  |                           | N1D - 2025 |
|               |                  |                           |            |
| 15/02/26      | Laurent Garrigue | Update logo equipes       |            |
| a 20:45       |                  |                           | N1H - 2025 |
|               |                  |                           |            |
| 11/02/26      | Laurent Garrigue | Connexion                 | Laurent    |
| a 22:44       |                  |                           | GARRIGUE   |
|               |                  |                           | ECM - 2025 |
+---------------------------------------------------------------------------+
| AdminPagination                                                            |
| [< 1 2 3 >]                                    [25|50|100 par page]      |
+---------------------------------------------------------------------------+
```

### 4.2 Vue Mobile (Cards)

```
+----------------------------------+
|  Journal d'activite              |
+----------------------------------+
| [Filtres v]                      |
| [Recherche...                  ] |
+----------------------------------+
| +--- Card -------------------+  |
| | 15/02/26 a 20:46           |  |
| | Laurent Garrigue           |  |
| | Update logo equipes        |  |
| | N1D - 2025                 |  |
| +----------------------------+  |
| +--- Card -------------------+  |
| | 15/02/26 a 20:45           |  |
| | Laurent Garrigue           |  |
| | Update logo equipes        |  |
| | N1H - 2025                 |  |
| +----------------------------+  |
+----------------------------------+
| [< 1 2 3 >]  [25 par page v]   |
+----------------------------------+
```

### 4.3 Tableau Desktop - Colonnes

| Colonne | Champ DB | Description | Largeur |
|---------|----------|-------------|---------|
| Date | `j.Dates` | Date et heure formatees (dd/MM/yy a HH:mm) | ~100px |
| Identite | `u.Identite` | Nom complet de l'utilisateur (tooltip: Fonction) | ~150px |
| Action | `j.Actions` | Type d'action effectuee | ~200px |
| Detail | `j.Journal` | Description detaillee de l'action | flex |
| Comp. | `j.Competitions` + `j.Saisons` | Code competition (gras) + saison | ~80px |
| Journ. | `j.Journees` | ID journee concernee | ~60px |
| Match | `j.Matchs` | ID(s) match concerne(s) | ~60px |

---

## 5. Filtres

### 5.1 Filtre Utilisateur

- **Type** : Autocomplete (input avec suggestions)
- **Source** : Endpoint `/admin/journal/users` (utilisateurs distincts ayant des entrees dans le journal)
- **Comportement** : Filtre les entrees dont `j.Users` correspond a l'utilisateur selectionne
- **Valeur par defaut** : Tous (pas de filtre)
- **Persistence** : Non (reset a chaque chargement de page)

### 5.2 Filtre Action

- **Type** : Select dropdown avec optgroups
- **Groupes** :
  - **Ensemble** (prefix match) :
    - `Connexion` → Connexions
    - `Ajout` → Ajouts
    - `Modif` → Modifications
    - `Supp` → Suppressions
    - `Calcul` → Calculs
  - **Detail** (valeur exacte) : Toutes les actions distinctes de la base (chargees depuis l'API)
- **Source** : Endpoint `/admin/journal/actions`
- **Comportement** : Filtre les entrees dont `j.Actions` commence par ou est egal a la valeur selectionnee
- **Valeur par defaut** : Toutes

### 5.3 Filtre Saison

- **Type** : Select dropdown
- **Source** : Endpoint `/admin/filters/seasons` (endpoint commun existant)
- **Comportement** : Filtre les entrees dont `j.Saisons` correspond a la saison selectionnee
- **Valeur par defaut** : Toutes (pas de filtre)

### 5.4 Filtre Competition

- **Type** : Input text (prefix match, comme le legacy)
- **Comportement** : Filtre les entrees dont `j.Competitions` commence par le texte saisi
- **Valeur par defaut** : Vide (pas de filtre)

### 5.5 Filtre par Date

- **Type** : Deux champs date (date debut, date fin)
- **Comportement** :
  - Si date debut remplie : filtre `j.Dates >= dateDebut`
  - Si date fin remplie : filtre `j.Dates <= dateFin 23:59:59`
  - Les deux peuvent etre utilises independamment
- **Valeur par defaut** : Vide (pas de filtre)

### 5.6 Recherche Textuelle

- **Type** : Champ recherche dans AdminToolbar
- **Comportement** : Recherche dans `j.Journal`, `j.Actions`, `j.Competitions`, `j.Matchs`
- **Debounce** : 300ms

---

## 6. Endpoints API2

### 6.1 Nouveaux endpoints a creer

| Methode | Endpoint | Description | Parametres | Profil |
|---------|----------|-------------|------------|--------|
| GET | `/admin/journal` | Liste paginee des entrees du journal | Voir 6.2 | <= 2 |
| GET | `/admin/journal/users` | Utilisateurs distincts ayant des entrees | - | <= 2 |
| GET | `/admin/journal/actions` | Actions distinctes du journal | - | <= 2 |

### 6.2 Parametres GET /admin/journal

| Parametre | Type | Defaut | Description |
|-----------|------|--------|-------------|
| `page` | int | 1 | Numero de page |
| `limit` | int | 50 | Nombre d'elements par page (max: 200) |
| `user` | string | - | Code utilisateur (filtre exact) |
| `action` | string | - | Action ou prefixe d'action |
| `actionMode` | string | `prefix` | `prefix` (groupes Ensemble) ou `exact` (Detail) |
| `season` | string | - | Code saison (filtre exact) |
| `competition` | string | - | Code competition (prefix match) |
| `search` | string | - | Recherche textuelle dans journal/actions/competitions/matchs |
| `dateFrom` | string | - | Date debut (format YYYY-MM-DD) |
| `dateTo` | string | - | Date fin (format YYYY-MM-DD) |

### 6.3 Reponse GET /admin/journal

```json
{
  "items": [
    {
      "id": 12345,
      "date": "2026-02-15T20:46:00",
      "userCode": "LGARR",
      "userIdentite": "Laurent Garrigue",
      "userFonction": "Webmaster",
      "action": "Update logo equipes",
      "journal": "",
      "saison": "2025",
      "competition": "N1D",
      "journee": null,
      "match": null
    }
  ],
  "total": 1250,
  "page": 1,
  "limit": 50,
  "totalPages": 25
}
```

### 6.4 Reponse GET /admin/journal/users

```json
[
  {
    "code": "LGARR",
    "identite": "Laurent Garrigue",
    "fonction": "Webmaster"
  }
]
```

### 6.5 Reponse GET /admin/journal/actions

```json
[
  "Ajout Competition",
  "Ajout Equipe",
  "Ajout Journee",
  "Ajout Match",
  "Ajout Rc",
  "Calcul Classements",
  "Connexion",
  "Modif Competition",
  "Modification kp_competition_equipe",
  "Modification kp_match_joueur",
  "Modif Rc",
  "Supp Competition",
  "Supp Equipe"
]
```

### 6.6 Logique backend

**GET /admin/journal :**

```sql
SELECT j.Id, j.Dates, j.Users, j.Actions, j.Saisons, j.Competitions,
       j.Evenements, j.Journees, j.Matchs, j.Journal,
       u.Identite, u.Fonction
FROM kp_journal j
INNER JOIN kp_user u ON u.Code = j.Users
WHERE 1=1
  AND (:user IS NULL OR j.Users = :user)
  AND (:action IS NULL OR j.Actions LIKE :actionPattern)
  AND (:season IS NULL OR j.Saisons = :season)
  AND (:competition IS NULL OR j.Competitions LIKE :competitionPattern)
  AND (:search IS NULL OR (j.Journal LIKE :searchPattern
       OR j.Actions LIKE :searchPattern
       OR j.Competitions LIKE :searchPattern
       OR j.Matchs LIKE :searchPattern))
  AND (:dateFrom IS NULL OR j.Dates >= :dateFrom)
  AND (:dateTo IS NULL OR j.Dates <= :dateToEnd)
ORDER BY j.Dates DESC
```

- `actionPattern` : si `actionMode=prefix` → `action%`, si `actionMode=exact` → `action`
- `competitionPattern` : `competition%`
- `searchPattern` : `%search%`
- `dateToEnd` : `dateTo 23:59:59`

**Note LIMIT** : Conformement a la regle documentee dans MEMORY.md, le LIMIT ne doit PAS utiliser de placeholder pour MariaDB. Utiliser un cast en int et interpolation directe.

**GET /admin/journal/users :**

```sql
SELECT DISTINCT j.Users AS code, u.Identite AS identite, u.Fonction AS fonction
FROM kp_journal j
INNER JOIN kp_user u ON u.Code = j.Users
ORDER BY u.Identite
```

**GET /admin/journal/actions :**

```sql
SELECT DISTINCT j.Actions
FROM kp_journal j
ORDER BY j.Actions
```

### 6.7 Codes de retour

| Code | Cas |
|------|-----|
| 200 | Succes |
| 401 | Non authentifie |
| 403 | Profil insuffisant (> 2) |

---

## 7. Schema de donnees

### 7.1 Table `kp_journal` (existante, aucune modification)

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| Id | int(11) | Non | Cle primaire auto-increment |
| Dates | timestamp | Oui | Date et heure de l'action |
| Users | varchar(8) | Non | Code utilisateur (defaut: 'INCONNU') |
| Actions | varchar(40) | Non | Type d'action (defaut: 'IGNORE') |
| Saisons | varchar(4) | Oui | Code saison concernee |
| Competitions | varchar(8) | Oui | Code competition concernee |
| Evenements | int(11) | Oui | ID evenement concerne |
| Journees | int(11) | Oui | ID journee concernee |
| Matchs | text | Oui | ID(s) match(s) concerne(s) |
| Journal | text | Oui | Detail de l'action |

### 7.2 Jointure `kp_user`

| Colonne | Usage |
|---------|-------|
| Code | Jointure avec `kp_journal.Users` |
| Identite | Nom complet de l'utilisateur |
| Fonction | Fonction affichee en tooltip |

### 7.3 Index utilises

- `kp_journal.Id` : PK
- `kp_journal.Users` → `kp_user.Code` : JOIN + filtre utilisateur

---

## 8. Composants Vue

### 8.1 Structure des fichiers

```
sources/app4/pages/journal/
└── index.vue              # Page principale
```

### 8.2 Composants reutilises

| Composant | Usage |
|-----------|-------|
| `AdminToolbar` | Barre de recherche avec slots pour filtres |
| `AdminPagination` | Pagination avec selection du nombre par page |
| `AdminCard` | Affichage mobile en cards |

### 8.3 Pas de dependance au contexte de travail

Cette page est **globale** : elle n'utilise pas `workContextStore` et ne depend ni d'une saison ni d'un perimetre de competition. Les filtres saison/competition sont internes a la page.

---

## 9. Menu de Navigation

### 9.1 Emplacement

La page Journal est accessible depuis le menu sous "Administration" > "Journal d'activite". Elle n'apparait que pour les profils <= 2.

### 9.2 Definition

| Propriete | Valeur |
|-----------|--------|
| Label FR | Journal |
| Label EN | Activity Log |
| Route | `/journal` |
| Icone | `heroicons:document-text` |
| Profil min | <= 2 |

---

## 10. Traductions i18n

### 10.1 Cles francaises (`fr.json`)

```json
{
  "journal": {
    "title": "Journal d'activite",
    "search_placeholder": "Rechercher dans le journal...",
    "filters": {
      "user": "Utilisateur",
      "user_all": "Tous",
      "user_placeholder": "Filtrer par utilisateur...",
      "action": "Action",
      "action_all": "Toutes",
      "action_group_ensemble": "Ensemble",
      "action_group_detail": "Detail",
      "action_connexion": "Connexions",
      "action_ajout": "Ajouts",
      "action_modif": "Modifications",
      "action_supp": "Suppressions",
      "action_calcul": "Calculs",
      "season": "Saison",
      "season_all": "Toutes",
      "competition": "Competition",
      "competition_placeholder": "Code competition...",
      "date_from": "Du",
      "date_to": "Au"
    },
    "table": {
      "date": "Date",
      "identite": "Identite",
      "action": "Action",
      "detail": "Detail",
      "competition": "Comp.",
      "season": "Saison",
      "gameday": "Journ.",
      "match": "Match"
    },
    "empty": "Aucune entree dans le journal.",
    "empty_filtered": "Aucune entree ne correspond aux filtres.",
    "loading": "Chargement du journal...",
    "error": "Erreur lors du chargement du journal.",
    "total_entries": "{count} entree | {count} entrees"
  }
}
```

### 10.2 Cles anglaises (`en.json`)

```json
{
  "journal": {
    "title": "Activity Log",
    "search_placeholder": "Search in the log...",
    "filters": {
      "user": "User",
      "user_all": "All",
      "user_placeholder": "Filter by user...",
      "action": "Action",
      "action_all": "All",
      "action_group_ensemble": "Groups",
      "action_group_detail": "Detail",
      "action_connexion": "Logins",
      "action_ajout": "Additions",
      "action_modif": "Modifications",
      "action_supp": "Deletions",
      "action_calcul": "Calculations",
      "season": "Season",
      "season_all": "All",
      "competition": "Competition",
      "competition_placeholder": "Competition code...",
      "date_from": "From",
      "date_to": "To"
    },
    "table": {
      "date": "Date",
      "identite": "Identity",
      "action": "Action",
      "detail": "Detail",
      "competition": "Comp.",
      "season": "Season",
      "gameday": "Day",
      "match": "Match"
    },
    "empty": "No entries in the log.",
    "empty_filtered": "No entries match the filters.",
    "loading": "Loading activity log...",
    "error": "Error loading activity log.",
    "total_entries": "{count} entry | {count} entries"
  }
}
```

---

## 11. Securite

### 11.1 Controle d'acces

| Operation | Profil requis | Role Symfony |
|-----------|--------------|--------------|
| Consultation du journal | <= 2 | ROLE_ADMIN |

### 11.2 Page en lecture seule

Le journal est un audit log immutable. Aucune operation de creation, modification ou suppression n'est disponible. Il n'y a pas de checkbox de selection, pas de bouton d'ajout, pas d'actions contextuelles.

### 11.3 Filtres utilisateur

Les restrictions de l'utilisateur connecte (`Filtre_saison`, `Filtre_competition`) ne s'appliquent **pas** au journal. Un administrateur (profil <= 2) a acces a l'integralite du journal, tous utilisateurs, toutes saisons et competitions confondus.

---

## 12. Notes de migration

### 12.1 Differences avec le legacy

| Aspect | Legacy (PHP) | App4 (Nuxt) |
|--------|-------------|-------------|
| Layout | 2 colonnes (table + filtres a droite) | Filtres dans toolbar + table pleine largeur |
| Pagination | Select "Nb de lignes" (25-1000) | AdminPagination standard (page + limit) |
| Filtre utilisateur | Select charge au demarrage | Autocomplete avec recherche |
| Filtre saison | Input text libre | Select dropdown |
| Filtre competition | Input text libre | Input text (conserve prefix match) |
| Filtre date | Absent | Ajoute (date debut / date fin) |
| Recherche | Absente | Ajoutee (recherche dans journal/actions) |
| Tri | Date DESC fixe | Date DESC fixe (pas de tri utilisateur) |
| Retour utilisateur | Aucun (chargement page) | Toast / loading states |
| Mobile | Non responsive | Cards responsive |

### 12.2 Endpoints a creer dans API2

Creer un nouveau controller `AdminJournalController` dans `sources/api2/` avec les 3 endpoints decrits en section 6.

### 12.3 Pas de modification de schema

La table `kp_journal` reste inchangee. Aucune migration Doctrine n'est necessaire.

---

**Document cree le** : 20 fevrier 2026
**Derniere mise a jour** : 20 fevrier 2026
**Statut** : A VALIDER
**Auteur** : Claude Code
