# Spécification - Page Journées / Phases

## Statut : 📋 À IMPLÉMENTER

## 1. Vue d'ensemble

Page d'administration des journées et phases de compétition. Une "journée" représente une unité d'organisation dans le calendrier (une journée de championnat, une phase de tournoi, etc.). Chaque journée est rattachée à une compétition et une saison, et contient des matchs.

La page permet de lister, filtrer, créer, modifier, dupliquer et supprimer des journées, ainsi que de les associer à des événements.

**Route** : `/gamedays`

**Accès** :
- Profil ≤ 10 : Lecture (consultation de la liste)
- Profil ≤ 4 : Création, modification, duplication, publication, suppression
- Profil ≤ 3 : Sélection multiple (checkboxes), association événements
- Profil = 1 : Fonctions avancées (ICS, Google Calendar, ajustement dates)

**Page PHP Legacy** : `GestionCalendrier.php` + `GestionCalendrier.tpl` + `GestionCalendrier.js`

**Formulaire détaillé** : `GestionParamJournee.php` + `GestionParamJournee.tpl` + `GestionParamJournee.js`

**Implémentation Nuxt** : `sources/app4/pages/gamedays/index.vue` (actuellement legacy redirect)

**Gestion des officiels** : `GestionInstances.php` + `GestionInstances.tpl` + `GestionInstances.js`

**Contexte de travail** : Utilise le `workContextStore` global (saison + périmètre compétitions) + filtres locaux (événement, mois, tri)

---

## 2. Fonctionnalités

### 2.1 Fonctionnalités de la liste (GestionCalendrier)

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 1 | Liste des journées avec filtres (événement, compétition, mois, tri) | ≤ 10 | Essentielle | ✅ Conserver |
| 2 | Filtre par événement (dropdown) | ≤ 10 | Essentielle | ✅ Conserver |
| 3 | Filtre par compétition (select multiple, composant `AdminCompetitionMultiSelect` comme sur la page RC) | ≤ 10 | Essentielle | ✅ Conserver (modernisé) |
| 4 | Filtre par mois | ≤ 10 | Essentielle | ✅ Conserver |
| 5 | Tri configurable (date croissante/décroissante, nom, numéro, niveau) | ≤ 10 | Essentielle | ✅ Conserver |
| 6 | Toggle publication (œil) par clic AJAX | ≤ 4 | Essentielle | ✅ Conserver |
| 7 | Toggle type C/E (classification/élimination) par clic AJAX | ≤ 4 | Essentielle | ✅ Conserver |
| 8 | Édition inline : champs calendrier public (**Nom**, **Dates**, **Lieu**, **Dpt/Pays** — mis en valeur visuellement) + champs internes (Phase, Niveau, Étape, Nb équipes) | ≤ 4 | Essentielle | ✅ Conserver |
| 9 | Ajout d'une journée (redirection vers formulaire) | ≤ 4 | Essentielle | ✅ Conserver (modal) |
| 10 | Édition d'une journée (redirection vers formulaire) | ≤ 4 | Essentielle | ✅ Conserver (modal) |
| 11 | Duplication d'une journée | ≤ 4 | Essentielle | ✅ Conserver |
| 12 | Suppression individuelle | ≤ 4 | Essentielle | ✅ Conserver |
| 13 | Sélection multiple (checkboxes) | ≤ 3 | Essentielle | ✅ Conserver |
| 14 | Publication en masse (toggle multi-sélection) | ≤ 4 | Essentielle | ✅ Conserver |
| 15 | Suppression en masse | ≤ 4 | Essentielle | ✅ Conserver |
| 16 | Mode Association événements (checkbox par journée) | ≤ 3 | Essentielle | ✅ Conserver |
| 17 | Lien "Voir tous les matchs" | ≤ 10 | Utile | ✅ Conserver |
| 18 | Lien "Matchs" par journée | ≤ 10 | Essentielle | ✅ Conserver |
| 19 | Lien "Schéma de compétition" | ≤ 10 | Utile | ✅ Conserver |
| 20 | Lien ICS (import calendrier) | = 1 | Spécifique | ⏳ Différé (phase ultérieure) |
| 21 | Lien Google Calendar | = 1 | Spécifique | ⏳ Différé (phase ultérieure) |
| 22 | Lien QR Code App (pour compétitions CHPT) | ≤ 4 | Spécifique | ⏳ Différé (phase ultérieure) |
| 23 | Colonnes Niveau/Tour/Équipes (visibles uniquement si compétition type CP) | ≤ 10 | Essentielle | ✅ Conserver |
| 24 | Affichage des officiels (RC, R1, Délégué, Chef arbitres, etc.) | ≤ 10 | Essentielle | ✅ Conserver |
| 25 | Gestion des officiels (GestionInstances intégré) | ≤ 3 | Essentielle | ✅ Intégrer dans le formulaire modal |
| 26 | Filtre type compétition (AfficheCompet : N, CF, M, section) | ≤ 10 | Legacy | ✅ Remplacé par workContext |
| 27 | Colonne nombre de matchs par journée | ≤ 10 | Amélioration | ✅ Ajouter (nouveau) |
| 28 | Modification en masse des paramètres calendrier public (Nom, Dates, Lieu, Dpt/Pays) pour les journées cochées | ≤ 4 | Amélioration | ✅ Ajouter (nouveau) |

### 2.2 Fonctionnalités du formulaire détaillé (GestionParamJournee)

