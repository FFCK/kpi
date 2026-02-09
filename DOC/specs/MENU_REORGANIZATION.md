# Spécification - Réorganisation du Menu App4

## 1. Vue d'ensemble

Réorganiser le menu de navigation de l'application App4 pour distinguer clairement :
1. **Pages de gestion de compétition** - Liées au contexte de travail (saison/périmètre)
2. **Pages d'administration** - Globales, non liées à une compétition spécifique

Cette séparation améliore la lisibilité et reflète la logique métier.

---

## 2. Structure du Menu

### 2.1 Section "Compétition" (liens directs)

Pages liées au **contexte de travail** défini sur la page d'accueil.

| Menu | Route | Profile min | Icône | Description |
|------|-------|-------------|-------|-------------|
| Compétitions | `/competitions` | ≤ 10 | `heroicons:trophy` | Liste des compétitions du périmètre |
| Documents | `/documents` | ≤ 9 | `heroicons:document-text` | Génération de documents PDF |
| Équipes | `/teams` | ≤ 9 | `heroicons:user-group` | Gestion des équipes |
| Journées/Phases | `/gamedays` | ≤ 9 | `heroicons:calendar` | Gestion des journées |
| Matchs | `/matches` | ≤ 9 | `heroicons:play-circle` | Gestion des matchs |
| Classements | `/rankings` | ≤ 9 | `heroicons:chart-bar` | Classements généraux |
| Statistiques | `/stats` | ≤ 9 | `heroicons:chart-pie` | Statistiques compétition |

**Note** : Le "Classement initial" n'est plus un élément de menu dédié. Il sera accessible via un bouton/lien sur la page `/rankings`.

### 2.2 Section "Administration" (dropdown)

Pages **globales** non liées à une compétition particulière.

| Menu | Route | Profile min | Icône | Description |
|------|-------|-------------|-------|-------------|
| Événements | `/events` | ≤ 2 | `heroicons:calendar-days` | Gestion des événements |
| Athlètes | `/athletes` | ≤ 8 | `heroicons:user` | Statistiques individuelles |
| Clubs | `/clubs` | ≤ 9 | `heroicons:building-office-2` | Gestion des clubs |
| Groupes | `/groups` | ≤ 2 | `heroicons:rectangle-group` | Gestion des groupes de compétitions |
| Utilisateurs | `/users` | ≤ 3 | `heroicons:users` | Gestion des utilisateurs |
| Opérations | `/operations` | = 1 | `heroicons:wrench-screwdriver` | Opérations système |

---

## 3. Rendu Visuel

### 3.1 Desktop (≥ lg breakpoint)

```
┌──────────────────────────────────────────────────────────────────────────────┐
│ [Logo KPI]  Compétitions Documents Équipes Journées Matchs Class. Stats │ Administration ▼ │
└──────────────────────────────────────────────────────────────────────────────┘
              └────────────────────── Section Compétition ────────────────────┘ └── Dropdown ──┘
```

- Les éléments de la section "Compétition" sont des **liens directs** (pas de dropdown)
- Un **séparateur vertical** sépare les deux sections
- La section "Administration" est un **dropdown unique** regroupant tous les éléments admin

### 3.2 Mobile (< lg breakpoint)

```
┌────────────────────────────┐
│ ≡ Menu                     │
├────────────────────────────┤
│ Compétition                │
│   ├─ Compétitions          │
│   ├─ Documents             │
│   ├─ Équipes               │
│   ├─ Journées/Phases       │
│   ├─ Matchs                │
│   ├─ Classements           │
│   └─ Statistiques          │
│────────────────────────────│
│ Administration ▼           │
│   ├─ Événements            │
│   ├─ Athlètes              │
│   ├─ Clubs                 │
│   ├─ Utilisateurs          │
│   └─ Opérations            │
└────────────────────────────┘
```

- Deux sections visuellement distinctes
- Chaque section est un accordion
- Séparateur horizontal entre les sections

---

## 4. Logique d'Affichage par Profil

### 4.1 Visibilité des Sections

| Profil | Section Compétition | Section Administration |
|--------|---------------------|------------------------|
| 1 (Super Admin) | Tous les éléments | Tous les éléments |
| 2-3 | Tous les éléments | Événements, Athlètes, Clubs, Users |
| 4-8 | Tous sauf Compétitions | Athlètes, Clubs |
| 9 | Tous sauf Compétitions | Clubs |
| 10 | Compétitions seulement | Aucun |
| > 10 | Aucun | Aucun |

### 4.2 Règles

- La section "Administration" n'apparaît que si au moins un élément est visible
- Chaque élément respecte le profil minimum défini

