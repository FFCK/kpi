# Spécifications Communes - Admin App4

Ce document décrit les spécifications communes à toutes les pages de l'administration App4 (admin2).

## 1. Authentification et Autorisations

### 1.1 Authentification JWT

Toutes les pages admin requièrent une authentification JWT via l'endpoint `/api2/auth/login`.

Le token JWT contient :
- `id` : Code utilisateur (ex: "1234567")
- `name` : Nom
- `firstname` : Prénom
- `profile` : Niveau de profil (1-10)
- `roles` : Rôles Symfony (ROLE_SUPER_ADMIN, ROLE_ADMIN, etc.)

### 1.2 Niveaux de Profil

| Niveau | Description Legacy | Rôle Symfony | Accès |
|--------|-------------------|--------------|-------|
| 1 | Super Admin | ROLE_SUPER_ADMIN | Accès total, suppression, configuration |
| 2 | Bureau CNAKP | ROLE_ADMIN | Administration fédérale |
| 3 | Resp. Division | ROLE_DIVISION | Multi-compétitions d'une division |
| 4 | Resp. Poule/Compétition | ROLE_COMPETITION | Une ou plusieurs compétitions |
| 5 | Délégué fédéral | ROLE_DELEGATE | Supervision journées |
| 6 | Organisateur journée | ROLE_ORGANIZER | Gestion d'une journée |
| 7 | Resp. club/équipe | ROLE_TEAM | Gestion de son équipe |
| 8 | Consultation simple | ROLE_VIEWER | Lecture seule |
| 9 | Table de marque | ROLE_SCORER | Saisie matchs uniquement |
| 10 | (Inutilisé) | ROLE_USER | Accès minimal |

#### Hiérarchie des rôles Symfony

```yaml
# security.yaml
role_hierarchy:
    ROLE_SUPER_ADMIN: [ROLE_ADMIN]
    ROLE_ADMIN: [ROLE_DIVISION, ROLE_DELEGATE]
    ROLE_DIVISION: [ROLE_COMPETITION]
    ROLE_COMPETITION: [ROLE_ORGANIZER]
    ROLE_ORGANIZER: [ROLE_TEAM]
    ROLE_TEAM: [ROLE_VIEWER]
    ROLE_VIEWER: [ROLE_SCORER, ROLE_USER]
    ROLE_SCORER: [ROLE_USER]
```

#### Restrictions par profil dans l'UI

| Fonctionnalité | Profil min | Rôle requis |
|----------------|------------|-------------|
| Suppression d'éléments | 1 | ROLE_SUPER_ADMIN |
| Documents Événement | ≤ 2 | ROLE_ADMIN |
| Documents Contrôle | ≤ 2 | ROLE_ADMIN |
| Stats Irrégularités | ≤ 6 | ROLE_ORGANIZER |
| Stats Licenciés nationaux | ≤ 6 | ROLE_ORGANIZER |
| Stats Cohérence matchs | ≤ 6 | ROLE_ORGANIZER |
| Gestion des événements | ≤ 2 | ROLE_ADMIN |
| Consultation stats | ≤ 8 | ROLE_VIEWER |

### 1.3 Filtres Utilisateur (Restrictions d'accès)

La table `kp_user` contient des champs de restriction :

| Champ | Type | Format | Exemple |
|-------|------|--------|---------|
| `Filtre_saison` | mediumtext | Pipe-delimited avec `\|` au début et fin | `"\|2023\|2022\|2021\|"` |
| `Filtre_competition` | mediumtext | Pipe-delimited avec `\|` au début et fin | `"\|CF15\|N1H\|N2H\|"` |
| `Filtre_journee` | mediumtext | Comma-separated (IDs) | `"5775,5777,5779"` |
| `Limitation_equipe_club` | varchar(50) | Comma-separated (codes) | `"4404,CR04"` |
| `Id_Evenement` | varchar(20) | Pipe-delimited avec `\|` au début et fin | `"\|209\|199\|144\|"` |

**Note** : `Filtre_competition_sql` existe mais n'est pas utilisé dans app4.

**Règles :**
- Si `Filtre_saison` est vide → accès à toutes les saisons
- Si `Filtre_competition` est vide → accès à toutes les compétitions
- Si `Id_Evenement` est vide → accès à tous les événements
- Les filtres doivent être appliqués **côté API2** (sécurité) ET **côté app4** (UX)