| # | Fonctionnalité | Profil | Évaluation | Décision |
|---|----------------|--------|------------|----------|
| 30 | Formulaire création/édition d'une journée | ≤ 4 | Essentielle | ✅ Conserver (modal/page) |
| 31 | Champs saison + compétition : éditables profil ≤ 2, **lecture seule** pour les autres profils | ≤ 4 | Essentielle | ✅ Conserver |
| 32 | Phase avec dropdown prédéfini (Poule A-Z, 1/4, 1/2, Finale, etc.) | ≤ 4 | Essentielle | ✅ Conserver |
| 33 | Niveau (1-29) | ≤ 4 | Essentielle | ✅ Conserver |
| 34 | Étape/Tour (1-19) | ≤ 4 | Essentielle | ✅ Conserver |
| 35 | Nombre d'équipes (1-19) | ≤ 4 | Essentielle | ✅ Conserver |
| 36 | Type C/E (radio) | ≤ 4 | Essentielle | ✅ Conserver |
| 37 | Dates début/fin (date picker) | ≤ 4 | Essentielle | ✅ Conserver |
| 38 | Nom de la journée (autocomplete sur kp_journee_ref) | ≤ 4 | Essentielle | ✅ Conserver |
| 39 | Lieu (autocomplete villes) | ≤ 4 | Essentielle | ✅ Conserver |
| 40 | Département/Pays | ≤ 4 | Essentielle | ✅ Conserver |
| 41 | Plan d'eau | ≤ 4 | Essentielle | ✅ Conserver |
| 42 | Organisateur (autocomplete clubs) | ≤ 4 | Essentielle | ✅ Conserver |
| 43 | Officiels : RC, R1, Délégué, Chef arbitres, Rep athlètes, Arb 1-5 (autocomplete joueurs) | ≤ 4 | Essentielle | ✅ Conserver |
| 44 | Badges RC (clic pour remplir le champ Responsable_insc) | ≤ 4 | Utile | ✅ Conserver |
| 45 | Duplication avec matchs (option) | ≤ 4 | Essentielle | ✅ Conserver |
| 46 | Encodage matchs de poule [T{id}/T{id}] | ≤ 4 | Spécifique | ✅ Conserver |
| 47 | Ajustement dates des matchs (Profile = 1) | = 1 | Spécifique | ✅ Conserver |
| 48 | Application en masse sur plusieurs journées (type CP) | ≤ 4 | Spécifique | ✅ Conserver |

### 2.3 Améliorations par rapport au legacy

| # | Amélioration | Description |
|---|--------------|-------------|
| 1 | Modal au lieu de redirection | Le formulaire d'ajout/édition sera dans une modal large au lieu d'une page séparée |
| 2 | Recherche textuelle | Ajouter une recherche sur Phase, Nom, Lieu, Id |
| 3 | Pagination | La liste legacy n'a pas de pagination (peut être longue) |
| 4 | Responsive mobile | Vue cartes pour mobile |
| 5 | Intégration workContext | Utiliser le contexte de travail global pour saison/périmètre compétitions |
| 6 | Filtre compétition multi-select | Utiliser `AdminCompetitionMultiSelect` (comme la page RC) au lieu d'un select simple |
| 7 | Colonne nombre de matchs | Afficher le nombre de matchs par journée |
| 8 | Mise en valeur calendrier public | Les colonnes Nom, Dates, Lieu, Dpt/Pays sont visuellement distinguées (en-têtes colorés, comme le legacy avec `colorPublic`) |
| 9 | Modification en masse calendrier public | Permettre de modifier Nom, Dates, Lieu, Dpt/Pays pour toutes les journées cochées |
| 10 | Officiels intégrés | Fusionner GestionInstances dans le formulaire modal (section collapsible avec badges RC) |
| 11 | Association événements modernisée | Panel/modal dédié au lieu d'un mode radio global |
| 12 | Date picker moderne | Composant Nuxt UI au lieu de Flatpickr |
| 13 | Feedback immédiat | Toast notifications au lieu d'alerts JavaScript |
| 14 | Colonnes officiels condensées | Afficher un résumé cliquable au lieu de lister tous les noms |

---

## 3. Structure de la Page

### 3.1 Vue Desktop

```
┌──────────────────────────────────────────────────────────────────────────────────┐
│  [Contexte de travail : Saison 2026 | National 1 Hommes (12 compétitions)]      │
├──────────────────────────────────────────────────────────────────────────────────┤
│  Journées / Phases                                                               │
├──────────────────────────────────────────────────────────────────────────────────┤
│  [Événement: ▼ Tous ] [Compétitions: ▼ Multi-select (3)]  [Mois: ▼] [Tri: ▼]  │
│  [🔍 Recherche...                     ]                          [+ Ajouter]    │
├──────────────────────────────────────────────────────────────────────────────────┤
│  Sélection: [✓ Tous] [✗ Aucun] [👁 Publier] [📝 Modifier cal. public] [🗑 Sup]│
│  [🔗 Gérer associations événement] (profil ≤ 3, si événement sélectionné)       │
├──────────────────────────────────────────────────────────────────────────────────┤
│  │☐│👁│ Id  │   │ Compét./Phase    │Niv│Tour│Éq│Type│ *Nom*          │ Matchs │ │
│  │  │  │     │   │                  │   │    │  │    │ (cal.public)   │        │ │
│  │☐│🟢│ 8642│✏🗐📋│ T-COR - Poule A │ 1 │ 1  │ 5│ ≡  │ Tournoi Pierre │    8   │ │
│  │☐│🟢│ 8643│✏🗐📋│ T-COR - Poule B │ 1 │ 1  │ 5│ ≡  │ Tournoi Pierre │   10   │ │
│  │☐│⚫│ 8644│✏🗐📋│ T-COR - Barrage │ 2 │ 2  │ 2│ ⇄  │ Tournoi Pierre │    2   │ │
│  ...                                                                             │
│          suite des colonnes (calendrier public mis en valeur ↓) →                │
│  │ *Date*                │ *Lieu*           │*Dpt*│ Officiels       │ 🗑 │       │
│  │ 11/01/2025-12/01/2025 │ Corbeil-Essonnes │ 91  │ RC: M. PREVOST  │ 🗑 │       │
│  │ 11/01/2025-12/01/2025 │ Corbeil-Essonnes │ 91  │ RC: M. PREVOST  │ 🗑 │       │
│  ...                                                                             │
├──────────────────────────────────────────────────────────────────────────────────┤
│  Pagination: [< 1 2 3 >]  Afficher: [25 ▼]  Total: 75 journées                 │
└──────────────────────────────────────────────────────────────────────────────────┘
```

