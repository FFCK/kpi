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

### 3.4 Filtre Multi-Compétition (Dropdown inline)

Le composant `AdminCompetitionMultiSelect` est intégré dans un **dropdown inline** positionné en absolu. Ce pattern est utilisé dans les pages Journées et RC pour filtrer par compétitions sans occuper de place verticale.

**Pattern d'intégration :**

```vue
<div ref="competitionFilterRef" class="relative">
  <button
    class="flex items-center gap-2 px-3 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 bg-white"
    @click="filterOpen = !filterOpen"
  >
    <UIcon name="heroicons:funnel" class="w-4 h-4 text-gray-500" />
    <span class="text-gray-700">{{ t('rc.filter_competitions') }}</span>
    <span v-if="selectedCompetitions.length > 0"
      class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
      {{ selectedCompetitions.length }}
    </span>
    <UIcon name="heroicons:chevron-down" class="w-4 h-4 text-gray-400 transition-transform"
      :class="{ 'rotate-180': filterOpen }" />
  </button>
  <div v-show="filterOpen" class="absolute z-20 mt-1 w-80 bg-white border border-gray-200 rounded-lg shadow-lg p-3">
    <AdminCompetitionMultiSelect
      v-model="selectedCompetitions"
      :competitions="workContext.competitions || []"
    />
  </div>
</div>
```

**Comportement :**
- Bouton compact aligné avec les autres filtres (Event, Month, Sort) ou dans la toolbar
- Badge bleu affichant le nombre de compétitions sélectionnées
- Dropdown flottant (`position: absolute`, `z-20`) au clic
- Fermeture au clic extérieur via `document.addEventListener('click', ...)` avec ref sur le conteneur
- Le dropdown contient le `AdminCompetitionMultiSelect` avec checkbox "Toutes" + liste scrollable

**Placement selon la page :**
- **Journées** : dans la ligne de filtres, au même niveau que Event, Mois, Tri
- **RC** : dans le slot `#before-search` du `AdminToolbar`, à gauche du champ de recherche

### 3.5 Toolbar (AdminToolbar)

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