### 1.4 Implémentation API2

L'entité `User` doit exposer les filtres :

```php
// User.php - Propriétés à ajouter
private ?string $filtreSaison = null;
private ?string $filtreJournee = null;
private ?string $idEvenement = null;

// Méthodes pour parser les filtres (format pipe-delimited)
public function getAllowedSeasons(): ?array
{
    if (empty($this->filtreSaison)) {
        return null; // Pas de restriction
    }
    // Format: "|2023|2022|2021|" → ["2023", "2022", "2021"]
    return array_filter(explode('|', trim($this->filtreSaison, '|')));
}

public function getAllowedCompetitions(): ?array
{
    if (empty($this->filtreCompetition)) {
        return null; // Pas de restriction
    }
    // Format: "|CF15|N1H|N2H|" → ["CF15", "N1H", "N2H"]
    return array_filter(explode('|', trim($this->filtreCompetition, '|')));
}

public function getAllowedEvents(): ?array
{
    if (empty($this->idEvenement)) {
        return null; // Pas de restriction
    }
    // Format: "|209|199|144|" → [209, 199, 144]
    return array_map('intval', array_filter(explode('|', trim($this->idEvenement, '|'))));
}

// Méthodes pour parser les filtres (format comma-separated)
public function getAllowedJournees(): ?array
{
    if (empty($this->filtreJournee)) {
        return null; // Pas de restriction
    }
    // Format: "5775,5777,5779" → [5775, 5777, 5779]
    return array_map('intval', array_filter(explode(',', $this->filtreJournee)));
}

public function getAllowedClubs(): ?array
{
    if (empty($this->limitClubs)) {
        return null; // Pas de restriction
    }
    // Format: "4404,CR04" → ["4404", "CR04"]
    return array_filter(explode(',', $this->limitClubs));
}
```

### 1.5 Token JWT Étendu

Le token JWT doit inclure les filtres pour que app4 puisse filtrer l'UI :

```json
{
  "id": "1234567",
  "name": "Dupont",
  "firstname": "Jean",
  "profile": 3,
  "roles": ["ROLE_MANAGER", "ROLE_STAFF", "ROLE_USER"],
  "filters": {
    "seasons": ["2023", "2022", "2021"],
    "competitions": ["CF15", "N1H", "N2H"],
    "events": [209, 199, 144],
    "journees": [5775, 5777, 5779],
    "clubs": ["4404", "CR04"]
  }
}
```

Si un filtre est `null` → pas de restriction sur cette dimension.

**Note** : `journees` et `events` seront utiles pour des fonctionnalités futures.

---

## 2. Internationalisation (i18n)

### 2.1 Langues Supportées

- `fr` : Français (défaut)
- `en` : Anglais

### 2.2 Langue Interface Utilisateur

Stockée dans le localStorage de app4 et utilisée pour :
- Textes de l'interface
- Format des dates
- Messages d'erreur

### 2.3 Langue des Documents

Pour les documents PDF générés, la langue est déterminée par :

1. **Priorité 1** : Paramètre `En_actif` de la compétition
   - `En_actif = 'O'` → Document en anglais
   - `En_actif = 'N'` ou absent → Document en français

2. **Priorité 2** : Langue de l'interface utilisateur (si pas de compétition sélectionnée)

### 2.4 Implémentation

```typescript
// composables/useDocumentLanguage.ts
export function useDocumentLanguage() {
  const { locale } = useI18n()

  function getDocumentLang(competition?: Competition): 'fr' | 'en' {
    if (competition?.enActif === 'O') {
      return 'en'
    }
    return locale.value === 'en' ? 'en' : 'fr'
  }

  return { getDocumentLang }
}
```

---

## 3. Composants Communs

### 3.1 Sélecteur de Saison

```vue
<SeasonSelect
  v-model="selectedSeason"
  :allowed-seasons="userFilters.seasons"
/>
```

- Affiche uniquement les saisons autorisées pour l'utilisateur
- Triées par ordre décroissant (plus récente en premier)
- Saison active pré-sélectionnée par défaut

### 3.2 Sélecteur de Compétition

```vue
<CompetitionSelect
  v-model="selectedCompetition"
  :season="selectedSeason"
  :allowed-competitions="userFilters.competitions"
  :grouped="true"
/>
```