**Colonnes calendrier public** : Les colonnes **Nom**, **Date**, **Lieu**, **Dpt/Pays** sont visuellement distinguées (en-têtes avec un indicateur coloré vert, comme le legacy qui utilise la classe `colorPublic`). Cela indique aux utilisateurs que ces champs impactent le calendrier public affiché sur le site.

### 3.2 Mode Association Événements

Quand le mode "Association événements" est activé (profil ≤ 3) :
- La colonne d'actions (✏🗐📋) est remplacée par une checkbox
- La checkbox est cochée si la journée est associée à l'événement sélectionné
- Le clic sur la checkbox ajoute/retire l'association via AJAX
- Le fond de la checkbox est rouge pour signaler le mode spécial

```
│☐│👁│ Id  │ ☑ │ Compét./Phase    │ Type │ Nom            │ Date       │ ...
│  │🟢│ 8642│ ✓ │ T-COR - Poule A │ ≡    │ Tournoi Pierre │ 11/01-12/01│ ...
│  │🟢│ 8643│ ✗ │ T-COR - Poule B │ ≡    │ Tournoi Pierre │ 11/01-12/01│ ...
```

### 3.3 Vue Mobile (cartes)

```
┌──────────────────────────────────┐
│  Journées / Phases               │
├──────────────────────────────────┤
│  [Événement: ▼ Tous            ] │
│  [Compétition: ▼ Toutes        ] │
│  [Mois: ▼] [Tri: ▼]  [+ Ajouter]│
│  [🔍 Recherche...              ] │
├──────────────────────────────────┤
│  ┌────────────────────────────┐  │
│  │ #8642 - T-COR - Poule A   │  │
│  │ 👁 Publié  │ Type: ≡ Clt  │  │
│  │ Niv: 1 │ Tour: 1 │ Éq: 5 │  │
│  │ Nom: Tournoi Pierre Bret. │  │
│  │ 📅 11/01 - 12/01/2025     │  │
│  │ 📍 Corbeil-Essonnes (91)  │  │
│  │ RC: Michel PREVOST         │  │
│  │ [✏ Éditer] [📋 Matchs] [🗑]│  │
│  └────────────────────────────┘  │
│  ┌────────────────────────────┐  │
│  │ #8643 - T-COR - Poule B   │  │
│  │ ...                        │  │
│  └────────────────────────────┘  │
└──────────────────────────────────┘
```

### 3.4 Colonnes conditionnelles (Type CP)

Les colonnes **Niveau**, **Tour** et **Équipes** ne sont affichées que lorsqu'une compétition **unique** de type `Code_typeclt = 'CP'` (système de jeu à phases) est sélectionnée. Sinon, ces colonnes sont masquées.

---

## 4. Filtres

### 4.1 Filtre Événement

| Propriété | Valeur |
|-----------|--------|
| Source | `kp_evenement` ORDER BY Date_debut DESC, Libelle |
| Option par défaut | "* - Tous les événements" (valeur: -1) |
| Format | "{Id} - {Libelle}" |
| Persistance | localStorage |
| Impact | Filtre les journées via `kp_evenement_journee` |

### 4.2 Filtre Compétition (Multi-select)

| Propriété | Valeur |
|-----------|--------|
| Composant | `AdminCompetitionMultiSelect` (même que page RC) |
| Source | `workContext.competitions` (déjà chargées via workContextStore) |
| Sélection | Multiple (checkboxes avec compteur de sélection) |
| Groupement | Par section (International, National, Régional, etc.) |
| Par défaut | Toutes les compétitions (aucune sélection = pas de filtre) |
| Filtre utilisateur | Respecte `Filtre_competition` via workContext |
| Persistance | localStorage |
| Impact | Filtre sur `Code_competition IN (...)` |
| UI | Section collapsible avec chevron, badge compteur "3 sélectionnées" |

### 4.3 Filtre Mois

| Propriété | Valeur |
|-----------|--------|
| Options | Tous (vide), Janvier (1) ... Décembre (12) |
| Impact | Filtre sur `MONTH(Date_debut)` ou `MONTH(Date_fin)` |
| Persistance | localStorage |

### 4.4 Tri

| Option | Clé SQL legacy | Description |
|--------|---------------|-------------|
| Par date croissante | `Date_debut, Niveau, Phase, Lieu, Libelle, Id` | Défaut |
| Par date décroissante | `Date_debut DESC, Niveau, Phase, Lieu, Libelle` | |
| Par nom | `Libelle, Niveau, Phase` | |
| Par numéro | `Id, Niveau, Phase` | |
| Par niveau | `Niveau, Phase, Date_debut` | |

### 4.5 Intégration avec le contexte de travail

**Décision** : Utiliser le `workContextStore` global pour la saison et le périmètre de compétitions, comme les pages Compétitions, Documents et Équipes. Les filtres suivants restent locaux à la page :

- **Événement** : filtre local (dropdown)
- **Mois** : filtre local (dropdown)
- **Tri** : filtre local (dropdown)
- **Recherche** : filtre local (texte)

Le rappel du contexte de travail est affiché en haut de page (pattern standard, voir APP4_STRUCTURE.md §9).

---

## 5. Modes de fonctionnement

### 5.1 Mode Normal (par défaut)

- La liste affiche les journées filtrées
- Actions par ligne : Éditer, Dupliquer, Voir matchs, Supprimer
- Toggle publication et type par clic
- Édition inline des champs texte

### 5.2 Mode Association Événements (modernisé)

Le mode association est **modernisé** par rapport au legacy (qui utilisait un radio button global basculant toute la page).

**Nouvelle approche** : Un panel dédié ou une modal de type "gestionnaire d'associations" :

1. L'utilisateur sélectionne un événement dans le filtre
2. Un bouton "Gérer les associations" (profil ≤ 3) ouvre un panel/modal dédié
3. Le panel affiche la liste des journées avec des checkboxes
4. Les journées déjà associées sont pré-cochées
5. L'utilisateur peut filtrer/rechercher dans le panel
6. Les modifications sont envoyées en AJAX (toggle individuel)
7. Un compteur affiche le nombre de journées associées