---

## 5. Changements par rapport à l'existant

### 5.1 Éléments déplacés

| Élément | Avant | Après |
|---------|-------|-------|
| Événements | Sous-menu de "Compétition" | Section "Administration" |
| Athlètes | Sous-menu de "Statistiques" | Section "Administration" |
| Clubs | Dropdown "Gestion" | Section "Administration" |
| Utilisateurs | Dropdown "Gestion" | Section "Administration" |
| Opérations | Dropdown "Gestion" | Section "Administration" |

### 5.2 Éléments supprimés du menu

| Élément | Ancien menu | Nouvel accès |
|---------|-------------|--------------|
| Classement initial | Sous-menu de "Classements" | Bouton sur page `/rankings` |

### 5.3 Dropdowns supprimés

- **"Compétition"** (ancien) → Devient un lien direct vers `/competitions`
- **"Statistiques"** (ancien) → Devient un lien direct vers `/stats`
- **"Classements"** (ancien) → Devient un lien direct vers `/rankings`
- **"Gestion"** → Remplacé par "Administration"

---

## 6. Traductions i18n

### 6.1 Clés existantes (inchangées)

```json
{
  "menu": {
    "competition": "Compétition" / "Competition",
    "documents": "Documents",
    "events": "Événements" / "Events",
    "teams": "Équipes" / "Teams",
    "gamedays": "Journées/Phases" / "Gamedays/Phases",
    "matches": "Matchs" / "Games",
    "rankings": "Classements" / "Rankings",
    "statistics": "Statistiques" / "Statistics",
    "athletes": "Athlètes" / "Athletes",
    "clubs": "Clubs",
    "users": "Utilisateurs" / "Users",
    "operations": "Opérations" / "Operations"
  }
}
```

### 6.2 Nouvelles clés

```json
{
  "menu": {
    "administration": "Administration"
  }
}
```

### 6.3 Clés à supprimer

```json
{
  "menu": {
    "management": "Gestion" / "Management",
    "initial_ranking": "Classement initial" / "Initial ranking"
  }
}
```

---

## 7. Impact sur les Pages

### 7.1 Page d'accueil (`/`)

Les **boutons d'accès rapide** doivent pointer vers les pages de **gestion de compétition** :

| Bouton actuel | Route | Action |
|---------------|-------|--------|
| Événements | `/events` | **Remplacer** par Compétitions (`/competitions`) |
| Documents | `/documents` | Conserver |
| Statistiques | `/stats` | Conserver |
| Opérations | `/operations` | **Remplacer** par Équipes (`/teams`) ou Matchs (`/matches`) |

**Nouvelle proposition de boutons d'accès rapide** :
- Compétitions → `/competitions`
- Documents → `/documents`
- Matchs → `/matches`
- Statistiques → `/stats`

**Note** : Les pages d'administration (Événements, Opérations) restent accessibles via le menu "Administration", pas depuis les raccourcis de la page d'accueil.

### 7.2 Page `/rankings`

Ajouter un accès au classement initial :
- Bouton ou lien vers `/rankings/initial`
- Texte : "Classement initial" / "Initial ranking"
- Visible uniquement si applicable

### 7.3 Autres pages

Aucun changement de fonctionnalité. Seule la navigation change.

---

## 8. Fichiers à Modifier

| Fichier | Modifications |
|---------|---------------|
| `components/admin/Header.vue` | Refactoriser la structure du menu |
| `pages/index.vue` | Modifier les boutons d'accès rapide (Compétitions, Documents, Matchs, Stats) |
| `i18n/locales/fr.json` | Ajouter `menu.administration`, supprimer `menu.management` et `menu.initial_ranking` |
| `i18n/locales/en.json` | Idem |
| `pages/rankings/index.vue` | Ajouter lien vers classement initial |

---

## 9. Checklist d'Implémentation

- [ ] Refactoriser `Header.vue` avec deux computed (`competitionMenuItems`, `adminMenuItems`)
- [ ] Implémenter le rendu desktop avec séparateur vertical
- [ ] Implémenter le dropdown "Administration" desktop
- [ ] Adapter le rendu mobile avec deux sections
- [ ] Mettre à jour les traductions FR
- [ ] Mettre à jour les traductions EN
- [ ] **Modifier les boutons d'accès rapide sur `pages/index.vue`** (Compétitions, Documents, Matchs, Stats)
- [ ] Ajouter le lien "Classement initial" sur `/rankings`
- [ ] Tester avec différents profils (1, 3, 9, 10)
- [ ] Tester sur desktop et mobile