- Groupées par section (Nationaux, Coupes, etc.)
- Filtrées selon saison ET restrictions utilisateur
- Option `grouped` pour affichage par optgroup

### 3.3 Sélecteur d'Événement

```vue
<EventSelect
  v-model="selectedEvent"
  :season="selectedSeason"
/>
```

- Liste des événements de la saison
- Triés par date décroissante

### 3.4 ~~Filtre Multi-Compétition (Dropdown inline)~~ — OBSOLÈTE

> **Remplacé par** le pattern EventGroupSelect + CompetitionSingleSelect (§ 3.5).
>
> Les pages Journées, Matchs et RC utilisent désormais `CompetitionSingleSelect` avec `showAllOption=true` au lieu du dropdown multi-sélection `AdminCompetitionMultiSelect`. Le composant `AdminCompetitionMultiSelect` reste disponible mais n'est plus utilisé dans les pages admin principales.

### 3.5 Filtre Événement / Groupe (EventGroupSelect + CompetitionSingleSelect)

Le composant `AdminEventGroupSelect` permet de filtrer par événement ou groupe de compétitions. Il est présent sur toutes les pages admin qui utilisent `AdminCompetitionSingleSelect`.

#### 3.5.1 Pages et comportement

| Page | EventGroupSelect | CompetitionSingleSelect | Option "Toutes" |
|------|:-:|:-:|:-:|
| Journées/Phases | ✅ | ✅ | ✅ Oui |
| Matchs | ✅ | ✅ | ✅ Oui |
| Resp. Compétition | ✅ | ✅ | ✅ Oui |
| Documents | ✅ | ✅ | ❌ Non |
| Équipes | ✅ | ✅ | ❌ Non |
| Classements | ✅ | ✅ | ❌ Non |
| Schéma | ✅ | ✅ | ❌ Non |

- **Pages avec "Toutes les compétitions"** (Journées, Matchs, Resp. Compétition) : quand un événement ou groupe est sélectionné, l'option "Toutes les compétitions" apparaît en premier dans le sélecteur de compétition et est pré-sélectionnée.
- **Pages sans "Toutes les compétitions"** (Documents, Équipes, Classements, Schéma) : quand un événement ou groupe est sélectionné, la liste des compétitions est filtrée et la première compétition est auto-sélectionnée.

#### 3.5.2 Composant EventGroupSelect

Le composant `AdminEventGroupSelect` affiche un `<select>` avec :
- **Option "Tous"** (`value=""`) : aucun filtre événement/groupe
- **Optgroup "Événements"** : liste des événements du contexte (`workContext.events`)
- **Optgroups par section** : groupes de compétitions (`workContext.uniqueGroups`), filtrés pour ne montrer que ceux dont au moins une compétition est dans le contexte courant

**Format de la valeur** : `'event:{id}'`, `'group:{code}'` ou `''` (aucun filtre).