**Avantages vs legacy** :
- Pas de changement de mode global qui modifie toute la page
- Interface dédiée plus claire
- Possibilité de voir simultanément les journées associées et non associées

---

## 6. Édition inline

### 6.1 Champs éditables dans le tableau

Les champs suivants sont éditables par clic direct dans le tableau (classe `.editable-cell`) :

**Champs calendrier public** (en-têtes et cellules visuellement distingués en vert) :

| Champ | Type d'input | Taille | Validation | Impact public |
|-------|-------------|--------|------------|---------------|
| **Nom** | text (longtext) | 20 chars | Texte libre | ✅ Calendrier public |
| **Date début** | date picker | date FR/EN | Date valide | ✅ Calendrier public |
| **Date fin** | date picker | date FR/EN | Date valide | ✅ Calendrier public |
| **Lieu** | text (longtext) | 20 chars | Texte libre | ✅ Calendrier public |
| **Département** | text (dpt) | 3 chars max | Alphanumérique majuscule | ✅ Calendrier public |

**Champs internes** (style standard) :

| Champ | Type d'input | Taille | Validation |
|-------|-------------|--------|------------|
| Phase | text (longtext) | 20 chars | Texte libre |
| Niveau | tel (numérique) | 2 chars | Entier ≥ 1 |
| Étape (Tour) | tel (numérique) | 2 chars | Entier ≥ 1 |
| Nb équipes | tel (numérique) | 2 chars | Entier ≥ 1 |

### 6.2 Comportement

1. Clic sur une cellule → le `<span>` est remplacé par un `<input>`
2. L'input est auto-sélectionné
3. Appui Entrée ou perte de focus → sauvegarde AJAX si la valeur a changé
4. Pour les champs date : utilise un date picker (FR: dd/mm/yyyy, EN: yyyy-mm-dd)
5. Pour les champs numériques : valeur minimum 1, uniquement chiffres

### 6.3 Endpoint AJAX

```
GET /api2/admin/gamedays/{id}/inline
  ?field={columnName}
  &value={newValue}
```

Legacy utilise `UpdateCellJQ.php` avec une table whitelist. En API2, créer un endpoint dédié avec validation des champs autorisés.

---

## 7. Modal Ajout / Édition (Formulaire détaillé)

### 7.1 Champs du formulaire

| Champ | Type | Requis | Profil | Validation | Description |
|-------|------|--------|--------|------------|-------------|
| Saison | Select | Oui | ≤ 2 | Années > 1900 | Code saison (ex: 2026) |
| Compétition | Select groupé | Oui | ≤ 2 | Existante dans saison | Code compétition |
| Phase | Text + Select | Oui | ≤ 4 | Max 30 chars | Nom de la phase (avec suggestions prédéfinies) |
| Niveau | Select | Oui | ≤ 4 | 1-29 | Importance dans le classement général |
| Étape/Tour | Select | Oui | ≤ 4 | 1-19 | Position dans le schéma de jeu |
| Nb équipes | Select | Oui | ≤ 4 | 1-19 | Nombre d'équipes dans la phase |
| Type | Radio | Oui | ≤ 4 | C ou E | Classification ou Élimination |
| Date début | Date | Oui | ≤ 4 | Date valide | Début de la journée |
| Date fin | Date | Oui | ≤ 4 | Date valide, ≥ début | Fin de la journée |
| Nom | Text | Non | ≤ 4 | Max 80 chars | Nom public (autocomplete kp_journee_ref) |
| Libellé | Text | Non | ≤ 4 | Max 80 chars | Description longue |
| Lieu | Text | Non | ≤ 4 | Max 40 chars | Ville (autocomplete villes) |
| Plan d'eau | Text | Non | ≤ 4 | Max 80 chars | Nom du plan d'eau |
| Département | Text | Non | ≤ 4 | Max 3 chars | Code département ou pays |
| Organisateur | Text | Non | ≤ 4 | Max 40 chars | Club organisateur (autocomplete clubs) |
| RC (Resp. inscriptions) | Text | Non | ≤ 4 | Max 80 chars | Autocomplete joueurs |
| R1 (Responsable) | Text | Non | ≤ 4 | Max 80 chars | Autocomplete joueurs |
| Délégué | Text | Non | ≤ 4 | Max 80 chars | Autocomplete joueurs |
| Chef arbitres | Text | Non | ≤ 4 | Max 80 chars | Autocomplete joueurs |
| Rep. athlètes | Text | Non | ≤ 4 | Max 80 chars | Autocomplete joueurs |
| Arbitres NJ 1-5 | Text ×5 | Non | ≤ 4 | Max 80 chars chacun | Autocomplete joueurs |

### 7.2 Suggestions prédéfinies pour le champ Phase

**Français :**
- Poules : "Poule A", "Poule B", ... "Poule Z"
- Finales : "1/8 Finale", "1/4 Finale", "1/2 Finale", "Finale"
- Places : "3ème Place", "5ème Place", ... "27ème Place"
- Barrages : "Barrage"
- Pause : "Pause"

**Anglais :**
- Groups : "Group A", "Group B", ... "Group Z"
- Finals : "Round of 16", "Quarter-final", "Semi-final", "Final"
- Places : "3rd Place", "5th Place", ... "27th Place"
- Playoffs : "Playoff"
- Break : "Break"

### 7.3 Section Officiels (intégration GestionInstances)

La gestion des officiels, précédemment dans une page séparée (GestionInstances.php), est **intégrée dans le formulaire modal** sous forme d'une section collapsible.

#### Structure de la section

```
── Comité de compétition ─────────────────────────
  RC (Resp. inscriptions) : [autocomplete joueur]  [Badges RC: ①②③ ]
  R1 (Responsable)        : [autocomplete joueur]
  Délégué fédéral         : [autocomplete joueur]
  Chef arbitres           : [autocomplete joueur]

── Jury d'appel ──────────────────────────────────
  Délégué     : (auto-rempli depuis ci-dessus)
  R1          : (auto-rempli depuis ci-dessus)
  Rep. athlètes : [autocomplete joueur]

── Arbitres non-joueurs ──────────────────────────
  Arbitre NJ 1 : [autocomplete joueur]
  Arbitre NJ 2 : [autocomplete joueur]
  Arbitre NJ 3 : [autocomplete joueur]
  Arbitre NJ 4 : [autocomplete joueur]
  Arbitre NJ 5 : [autocomplete joueur]
```

