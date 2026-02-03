# Spécification - Page d'Accueil avec Contexte de Travail

## 1. Vue d'ensemble

La page d'accueil permet de sélectionner un contexte de travail (saison + périmètre de compétitions) qui sera utilisé comme filtre par défaut dans toutes les pages de gestion. Ce contexte est persisté en localStorage.

**Route** : `/`

**Accès** : Authentifié (tous profils, filtré selon droits utilisateur)

---

## 2. Modèle de Sélection Multi-Niveaux

### 2.1 Hiérarchie des Sélections

L'utilisateur peut sélectionner un périmètre de travail à différents niveaux de granularité :

```
Saison
  └── Section (ex: "Compétitions Nationales")
        └── Groupe (ex: "N1H" → N1H + NPOH)
              └── Compétition individuelle (ex: "N1H")
  └── Événement (ex: "Championnat de France" → toutes compétitions liées)
```

### 2.2 Types de Sélection

| Type | Description | Exemple | Compétitions résultantes |
|------|-------------|---------|--------------------------|
| `section` | Toute une section | Section 2 (Nationales) | N1H, NPOH, N1F, NPOF, N2H, N2F, etc. |
| `group` | Tout un groupe | Groupe "N1H" | N1H, NPOH |
| `competition` | Une seule compétition | "N1H" | N1H |
| `event` | Un événement | Événement 123 | Toutes les compétitions dont les journées sont liées à cet événement |

### 2.3 Structure de Données du Contexte

```typescript
interface WorkContext {
  season: string              // Code saison (ex: "2026")

  // Type de sélection
  selectionType: 'section' | 'group' | 'competition' | 'event'

  // Valeur selon le type
  sectionId?: number          // ID de section (1, 2, 3...)
  groupCode?: string          // Code groupe (ex: "N1H")
  competitionCode?: string    // Code compétition unique
  eventId?: number            // ID événement

  // Compétitions effectives (calculées)
  competitionCodes: string[]  // Liste des codes compétitions résultants
}
```

---

## 3. Utilisation selon les Pages

### 3.1 Principe Général