**Stockage** : la sélection est persistée dans `localStorage` via la clé `kpi_admin_work_page_event_group` et restaurée au rechargement (avec validation que l'événement/groupe existe encore).

**Comportement au changement** :
1. Met à jour `workContext.pageEventGroupSelection`
2. Remet `pageCompetitionCodeAll` à `''` (= Toutes)
3. Si événement sélectionné → charge les codes compétitions associés via l'API (`/admin/filters/event-competitions`)
4. Si groupe ou "Tous" → vide `pageEventCompetitionCodes`

**Validation au changement de contexte** : un watcher surveille `competitionCodes` et `events` ; si la sélection courante n'est plus valide (événement supprimé, groupe sans compétitions dans le contexte), elle est remise à `''`.

#### 3.5.3 Composant CompetitionSingleSelect — Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `showAllOption` | `boolean` | `false` | Affiche l'option "Toutes les compétitions" en premier |
| `allOptionLabel` | `string` | `''` | Label personnalisé pour l'option "Toutes" (défaut : clé i18n `context.all_competitions_for_selection`) |
| `filteredCodes` | `string[] \| null` | `null` | Si fourni, restreint les compétitions affichées à l'intersection de ces codes avec le contexte |

**Logique de sélection** :
- `showAllOption=true` → lit/écrit `workContext.pageCompetitionCodeAll` (peut être `''` = toutes)
- `showAllOption=false` → lit/écrit `workContext.pageCompetitionCode` (toujours un code réel)

**Auto-sélection** : quand les compétitions disponibles changent, le watcher :
1. Si `showAllOption=true` et la valeur courante est `''` → conserve "Toutes"
2. Si la sélection courante est encore dans la liste → conserve
3. Sinon → auto-sélectionne la première compétition disponible

#### 3.5.4 Label dynamique du sélecteur de compétition

Le label au-dessus du sélecteur de compétition s'adapte au contexte via `workContext.competitionFilterLabelKey` :

| Contexte | Clé i18n | Label FR | Label EN |
|----------|----------|----------|----------|
| Aucun filtre | `context.competition_from_context` | Compétition (du contexte) | Competition (from context) |
| Événement sélectionné | `context.competition_from_event` | Compétition (de l'événement) | Competition (from event) |
| Groupe sélectionné | `context.competition_from_group` | Compétition (du groupe) | Competition (from group) |

#### 3.5.5 Stockage séparé des sélections de compétition

**Problème** : les pages avec et sans "Toutes les compétitions" partagent le même contexte de travail. Si un utilisateur choisit "Toutes" sur Journées puis navigue vers Équipes, la page Équipes ne peut pas afficher "Toutes" car elle requiert une compétition spécifique. Inversement, si Équipes auto-sélectionne la première compétition, le retour sur Journées ne devrait pas perdre le choix "Toutes".

**Solution** : deux clés de stockage distinctes dans le store `workContextStore` :

| Clé | localStorage | Utilisation | Valeur possible |
|-----|-------------|-------------|-----------------|
| `pageCompetitionCode` | `kpi_admin_work_page_competition` | Pages **sans** "Toutes" (Documents, Équipes, Classements, Schéma) | Code compétition (jamais vide) |
| `pageCompetitionCodeAll` | `kpi_admin_work_page_competition_all` | Pages **avec** "Toutes" (Journées, Matchs, Resp. Compétition) | Code compétition ou `''` (= toutes) |
| `pageEventGroupSelection` | `kpi_admin_work_page_event_group` | Toutes les pages avec EventGroupSelect | `'event:{id}'`, `'group:{code}'` ou `''` |

**Règles** :
- `CompetitionSingleSelect` avec `showAllOption=false` lit/écrit `pageCompetitionCode`
- `CompetitionSingleSelect` avec `showAllOption=true` lit/écrit `pageCompetitionCodeAll`
- Changer la compétition sur une page "avec Toutes" ne change pas la sélection sur les pages "sans Toutes" (et vice versa)
- Changer l'événement/groupe dans `EventGroupSelect` remet `pageCompetitionCodeAll` à `''` (= Toutes), ce qui déclenche l'auto-sélection de la première compétition sur les pages "sans Toutes" via leur watcher
- Le `pageEventGroupSelection` est partagé entre toutes les pages (la sélection événement/groupe est commune)

#### 3.5.6 Pattern d'intégration

**Layout commun** : les filtres EventGroupSelect et CompetitionSingleSelect sont placés dans une ligne de filtres avec labels au-dessus :

```vue
<div class="flex flex-wrap gap-3 items-end">
  <!-- Event / Group filter -->
  <div class="min-w-48 max-w-96">
    <label class="block text-xs font-medium text-gray-500 mb-1">{{ t('eventGroupSelect.label') }}</label>
    <AdminEventGroupSelect @change="..." />
  </div>
  <!-- Competition filter -->
  <div class="min-w-48 max-w-96">
    <label class="block text-xs font-medium text-gray-500 mb-1">{{ t(workContext.competitionFilterLabelKey) }}</label>
    <AdminCompetitionSingleSelect ... />
  </div>
</div>
```

**Pages avec "Toutes les compétitions" (Journées, Matchs, RC) :**
```vue
<AdminEventGroupSelect @change="() => { page = 1 }" />
<AdminCompetitionSingleSelect
  :show-all-option="!!workContext.pageEventGroupSelection"
  :filtered-codes="workContext.pageFilteredCompetitionCodes"
  @change="() => { page = 1 }"
/>
```
Ces pages lisent `workContext.pageCompetitionCodeAll` dans leur logique de chargement.

**Pages sans "Toutes les compétitions" (Documents, Équipes, Classements, Schéma) :**
```vue
<AdminEventGroupSelect />
<AdminCompetitionSingleSelect
  :filtered-codes="workContext.pageFilteredCompetitionCodes"
  @change="onCompetitionChange"
/>
```
Ces pages lisent `workContext.pageCompetitionCode` dans leur logique de chargement.

#### 3.5.7 Logique de chargement des pages "avec Toutes"

Les pages Journées, Matchs et RC utilisent une logique de chargement commune pour résoudre le filtre compétition à envoyer à l'API :

```typescript
// Competition filter
if (workContext.pageCompetitionCodeAll) {
  // A specific competition is selected
  params.competitions = workContext.pageCompetitionCodeAll
} else if (workContext.pageEventGroupType === 'group') {
  // "All competitions" with a group: resolve group to competition codes
  const group = workContext.uniqueGroups.find(g => g.code === workContext.pageEventGroupValue)
  if (group) {
    const contextCodes = new Set(workContext.competitionCodes)
    const groupCodes = group.competitions.filter(c => contextCodes.has(c))
    if (groupCodes.length > 0) params.competitions = groupCodes.join(',')
  }
} else if (workContext.pageEventGroupType === 'event') {
  // "All competitions" with an event: pass event ID
  params.event = workContext.pageEventGroupValue
} else if (workContext.hasValidContext && workContext.competitionCodes.length > 0) {
  // No filter: use all context competition codes
  params.competitions = workContext.competitionCodes.join(',')
}
```

**Watchers** : ces pages surveillent `workContext.pageCompetitionCodeAll` et `workContext.pageEventGroupSelection` pour recharger les données.

#### 3.5.8 Filtrage des compétitions par événement/groupe

Quand un événement ou groupe est sélectionné dans `EventGroupSelect`, la prop `filteredCodes` de `CompetitionSingleSelect` est renseignée via `workContext.pageFilteredCompetitionCodes` :
- **Événement** : les codes sont chargés depuis l'API (`/admin/filters/event-competitions`)
- **Groupe** : les codes sont résolus côté client via `workContext.uniqueGroups`
- **Aucun filtre** : `null` → toutes les compétitions du contexte sont affichées

#### 3.5.9 Lien Schéma depuis la page Journées

Le lien vers la page Schéma n'est plus un lien global dans le header de la page Journées. Il est désormais un bouton d'action par ligne de journée, qui :
1. Définit `workContext.pageCompetitionCode` avec le code compétition de la journée
2. Navigue vers `/gamedays/schema`

Cela garantit que le schéma s'ouvre directement sur la bonne compétition.

### 3.6 Toolbar (AdminToolbar)

Barre d'outils commune avec recherche, bouton d'ajout et actions en masse.

```vue
<AdminToolbar
  v-model:search="searchQuery"
  :search-placeholder="t('page.search')"
  :add-label="t('page.add')"
  :show-add="canEdit"
  :show-bulk-delete="canDelete"
  :selected-count="selectedIds.length"
  @add="openAddModal"
  @bulk-delete="confirmDelete"
>
  <template #before-search>
    <!-- Contenu à gauche du champ de recherche (ex: filtre compétitions) -->
  </template>
  <template #after-search>
    <!-- Contenu après le champ de recherche (ex: boutons d'actions) -->
  </template>
  <template #left>
    <!-- Contenu côté gauche (zone des actions en masse) -->
  </template>
  <template #right>
    <!-- Contenu après le bouton d'ajout -->
  </template>
</AdminToolbar>
```

**Structure :**
- **Gauche** : bouton suppression en masse (si sélection) + slot `#left`
- **Droite** : slot `#before-search` + champ de recherche + slot `#after-search` + bouton ajouter + slot `#right`

### 3.7 Cases à cocher dans les tableaux

Les cases à cocher (`<input type="checkbox">`) dans les cellules `<th>` et `<td>` sont cliquables sur toute la surface de la cellule, pas uniquement sur la case elle-même.

**Implémentation :**
- **Plugin global** : `plugins/checkbox-cell-click.client.ts` — event delegation sur `document` en **phase de capture** (`{ capture: true }`), détecte les clics sur `th`/`td` contenant une unique checkbox et la bascule
- **CSS** : `cursor: pointer` sur les cellules via `:has(> input[type="checkbox"]:only-child)`

**Phase de capture :** le listener utilise la capture phase pour intercepter le clic **avant** que `@click.stop` (présent sur certains `<td>`) ne bloque la propagation. Sans cela, `stopPropagation` empêcherait l'événement de remonter au `document`.

**Conditions d'activation :**
- La cellule contient exactement une seule checkbox
- Le clic ne cible pas déjà la checkbox elle-même
- La checkbox n'est pas désactivée (`disabled`)

**Aucune modification des pages individuelles n'est nécessaire** — le comportement est automatique pour toutes les pages admin.

### 3.8 Header de page (AdminPageHeader)

Le composant `AdminPageHeader` uniformise la structure du header sur toutes les pages de gestion de compétition. Il remplace le pattern précédent où chaque page assemblait manuellement `AdminWorkContextSummary`, le titre, les filtres et les notices.

#### 3.8.1 Layout

```
┌──────────────────────────────────────────────────────────────────┐
│ [← Retour] Titre (h1)              [Saison: 2025] [Périmètre] [✏]│
├──────────────────────────────────────────────────────────────────┤
│ Événement/Groupe    Compétition     [filtres extra]  [badges]    │
│ [▼ select]          [▼ select]      [▼ mois] [▼ tri] NIV TYPE   │
├──────────────────────────────────────────────────────────────────┤
│ ⚠ Notice (masquable via ×)                                       │
└──────────────────────────────────────────────────────────────────┘
```

**Ligne 1 — Titre + Contexte de travail :**
- Titre de la page (h1) à gauche, avec optionnellement un bouton retour (page Schéma)
- `AdminWorkContextSummary` en mode compact à droite (badges inline : saison + périmètre + lien modifier)

**Ligne 2 — Filtres :**
- `AdminEventGroupSelect` + `AdminCompetitionSingleSelect` intégrés avec labels au-dessus (`min-w-48 max-w-96`)
- Slot `#filters` pour les filtres supplémentaires spécifiques à chaque page
- Slot `#badges` pour les badges de compétition (niveau, type, statut, etc.)
- Utilise `flex-wrap` pour le responsive

**Ligne 3 — Notices (optionnelle) :**
- Slot `#notices` pour les messages d'alerte (verrou compétition, restriction statut, etc.)
- Bouton × pour masquer la notice (état local, réinitialisé au changement de compétition)

**Note :** La toolbar (`AdminToolbar`) reste un composant séparé, utilisé juste après `AdminPageHeader`.

#### 3.8.2 Props

| Prop | Type | Défaut | Description |
|------|------|--------|-------------|
| `title` | `string` | requis | Titre de la page |
| `showFilters` | `boolean` | `true` | Afficher la ligne de filtres (`false` pour la page Compétitions) |
| `showAllOption` | `boolean` | `false` | Active l'option "Toutes les compétitions" dans `CompetitionSingleSelect` |
| `competitionFilteredCodes` | `string[] \| null` | `null` | Codes filtrés passés à `CompetitionSingleSelect` |
| `backTo` | `string` | `''` | Route pour le bouton retour (ex: `/gamedays` pour la page Schéma) |
| `backLabel` | `string` | `''` | Label du bouton retour |

#### 3.8.3 Événements

| Événement | Description |
|-----------|-------------|
| `event-group-change` | Émis quand l'événement/groupe change dans `EventGroupSelect` |
| `competition-change` | Émis quand la compétition change dans `CompetitionSingleSelect` |

#### 3.8.4 Slots

| Slot | Description | Exemple d'utilisation |
|------|-------------|----------------------|
| `#filters` | Filtres supplémentaires après EventGroup + Competition | Mois, tri (Journées), tour, journée, date, terrain (Matchs), type (Classements) |
| `#badges` | Badges de compétition sur la ligne des filtres | Niveau (INT/NAT/REG), type (CHPT/CP), statut, verrou, goal-average |
| `#notices` | Messages d'alerte sous les filtres, masquables | Notice de verrouillage (Équipes), restriction de statut (Classements) |

#### 3.8.5 Pages et utilisation

| Page | `showFilters` | `showAllOption` | Slot `#filters` | Slot `#badges` | Slot `#notices` |
|------|:-:|:-:|:-:|:-:|:-:|
| Compétitions | `false` | — | — | — | — |
| Resp. Compétition | `true` | `true` | — | — | — |
| Documents | `true` | `false` | — | EN + type | — |
| Schéma | `true` | `false` | — | niveau + type | — |
| Équipes | `true` | `false` | — | niveau + type + statut + verrou | verrou compétition |
| Classements | `true` | `false` | type selector | niveau + type + statut + goal-avg | restriction statut |
| Journées/Phases | `true` | `true` | mois, tri | — | — |
| Matchs | `true` | `true` | tour, journée, date, terrain, tri, checkbox, spinner | — | — |

#### 3.8.6 Pattern d'intégration

```vue
<!-- Exemple simple (RC) -->
<AdminPageHeader
  :title="t('rc.title')"
  :show-all-option="true"
  :competition-filtered-codes="workContext.pageFilteredCompetitionCodes"
/>

<!-- Exemple avec filtres, badges et notices (Équipes) -->
<AdminPageHeader
  :title="t('teams_page.title')"
  :competition-filtered-codes="workContext.pageFilteredCompetitionCodes"
  @competition-change="onCompetitionChange"
>
  <template #badges>
    <div v-if="competitionInfo" class="flex items-center gap-2 flex-wrap">
      <span class="px-2 py-1 text-xs font-medium rounded" :class="getLevelColor(...)">...</span>
    </div>
  </template>
  <template #notices>
    <div v-if="competitionInfo?.verrou" class="flex items-center gap-2 p-2 bg-amber-50 ...">
      ⚠ Compétition verrouillée
    </div>
  </template>
</AdminPageHeader>
```

#### 3.8.7 WorkContextSummary — Mode compact

Le composant `AdminWorkContextSummary` supporte un prop `compact` (défaut `false`) :
- **Mode normal** (`compact=false`) : barre pleine largeur avec fond bleu, utilisé si le composant est utilisé seul
- **Mode compact** (`compact=true`) : badges inline sans wrapper, utilisé dans `AdminPageHeader` sur la ligne du titre

---

## 4. Endpoints API2 Communs

### 4.1 Filtres (saisons, compétitions, événements)

```
GET /api2/admin/filters
```

Réponse :
```json
{
  "seasons": [
    { "code": "2025", "active": true },
    { "code": "2024", "active": false }
  ],
  "activeSeason": "2025"
}
```

Les saisons retournées respectent `Filtre_saison` de l'utilisateur.

### 4.2 Compétitions par saison

```
GET /api2/admin/filters/competitions?season=2025
```

Réponse :
```json
{
  "competitions": [
    {
      "code": "N1M",
      "libelle": "Nationale 1 Masculine",
      "section": 1,
      "sectionLabel": "Nationaux",
      "enActif": "N"
    }
  ]
}
```

Les compétitions retournées respectent `Filtre_competition` de l'utilisateur.

### 4.3 Événements par saison

```
GET /api2/admin/filters/events?season=2025
```

Réponse :
```json
{
  "events": [
    {
      "id": 123,
      "libelle": "Championnat de France",
      "dateDebut": "2025-05-15",
      "dateFin": "2025-05-18"
    }
  ]
}
```

---

## 5. Gestion des Erreurs

### 5.1 Codes HTTP

| Code | Signification |
|------|---------------|
| 200 | Succès |
| 201 | Création réussie |
| 400 | Requête invalide |
| 401 | Non authentifié |
| 403 | Accès interdit (profil insuffisant ou filtre) |
| 404 | Ressource non trouvée |
| 500 | Erreur serveur |

### 5.2 Format Erreur API

```json
{
  "error": true,
  "message": "Accès non autorisé à cette compétition",
  "code": "ACCESS_DENIED"
}
```

### 5.3 Gestion côté App4

Le composable `useApi` gère automatiquement :
- Rafraîchissement du token expiré
- Redirection vers login si 401
- Affichage toast pour erreurs

---

## 6. Notes pour Développement Futur

### 6.1 Page Résumé Compétition (différée)

Une page dédiée sera créée ultérieurement pour afficher :
- Détails compétition (logo, bannière, sponsor)
- Compteurs (équipes, journées, matchs)
- Statuts de publication/validation
- Liens rapides vers autres pages admin

Route prévue : `/competition/:code/summary`

### 6.2 Cohérence des Filtres

Tous les endpoints admin DOIVENT :
1. Vérifier l'authentification JWT
2. Appliquer les filtres utilisateur (`Filtre_saison`, `Filtre_competition`)
3. Retourner 403 si accès à une ressource non autorisée