#### Badges RC

- Les RC (Responsables de Compétition) assignés à la compétition sont affichés sous forme de badges cliquables
- Le clic sur un badge remplit automatiquement le champ RC
- Source : table `kp_rc` filtrée par compétition + saison
- Format badge : numéro d'ordre, tooltip "Prénom Nom (Matricule)"

#### Jury d'appel (champs liés)

Les champs Délégué et R1 du Jury d'appel sont des **copies en lecture seule** des champs correspondants du Comité de compétition. Ils se mettent à jour automatiquement quand les champs source changent.

#### Lien PDF

Un bouton "Exporter PDF Instances" génère le document `FeuilleInstances.php` pour impression.

### 7.4 Options de duplication (dans le formulaire)

Lors de la création à partir d'une duplication :

| Option | Description | Condition |
|--------|-------------|-----------|
| Inclure les matchs | Copie les matchs de la journée source | Checkbox |
| Encoder matchs de poule | Ajoute `[T{idA}/T{idB}]` aux libellés des matchs | Checkbox, visible si Niveau ≤ 1 ET "Inclure matchs" coché |

### 7.5 Section "Appliquer à d'autres phases" (Type CP uniquement)

Visible uniquement pour les compétitions de type `Code_typeclt = 'CP'` et profil ≤ 4.