La page d'accueil définit un **périmètre de compétitions disponibles**. Les autres pages permettent ensuite de sélectionner parmi ce périmètre :
- Soit toutes les compétitions du périmètre
- Soit une sélection partielle (multi-select)
- Soit une seule compétition (pour les pages qui l'exigent)

### 3.2 Page Compétitions

La page Compétitions affiche **directement** toutes les compétitions du périmètre défini sur la page d'accueil. Pas besoin de filtre supplémentaire puisque cette page liste déjà les compétitions.

| Page | Comportement |
|------|--------------|
| **Compétitions** | Liste automatiquement toutes les compétitions du périmètre |

### 3.3 Pages avec Sélection Multiple

Ces pages permettent de filtrer sur une ou plusieurs compétitions du périmètre :

| Page | Composant | Comportement |
|------|-----------|--------------|
| **Journées/Phases** | Multi-select + "Toutes" | Liste les journées des compétitions sélectionnées |
| **Matchs** | Multi-select + "Toutes" | Liste les matchs des compétitions sélectionnées |
| **Statistiques** | Multi-select + "Toutes" | Agrège les stats sur les compétitions sélectionnées |

**UI du sélecteur :**
```
┌─────────────────────────────────────────────────┐
│ Compétitions (du contexte)                      │
│ ┌─────────────────────────────────────────────┐ │
│ │ ☑ Toutes (2 compétitions)                   │ │
│ │ ─────────────────────────────               │ │
│ │ ☑ N1H - Nationale 1 Hommes                  │ │
│ │ ☑ NPOH - Play-offs N1 Hommes                │ │
│ └─────────────────────────────────────────────┘ │
└─────────────────────────────────────────────────┘
```

### 3.4 Pages Mono-Compétition

Ces pages ne peuvent travailler qu'avec une seule compétition à la fois :

| Page | Comportement |
|------|--------------|
| **Documents** | Dropdown simple pour choisir UNE compétition |
| **Équipes** | Dropdown simple pour choisir UNE compétition |
| **Classements** | Dropdown simple pour choisir UNE compétition |

### 3.5 Exemple Concret

**Contexte de travail** : Saison 2026, Groupe "N1H" → Périmètre = [N1H, NPOH]

- Sur la page **Compétitions** :
  - Affiche automatiquement N1H et NPOH (pas de filtre supplémentaire)
- Sur la page **Matchs** :
  - Multi-select avec N1H et NPOH disponibles
  - Par défaut "Toutes" coché → affiche tous les matchs
  - Peut décocher pour ne voir que N1H ou NPOH
- Sur la page **Documents** :
  - Dropdown simple avec N1H et NPOH
  - Doit choisir UNE compétition pour générer les PDFs

---

## 4. Structure de la Page d'Accueil

```
┌─────────────────────────────────────────────────────────────────┐
│  Tableau de bord                                                │
│  Bienvenue, {prénom}                                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                 │
│  ┌─────────────────────────────────────────────────────────────┐│
│  │  ⚙️  Contexte de travail                                    ││
│  │                                                             ││
│  │  ┌──────────────────────┐                                   ││
│  │  │ Saison               │                                   ││
│  │  │ ▼ 2026 *             │                                   ││
│  │  └──────────────────────┘                                   ││
│  │                                                             ││
│  │  ┌──────────────────────────────────────────────────────┐   ││
│  │  │ Périmètre de travail                                 │   ││
│  │  │                                                      │   ││
│  │  │ ○ Section entière                                    │   ││
│  │  │   [▼ Compétitions Nationales         ]               │   ││
│  │  │                                                      │   ││
│  │  │ ○ Groupe entier                                      │   ││
│  │  │   [▼ N1H - Nationale 1 Hommes        ]               │   ││
│  │  │                                                      │   ││
│  │  │ ● Compétition unique                                 │   ││
│  │  │   [▼ N1H - Nationale 1 Hommes Ph.1   ]               │   ││
│  │  │                                                      │   ││
│  │  │ ○ Événement                                          │   ││
│  │  │   [▼ Championnat de France 2026      ]               │   ││
│  │  └──────────────────────────────────────────────────────┘   ││
│  │                                                             ││
│  │  ✅ Contexte : 2026 / Groupe N1H (2 compétitions)          ││
│  │     → N1H - Nationale 1 Hommes                              ││
│  │     → NPOH - Play-offs N1 Hommes                            ││
│  └─────────────────────────────────────────────────────────────┘│
│                                                                 │
│  ┌──────────────┐ ┌──────────────┐ ┌──────────────┐ ┌──────────┐│
│  │ 📅 Événements│ │ 📄 Documents │ │ 📊 Stats     │ │ ⚙️ Opéra.││
│  └──────────────┘ └──────────────┘ └──────────────┘ └──────────┘│
│                                                                 │
│  ⚠️ Version Beta                                                │
└─────────────────────────────────────────────────────────────────┘
```

---

## 5. Store Pinia : workContextStore

### 5.1 État (State)

```typescript
interface WorkContextState {
  // Saison
  season: string

  // Type de sélection
  selectionType: 'section' | 'group' | 'competition' | 'event' | null

  // Valeurs de sélection (une seule remplie selon le type)
  sectionId: number | null
  groupCode: string | null
  competitionCode: string | null
  eventId: number | null

  // Compétitions résultantes (calculées lors de la sélection)
  competitionCodes: string[]

  // Données de référence (chargées depuis API)
  seasons: Season[]
  sections: Section[]
  groups: Group[]
  competitions: Competition[]
  events: FilterEvent[]

  // État
  initialized: boolean
  loading: boolean
}

interface Section {
  id: number
  label: string
  labelKey: string  // Clé i18n
}

interface Group {
  code: string
  libelle: string
  section: number
  competitions: string[]  // Codes des compétitions du groupe
}

interface Competition {
  code: string
  libelle: string
  soustitre: string | null
  codeRef: string | null  // Groupe parent
  section: number
  codeTypeclt: string
}
```

### 5.2 Getters

| Getter | Type | Description |
|--------|------|-------------|
| `hasValidContext` | boolean | true si saison + au moins une sélection |
| `activeSeason` | Season \| undefined | La saison active |
| `contextLabel` | string | Description du contexte (ex: "Groupe N1H") |
| `competitionCount` | number | Nombre de compétitions sélectionnées |
| `isSingleCompetition` | boolean | true si une seule compétition |
| `firstCompetition` | Competition \| undefined | Première compétition (pour pages mono) |
| `competitionsBySection` | Map<number, Competition[]> | Compétitions groupées par section |

### 5.3 Actions

| Action | Description |
|--------|-------------|
| `initContext()` | Charge depuis localStorage |
| `setSeason(code)` | Change la saison, recharge les données |
| `selectSection(sectionId)` | Sélectionne toute une section |
| `selectGroup(groupCode)` | Sélectionne tout un groupe |
| `selectCompetition(code)` | Sélectionne une compétition unique |
| `selectEvent(eventId)` | Sélectionne un événement |
| `clearContext()` | Réinitialise |
| `loadSeasonData(season)` | Charge sections, groupes, compétitions |
| `loadEvents()` | Charge la liste des événements |

### 5.4 Persistance localStorage

```typescript
// Clés localStorage
const STORAGE_KEYS = {
  season: 'kpi_admin_work_season',
  selectionType: 'kpi_admin_work_type',
  sectionId: 'kpi_admin_work_section',
  groupCode: 'kpi_admin_work_group',
  competitionCode: 'kpi_admin_work_competition',
  eventId: 'kpi_admin_work_event',
}
```

---

## 6. API Endpoints

### 6.1 Endpoints Existants

```
GET /api2/admin/filters/seasons
→ { seasons: [...], activeSeason: "2026" }

GET /api2/admin/filters/competitions?season=2026
→ { season: "2026", groups: [...] }

GET /api2/admin/filters/events
→ { events: [...] }
```

### 6.2 Nouvel Endpoint : Compétitions par Événement

```
GET /api2/admin/filters/event-competitions?eventId=123
Authorization: Bearer {token}
```

**Réponse :**
```json
{
  "eventId": 123,
  "competitions": [
    {
      "code": "N1H",
      "libelle": "Nationale 1 Hommes",
      "codeRef": "N1H"
    },
    {
      "code": "N1F",
      "libelle": "Nationale 1 Femmes",
      "codeRef": "N1F"
    }
  ]
}
```

**Implémentation :**
```sql
SELECT DISTINCT c.Code, c.Libelle, c.Code_ref
FROM kp_competition c
INNER JOIN kp_journee j ON j.Code_competition = c.Code AND j.Code_saison = c.Code_saison
INNER JOIN kp_evenement_journee ej ON ej.Id_journee = j.Id
WHERE ej.Id_evenement = ?
ORDER BY c.Code
```

---

## 7. Composants Vue

### 7.1 Structure des Fichiers

```
stores/
└── workContextStore.ts

components/admin/
├── WorkContextSelector.vue       # Sélecteur principal (page accueil)
├── CompetitionMultiSelect.vue    # Multi-select pour pages multi-compétition
└── CompetitionSingleSelect.vue   # Select simple pour pages mono-compétition

pages/
└── index.vue
```

### 7.2 WorkContextSelector.vue

Composant pour la page d'accueil avec les 4 modes de sélection (section, groupe, compétition, événement).

### 7.3 CompetitionMultiSelect.vue

Composant multi-select pour les pages Compétitions, Journées, Matchs, Statistiques :

```vue
<template>
  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ t('context.competitions_from_context') }}
    </label>
    <div class="border rounded-md p-2 max-h-48 overflow-y-auto">
      <!-- Option "Toutes" -->
      <label class="flex items-center gap-2 p-1 hover:bg-gray-50 cursor-pointer">
        <input
          type="checkbox"
          :checked="allSelected"
          :indeterminate="someSelected && !allSelected"
          @change="toggleAll"
        />
        <span class="font-medium">
          {{ t('context.all_competitions') }} ({{ availableCompetitions.length }})
        </span>
      </label>

      <hr class="my-1" />

      <!-- Compétitions individuelles -->
      <label
        v-for="comp in availableCompetitions"
        :key="comp.code"
        class="flex items-center gap-2 p-1 hover:bg-gray-50 cursor-pointer"
      >
        <input
          type="checkbox"
          :value="comp.code"
          v-model="selectedCodes"
        />
        <span>{{ comp.code }} - {{ comp.libelle }}</span>
      </label>
    </div>
  </div>
</template>

<script setup lang="ts">
const props = defineProps<{
  modelValue: string[]
}>()

const emit = defineEmits<{
  (e: 'update:modelValue', value: string[]): void
}>()

const workContext = useWorkContextStore()

const availableCompetitions = computed(() =>
  workContext.competitions.filter(c =>
    workContext.competitionCodes.includes(c.code)
  )
)

const selectedCodes = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val)
})

const allSelected = computed(() =>
  selectedCodes.value.length === availableCompetitions.value.length
)

const someSelected = computed(() =>
  selectedCodes.value.length > 0
)

function toggleAll() {
  if (allSelected.value) {
    selectedCodes.value = []
  } else {
    selectedCodes.value = availableCompetitions.value.map(c => c.code)
  }
}
</script>
```

### 7.4 CompetitionSingleSelect.vue

Composant select simple pour les pages Documents, Équipes, Classements :

```vue
<template>
  <div class="mb-4">
    <label class="block text-sm font-medium text-gray-700 mb-1">
      {{ t('context.competition_from_context') }}
    </label>
    <select
      v-model="selectedCode"
      class="w-full rounded-md border-gray-300 shadow-sm"
    >
      <option value="" disabled>{{ t('context.select_competition') }}</option>
      <option
        v-for="comp in availableCompetitions"
        :key="comp.code"
        :value="comp.code"
      >
        {{ comp.code }} - {{ comp.libelle }}
      </option>
    </select>
  </div>
</template>
```

---

## 8. Intégration dans les Pages

### 8.1 Page Compétitions

La page Compétitions utilise directement le périmètre du contexte sans filtre supplémentaire :

```typescript
// pages/competitions/index.vue
const workContext = useWorkContextStore()

onMounted(() => {
  workContext.initContext()
})

// Charger les compétitions du périmètre
const competitions = computed(() => {
  if (!workContext.hasValidContext) return []
  // Le périmètre est déjà défini dans workContext.competitionCodes
  return workContext.competitionCodes
})

// Template - affiche uniquement la saison en lecture seule
// Pas de CompetitionMultiSelect nécessaire
```

### 8.2 Pages avec Multi-Select (Journées, Matchs, Stats)

```typescript
// pages/gamedays/index.vue, pages/matches/index.vue, pages/stats/index.vue
const workContext = useWorkContextStore()

// Compétitions sélectionnées parmi le périmètre
const selectedCompetitions = ref<string[]>([])

onMounted(() => {
  workContext.initContext()
  if (workContext.hasValidContext) {
    // Par défaut, toutes les compétitions du périmètre sont sélectionnées
    selectedCompetitions.value = [...workContext.competitionCodes]
  }
})

// Template
<CompetitionMultiSelect v-model="selectedCompetitions" />

// Utilisation pour filtrer les données
const filteredData = computed(() =>
  allData.filter(item =>
    selectedCompetitions.value.includes(item.competitionCode)
  )
)
```

### 8.3 Pages avec Single-Select (Documents, Équipes, Classements)

```typescript
// pages/documents/index.vue, pages/teams/index.vue, etc.
const workContext = useWorkContextStore()

// UNE seule compétition sélectionnée
const selectedCompetition = ref<string>('')

onMounted(() => {
  workContext.initContext()
  if (workContext.hasValidContext) {
    selectedSeason.value = workContext.season
    // Pré-sélectionner la première compétition du périmètre
    if (workContext.competitionCodes.length > 0) {
      selectedCompetition.value = workContext.competitionCodes[0]
    }
  }
})

// Template
<CompetitionSingleSelect v-model="selectedCompetition" />
```

### 8.4 Comportement de la Saison

**La saison de travail ne peut être changée que depuis la page d'accueil.**

Sur les autres pages :
- La saison est affichée en lecture seule (badge ou texte)
- Pas de sélecteur de saison modifiable
- La saison vient toujours du contexte de travail

```vue
<!-- Affichage de la saison sur les autres pages -->
<div class="flex items-center gap-2 text-sm text-gray-600 mb-4">
  <UIcon name="i-heroicons-calendar" class="w-4 h-4" />
  <span>{{ t('context.season') }}: <strong>{{ workContext.season }}</strong></span>
  <NuxtLink to="/" class="text-blue-600 hover:underline text-xs">
    {{ t('context.change') }}
  </NuxtLink>
</div>
```

---

## 9. Traductions i18n

### 9.1 Français (fr.json)

```json
{
  "context": {
    "title": "Contexte de travail",
    "season": "Saison",
    "scope": "Périmètre de travail",
    "type_section": "Section entière",
    "type_group": "Groupe entier",
    "type_competition": "Compétition unique",
    "type_event": "Événement",
    "select_season": "Sélectionnez une saison",
    "select_section": "Sélectionnez une section",
    "select_group": "Sélectionnez un groupe",
    "select_competition": "Sélectionnez une compétition",
    "select_event": "Sélectionnez un événement",
    "competitions_from_context": "Compétitions (du contexte)",
    "competition_from_context": "Compétition (du contexte)",
    "all_competitions": "Toutes",
    "current": "Contexte",
    "competitions_count": "{count} compétition(s)",
    "no_context": "Aucun contexte sélectionné",
    "active_season": "Saison active",
    "change": "Modifier",
    "sections": {
      "1": "Compétitions Internationales",
      "2": "Compétitions Nationales",
      "3": "Compétitions Régionales",
      "4": "Tournois Internationaux",
      "5": "Continents",
      "100": "Divers"
    }
  }
}
```

### 9.2 Anglais (en.json)

```json
{
  "context": {
    "title": "Working Context",
    "season": "Season",
    "scope": "Working Scope",
    "type_section": "Entire Section",
    "type_group": "Entire Group",
    "type_competition": "Single Competition",
    "type_event": "Event",
    "select_season": "Select a season",
    "select_section": "Select a section",
    "select_group": "Select a group",
    "select_competition": "Select a competition",
    "select_event": "Select an event",
    "competitions_from_context": "Competitions (from context)",
    "competition_from_context": "Competition (from context)",
    "all_competitions": "All",
    "current": "Context",
    "competitions_count": "{count} competition(s)",
    "no_context": "No context selected",
    "active_season": "Active season",
    "change": "Change",
    "sections": {
      "1": "International Competitions",
      "2": "National Competitions",
      "3": "Regional Competitions",
      "4": "International Tournaments",
      "5": "Continents",
      "100": "Other"
    }
  }
}
```

---

## 10. Comportements Spécifiques

### 10.1 Calcul des Compétitions Résultantes

```typescript
function computeCompetitionCodes(): string[] {
  switch (selectionType) {
    case 'section':
      // Toutes les compétitions de la section
      return competitions
        .filter(c => c.section === sectionId)
        .map(c => c.code)

    case 'group':
      // Toutes les compétitions du groupe (même codeRef)
      return competitions
        .filter(c => c.codeRef === groupCode)
        .map(c => c.code)

    case 'competition':
      return [competitionCode]

    case 'event':
      // Chargé depuis l'API /event-competitions
      return eventCompetitionCodes
  }
}
```

### 10.2 Changement de Saison

1. Vider la sélection courante
2. Recharger sections, groupes, compétitions pour la nouvelle saison
3. Conserver le type de sélection si possible

### 10.3 Permissions

- Les API filtrent automatiquement selon les droits utilisateur
- Un contexte sauvegardé non accessible est ignoré
- L'interface n'affiche que les éléments autorisés

---

## 11. Checklist d'Implémentation

### Phase 1 : Backend API

- [ ] Créer endpoint `/admin/filters/event-competitions`
- [ ] Ajouter les compétitions groupées par `codeRef` dans `/admin/filters/competitions`

### Phase 2 : Store

- [ ] Créer `stores/workContextStore.ts`
- [ ] Implémenter les 4 modes de sélection
- [ ] Gérer la persistance localStorage
- [ ] Calculer les compétitions résultantes

### Phase 3 : Composants

- [ ] Créer `WorkContextSelector.vue` avec radio buttons et dropdowns
- [ ] Créer `CompetitionMultiSelect.vue` pour pages multi-compétition (checkbox list avec "Toutes")
- [ ] Créer `CompetitionSingleSelect.vue` pour pages mono-compétition (dropdown simple)
- [ ] Ajouter traductions i18n

### Phase 4 : Page d'Accueil

- [ ] Modifier `pages/index.vue`
- [ ] Intégrer WorkContextSelector
- [ ] Afficher le récapitulatif du contexte

### Phase 5 : Intégration Pages

- [ ] `pages/competitions/index.vue` - utilise directement le périmètre (pas de filtre)
- [ ] `pages/gamedays/index.vue` - multi-select avec CompetitionMultiSelect
- [ ] `pages/matches/index.vue` - multi-select avec CompetitionMultiSelect
- [ ] `pages/stats/index.vue` - multi-select avec CompetitionMultiSelect
- [ ] `pages/documents/index.vue` - mono avec CompetitionSingleSelect
- [ ] `pages/teams/index.vue` - mono avec CompetitionSingleSelect
- [ ] `pages/rankings/index.vue` - mono avec CompetitionSingleSelect

### Phase 6 : Tests

- [ ] Tester chaque type de sélection
- [ ] Tester persistance localStorage
- [ ] Tester changement de saison
- [ ] Tester navigation entre pages
- [ ] Tester avec différents profils utilisateur