Permet de cocher d'autres journées de la même compétition et d'y appliquer les mêmes valeurs de calendrier et officiels (Date, Nom, Lieu, Département, Plan d'eau, tous les officiels).

### 7.6 Ajustement des dates des matchs

Bouton visible uniquement pour profil = 1. Met à jour `Date_match` de tous les matchs de la journée avec la valeur `Date_debut` de la journée.

---

## 8. Actions

### 8.1 Publication (Toggle)

| Action | Profil | Méthode |
|--------|--------|---------|
| Toggle publication individuelle | ≤ 4 | Clic sur l'icône œil → AJAX PATCH |
| Toggle publication en masse | ≤ 4 | Sélection + bouton Publier |

- Valeur 'O' = publié (public), 'N' = non publié (privé)
- L'icône change immédiatement (feedback optimiste)

### 8.2 Type (Toggle C/E)

| Action | Profil | Méthode |
|--------|--------|---------|
| Toggle type | ≤ 4 | Clic sur l'icône type → AJAX PATCH |

- 'C' = Classification (icône barres horizontales)
- 'E' = Élimination (icône flèches croisées)

### 8.3 Suppression

**Vérification préalable :**
1. Vérifier qu'il n'y a pas de matchs dans la journée (`kp_match.Id_journee`)
2. Vérifier qu'il n'y a pas d'association événement (`kp_evenement_journee.Id_journee`)

**Si des matchs existent :**
- Retour HTTP 409
- Message : "Il reste des matchs dans cette journée ! Suppression impossible."

**Si des associations événement existent :**
- Retour HTTP 409
- Message : "Cette journée est associée à un événement ! Suppression impossible."

**Suppression en masse :** Même vérifications pour chaque journée sélectionnée.

### 8.4 Modification en masse du calendrier public

Lorsque des journées sont sélectionnées (checkboxes), un bouton "Modifier calendrier public" permet de modifier les paramètres publics pour toutes les journées sélectionnées.

**Modal de modification en masse :**

| Champ | Type | Description |
|-------|------|-------------|
| Nom | Text | Nom de la journée (calendrier public) |
| Date début | Date | Date de début |
| Date fin | Date | Date de fin |
| Lieu | Text | Ville |
| Dpt/Pays | Text (3 chars) | Code département ou pays |

**Comportement :**
- Les champs vides ne sont PAS appliqués (seuls les champs remplis sont modifiés)
- Confirmation avant application : "Modifier {n} journées sélectionnées ?"
- Feedback : toast de succès avec le nombre de journées modifiées

**Endpoint :**
```
PATCH /admin/gamedays/bulk/calendar
Body: { "ids": [8642, 8643], "nom": "...", "dateDebut": "...", "dateFin": "...", "lieu": "...", "departement": "..." }
```

### 8.5 Duplication

1. Confirmation dialog : "Confirmer la duplication ?"
2. Copie de tous les champs de la journée avec un nouvel Id
3. Option de copier également les matchs (via le formulaire détaillé)

### 8.6 Association événement (Panel modernisé)

| Action | Méthode |
|--------|---------|
| Cocher checkbox | REPLACE INTO `kp_evenement_journee` |
| Décocher checkbox | DELETE FROM `kp_evenement_journee` |

---

## 9. Endpoints API2

### 9.1 Lecture

| Méthode | Endpoint | Description | Paramètres |
|---------|----------|-------------|------------|
| GET | `/admin/gamedays` | Liste des journées | `?season=`, `?competition=`, `?event=`, `?month=`, `?sort=`, `?page=`, `?limit=`, `?search=` |
| GET | `/admin/gamedays/{id}` | Détail d'une journée | - |

**Réponse GET /admin/gamedays :**
```json
{
  "gamedays": [
    {
      "id": 8642,
      "codeCompetition": "T-COR",
      "codeSaison": "2025",
      "phase": "Poule A",
      "niveau": 1,
      "etape": 1,
      "nbEquipes": 5,
      "type": "C",
      "dateDebut": "2025-01-11",
      "dateFin": "2025-01-12",
      "nom": "Tournoi Pierre Bretenoux",
      "libelle": "",
      "lieu": "Corbeil-Essonnes",
      "planEau": "",
      "departement": "91",
      "responsableInsc": "Michel PREVOST",
      "responsableR1": "",
      "organisateur": "Organisation: Michel PREVOST",
      "delegue": "",
      "chefArbitre": "",
      "repAthletes": "",
      "arbNj1": "",
      "arbNj2": "",
      "arbNj3": "",
      "arbNj4": "",
      "arbNj5": "",
      "publication": "O",
      "matchCount": 8,
      "authorized": true,
      "competitionTypeClt": "CP"
    }
  ],
  "total": 75,
  "page": 1,
  "totalPages": 3
}
```

### 9.2 Écriture

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| POST | `/admin/gamedays` | Créer une journée | ≤ 4 |
| PUT | `/admin/gamedays/{id}` | Modifier une journée | ≤ 4 |
| PATCH | `/admin/gamedays/{id}/publication` | Toggle publication | ≤ 4 |
| PATCH | `/admin/gamedays/{id}/type` | Toggle type C/E | ≤ 4 |
| PATCH | `/admin/gamedays/{id}/inline` | Mise à jour inline | ≤ 4 |
| POST | `/admin/gamedays/{id}/duplicate` | Dupliquer | ≤ 4 |
| DELETE | `/admin/gamedays/{id}` | Supprimer | ≤ 4 |

### 9.3 Actions en masse

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| PATCH | `/admin/gamedays/bulk/publication` | Toggle publication en masse | ≤ 4 |
| PATCH | `/admin/gamedays/bulk/calendar` | Modifier paramètres calendrier public en masse | ≤ 4 |
| DELETE | `/admin/gamedays/bulk` | Suppression en masse | ≤ 4 |

**Body PATCH /admin/gamedays/bulk/publication :**
```json
{
  "ids": [8642, 8643, 8644]
}
```

**Body PATCH /admin/gamedays/bulk/calendar :**
```json
{
  "ids": [8642, 8643, 8644],
  "nom": "Tournoi Pierre Bretenoux",
  "dateDebut": "2025-01-11",
  "dateFin": "2025-01-12",
  "lieu": "Corbeil-Essonnes",
  "departement": "91"
}
```
Seuls les champs non vides/non null sont appliqués.

### 9.4 Association événements

| Méthode | Endpoint | Description | Profil |
|---------|----------|-------------|--------|
| PUT | `/admin/gamedays/{id}/event/{eventId}` | Associer journée à événement | ≤ 3 |
| DELETE | `/admin/gamedays/{id}/event/{eventId}` | Dissocier journée d'événement | ≤ 3 |

### 9.5 Autocomplete

| Méthode | Endpoint | Description |
|---------|----------|-------------|
| GET | `/admin/autocomplete/cities?q=` | Villes (pour Lieu) |
| GET | `/admin/autocomplete/gameday-names?q=` | Noms de journées (kp_journee_ref) |
| GET | `/admin/autocomplete/clubs?q=` | Clubs (pour Organisateur) |
| GET | `/admin/autocomplete/players?q=` | Joueurs (pour Officiels) |

### 9.6 Codes de retour

| Code | Cas |
|------|-----|
| 200 | Succès (GET, PUT, PATCH) |
| 201 | Création réussie (POST) |
| 204 | Suppression réussie (DELETE) |
| 400 | Données invalides |
| 403 | Profil insuffisant |
| 404 | Journée non trouvée |
| 409 | Conflit : matchs ou événements liés (DELETE) |

---

## 10. Schéma de données

### 10.1 Table `kp_journee`

| Colonne | Type | Null | Description |
|---------|------|------|-------------|
| Id | int(11) | Non | Clé primaire |
| Code_competition | varchar(12) | Non | FK → kp_competition.Code |
| Code_saison | char(4) | Non | FK → kp_saison.Code |
| Date_debut | date | Oui | Date de début |
| Date_fin | date | Oui | Date de fin |
| Nom | varchar(80) | Oui | Nom affiché dans le calendrier public |
| Libelle | varchar(80) | Oui | Description longue |
| Lieu | varchar(40) | Oui | Ville |
| Departement | varchar(3) | Oui | Code département ou pays |
| Plan_eau | varchar(80) | Oui | Nom du plan d'eau |
| Phase | varchar(30) | Oui | Nom de la phase (Poule A, Finale, etc.) |
| Niveau | smallint(6) | Oui | Importance dans le classement (1 = premier niveau) |
| Etape | smallint(6) | Non | Tour dans le schéma de jeu (défaut: 1) |
| Nbequipes | smallint(6) | Non | Nombre d'équipes (défaut: 1) |
| Type | char(1) | Non | 'C' = Classification, 'E' = Élimination (défaut: C) |
| Etat | char(1) | Oui | État de la journée |
| Responsable_insc | varchar(80) | Oui | Responsable inscriptions |
| Responsable_R1 | varchar(80) | Oui | R1 |
| Organisateur | varchar(40) | Oui | Club organisateur |
| Delegue | varchar(80) | Oui | Délégué fédéral |
| ChefArbitre | varchar(80) | Oui | Chef des arbitres |
| Rep_athletes | varchar(80) | Oui | Représentant des athlètes |
| Arb_nj1 | varchar(80) | Oui | Arbitre 1 |
| Arb_nj2 | varchar(80) | Oui | Arbitre 2 |
| Arb_nj3 | varchar(80) | Oui | Arbitre 3 |
| Arb_nj4 | varchar(80) | Oui | Arbitre 4 |
| Arb_nj5 | varchar(80) | Oui | Arbitre 5 |
| Code_organisateur | varchar(5) | Oui | Code du club organisateur |
| Validation | char(1) | Oui | Validation de la journée |
| Code_uti | varchar(8) | Oui | Code utilisateur dernière modification |
| Publication | char(1) | Oui | 'O' = publié, 'N' ou NULL = non publié |
| Id_dupli | int(11) | Oui | Id de la journée dupliquée |
| Public_prin | char(1) | Non | Calendrier public principal (défaut: O) |
| Public_sec | char(1) | Non | Calendrier public secondaire (défaut: O) |

### 10.2 Table `kp_evenement_journee` (association N:N)

| Colonne | Type | Description |
|---------|------|-------------|
| Id_evenement | int(11) | FK → kp_evenement.Id |
| Id_journee | int(11) | FK → kp_journee.Id |

Clé primaire composite : (Id_evenement, Id_journee)

### 10.3 Table `kp_journee_ref` (autocomplete noms)

| Colonne | Type | Description |
|---------|------|-------------|
| id | int(11) | Clé primaire auto-incrémentée |
| nom | varchar(40) | Nom prédéfini de journée |

### 10.4 Contraintes de clé étrangère

```sql
-- Journée → Compétition + Saison
ALTER TABLE kp_journee
  ADD CONSTRAINT fk_journees_competitions
  FOREIGN KEY (Code_competition, Code_saison)
  REFERENCES kp_competition (Code, Code_saison);

-- Matchs → Journée
ALTER TABLE kp_match
  ADD CONSTRAINT fk_matchs_journee
  FOREIGN KEY (Id_journee) REFERENCES kp_journee (Id);

-- Événement ↔ Journée
ALTER TABLE kp_evenement_journee
  ADD CONSTRAINT fk_evenements_journee
  FOREIGN KEY (Id_journee) REFERENCES kp_journee (Id);
ALTER TABLE kp_evenement_journee
  ADD CONSTRAINT fk_evenements_evenement
  FOREIGN KEY (Id_evenement) REFERENCES kp_evenement (Id);
```

---

## 11. Composants Vue

### 11.1 Structure des fichiers

```
sources/app4/pages/gamedays/
└── index.vue              # Page principale (liste + modals)
```

### 11.2 Composants réutilisés

| Composant | Usage |
|-----------|-------|
| `AdminToolbar` | Barre de recherche + bouton Ajouter |
| `AdminModal` | Modal ajout/édition de journée |
| `AdminConfirmModal` | Confirmation suppression |
| `AdminToggleButton` | Toggle publication (œil) |
| `AdminPagination` | Pagination de la liste |

### 11.3 Dépendance au contexte de travail

Cette page utilise :
- `workContextStore.season` pour la saison active
- `workContextStore.competitionCodes` pour le filtrage par compétition (si applicable)
- Filtres locaux additionnels : événement, mois, tri

---

## 12. Traductions i18n

### 12.1 Clés françaises (`fr.json`)

```json
{
  "menu": {
    "gamedays": "Journées / Phases"
  },
  "gamedays": {
    "title": "Journées / Phases",
    "add": "Ajouter une journée",
    "edit": "Modifier la journée",
    "delete_confirm_title": "Supprimer la journée",
    "delete_confirm_message": "Êtes-vous sûr de vouloir supprimer la journée #{id} ({phase}) ?",
    "delete_error_matches": "Il reste des matchs dans cette journée ! Suppression impossible.",
    "delete_error_events": "Cette journée est associée à un événement ! Suppression impossible.",
    "duplicate_confirm": "Confirmer la duplication de la journée #{id} ?",
    "publish_confirm": "Confirmer la publication/dépublication des journées sélectionnées ?",
    "all_events": "Tous les événements",
    "all_competitions": "Toutes les compétitions",
    "all_months": "Tous les mois",
    "mode_normal": "Mode normal",
    "mode_association": "Association événements",
    "sort": {
      "date_asc": "Par date croissante",
      "date_desc": "Par date décroissante",
      "name": "Par nom",
      "number": "Par numéro",
      "level": "Par niveau"
    },
    "field": {
      "id": "Id",
      "competition": "Compétition",
      "season": "Saison",
      "phase": "Phase",
      "niveau": "Niveau",
      "etape": "Tour",
      "nb_equipes": "Équipes",
      "type": "Type",
      "type_c": "Classification",
      "type_e": "Élimination",
      "date_debut": "Date début",
      "date_fin": "Date fin",
      "nom": "Nom",
      "libelle": "Libellé",
      "lieu": "Lieu",
      "departement": "Dpt/Pays",
      "plan_eau": "Plan d'eau",
      "organisateur": "Organisateur",
      "responsable_insc": "Resp. inscriptions (RC)",
      "responsable_r1": "R1",
      "delegue": "Délégué",
      "chef_arbitre": "Chef arbitres",
      "rep_athletes": "Rep. athlètes",
      "arb_nj": "Arbitre NJ",
      "officiels": "Officiels",
      "publication": "Publication"
    },
    "published": "Publié",
    "unpublished": "Non publié",
    "view_matches": "Voir les matchs",
    "view_all_matches": "Voir tous les matchs",
    "competition_schema": "Schéma de compétition",
    "include_matches": "Inclure les matchs",
    "encode_pool_matches": "Encoder les matchs de poule",
    "adjust_dates": "Ajuster les dates des matchs",
    "apply_to_others": "Appliquer à d'autres phases",
    "added": "Journée créée.",
    "updated": "Journée modifiée.",
    "deleted": "Journée supprimée.",
    "duplicated": "Journée dupliquée.",
    "event_linked": "Journée associée à l'événement.",
    "event_unlinked": "Journée dissociée de l'événement."
  }
}
```

### 12.2 Clés anglaises (`en.json`)

```json
{
  "menu": {
    "gamedays": "Gamedays / Phases"
  },
  "gamedays": {
    "title": "Gamedays / Phases",
    "add": "Add a gameday",
    "edit": "Edit gameday",
    "delete_confirm_title": "Delete gameday",
    "delete_confirm_message": "Are you sure you want to delete gameday #{id} ({phase})?",
    "delete_error_matches": "Matches still exist in this gameday! Cannot delete.",
    "delete_error_events": "This gameday is linked to an event! Cannot delete.",
    "duplicate_confirm": "Confirm duplication of gameday #{id}?",
    "publish_confirm": "Confirm publish/unpublish for selected gamedays?",
    "all_events": "All events",
    "all_competitions": "All competitions",
    "all_months": "All months",
    "mode_normal": "Normal mode",
    "mode_association": "Event association",
    "sort": {
      "date_asc": "By date (ascending)",
      "date_desc": "By date (descending)",
      "name": "By name",
      "number": "By number",
      "level": "By level"
    },
    "field": {
      "id": "Id",
      "competition": "Competition",
      "season": "Season",
      "phase": "Phase",
      "niveau": "Level",
      "etape": "Stage",
      "nb_equipes": "Teams",
      "type": "Type",
      "type_c": "Classification",
      "type_e": "Elimination",
      "date_debut": "Start date",
      "date_fin": "End date",
      "nom": "Name",
      "libelle": "Label",
      "lieu": "Location",
      "departement": "Dept/Country",
      "plan_eau": "Water body",
      "organisateur": "Organizer",
      "responsable_insc": "Registration resp. (RC)",
      "responsable_r1": "R1",
      "delegue": "Delegate",
      "chef_arbitre": "Chief referee",
      "rep_athletes": "Athletes rep.",
      "arb_nj": "Umpire",
      "officiels": "Officials",
      "publication": "Publication"
    },
    "published": "Published",
    "unpublished": "Unpublished",
    "view_matches": "View matches",
    "view_all_matches": "View all matches",
    "competition_schema": "Competition chart",
    "include_matches": "Include matches",
    "encode_pool_matches": "Encode pool matches",
    "adjust_dates": "Adjust match dates",
    "apply_to_others": "Apply to other phases",
    "added": "Gameday created.",
    "updated": "Gameday updated.",
    "deleted": "Gameday deleted.",
    "duplicated": "Gameday duplicated.",
    "event_linked": "Gameday linked to event.",
    "event_unlinked": "Gameday unlinked from event."
  }
}
```

---

## 13. Sécurité

### 13.1 Contrôle d'accès

| Opération | Profil requis | Rôle Symfony |
|-----------|--------------|--------------|
| Lecture liste | ≤ 10 | ROLE_USER |
| Toggle publication | ≤ 4 | ROLE_COMPETITION |
| Toggle type C/E | ≤ 4 | ROLE_COMPETITION |
| Édition inline | ≤ 4 | ROLE_COMPETITION |
| Ajout | ≤ 4 | ROLE_COMPETITION |
| Modification | ≤ 4 | ROLE_COMPETITION |
| Duplication | ≤ 4 | ROLE_COMPETITION |
| Suppression | ≤ 4 | ROLE_COMPETITION |
| Sélection multiple | ≤ 3 | ROLE_DIVISION |
| Association événements | ≤ 3 | ROLE_DIVISION |
| Modification Saison/Compétition | ≤ 2 | ROLE_ADMIN |
| Ajustement dates matchs | = 1 | ROLE_SUPER_ADMIN |

### 13.2 Autorisation par journée

En plus du profil, le backend vérifie `utyIsAutorisationJournee(idJournee)` qui contrôle que l'utilisateur a bien le droit de modifier cette journée en particulier (via ses filtres compétition/saison/journée).

### 13.3 Journal d'audit

Toutes les opérations sont loguées dans `kp_journal` :
- Ajout journée
- Modification journée
- Suppression journée
- Duplication journée
- Publication journée (toggle)
- Association/dissociation événement

---

## 14. Notes de migration

### 14.1 Différences avec le legacy

| Aspect | Legacy (PHP) | App4 (Nuxt) |
|--------|-------------|-------------|
| Formulaire | Page séparée (GestionParamJournee) | Modal/Panel dans la même page |
| Pagination | Aucune | Avec AdminPagination |
| Recherche | Aucune | Recherche textuelle |
| Responsive | Non | Oui (table → cartes) |
| Filtres | Session PHP | localStorage + workContext |
| Édition inline | jQuery directInput | Vue editable-cell |
| Date picker | Flatpickr | Composant Nuxt UI |
| Autocomplete | jQuery autocomplete maison | Composant combobox Nuxt UI |
| Notifications | alert() / confirm() | Toast Nuxt UI |
| Schéma compétition | Page séparée GestionSchema.php | ⏳ Lien legacy redirect (différé) |
| Officiels | Page séparée GestionInstances.php | ✅ Intégré dans la modal de journée |
| Mode association | Radio button global basculant la page | ✅ Panel/modal dédié modernisé |

### 14.2 Pages legacy liées (non migrées)

| Page | Fichier PHP | Description | Impact |
|------|-------------|-------------|--------|
| Matchs d'une journée | GestionJournee.php | Liste des matchs | Route `/games` (lien) |
| Schéma de compétition | GestionSchema.php | Organigramme des phases | ❓ À discuter |
| Officiels d'une journée | GestionInstances.php | Gestion détaillée des officiels | ❓ Intégré ou lien |
| Import ICS | upload_ics.php | Import calendrier iCalendar | Profil 1 uniquement |

### 14.3 Champs non exposés dans l'UI

Les champs suivants de `kp_journee` ne sont **pas** affichés dans l'interface :
- `Etat` : non utilisé dans la page calendrier
- `Code_organisateur` : calculé depuis Organisateur
- `Validation` : pas dans cette page
- `Code_uti` : automatique
- `Id_dupli` : interne
- `Public_prin`, `Public_sec` : gérés automatiquement

---

## 15. Décisions prises

| # | Question | Décision |
|---|----------|----------|
| Q1 | Intégration workContext | ✅ Utiliser `workContextStore` pour saison + périmètre compétitions. Filtres locaux pour événement, mois, tri. |
| Q2 | Formulaire modal vs page séparée | ✅ Modal large (max-width 2xl) avec sections collapsibles pour les officiels |
| Q3 | Fonctionnalités profil 1 (ICS, GCal, QR) | ⏳ Différé à une phase ultérieure |
| Q4 | Page Schéma de compétition | ⏳ Différé, garder lien legacy redirect |
| Q5 | Page Officiels (GestionInstances) | ✅ Intégrer dans le formulaire modal (section collapsible avec badges RC et autocomplete) |
| Q6 | Édition inline vs formulaire uniquement | ✅ Conserver les deux : inline pour modifications rapides + formulaire pour création/édition complète |
| Q7 | Mode Association événements | ✅ Moderniser : remplacer le mode radio global par un panel/modal dédié plus intuitif |

---

**Document créé le** : 13 février 2026
**Dernière mise à jour** : 13 février 2026
**Statut** : 📋 À IMPLÉMENTER
**Auteur** : Claude Code
