# Spécification — Page Event Cache Manager

## Statut : 📋 À IMPLÉMENTER

## 1. Vue d'ensemble

Page d'administration permettant de contrôler le **worker CLI** (`event_worker.php`) qui génère en continu les fichiers JSON de cache utilisés par les overlays vidéo des retransmissions TV (compétitions de kayak-polo). Le worker tourne en tâche de fond, lit sa configuration dans `kp_event_worker_config` et n'a aucun couplage HTTP avec l'UI : la page se contente donc de manipuler ce tableau de configuration via api2.

La page remplace l'écran legacy `sources/live/event.php`, qui s'appuie aujourd'hui sur la session PHP héritée et sur l'API REST `sources/live/api_worker.php`. L'objectif est de déporter le contrôle vers app4 (Nuxt 4 + JWT), avec le système de profils app4 (≤ 2 = admin) au lieu de la session legacy.

**Route** : `/live/cache-manager`

**Accès** : Profil ≤ 2 (Admin)

**Pages PHP Legacy de référence** :
- `sources/live/event.php` (UI - 363 lignes)
- `sources/live/js/event.js` (logique frontend jQuery - 468 lignes)
- `sources/live/api_worker.php` (API REST de contrôle - 333 lignes)
- `sources/live/ajax_cache_event.php` (monitoring temps réel - 39 lignes)

**Implémentation Nuxt** : `sources/app4/pages/live/cache-manager.vue`

**Backend Symfony** : `sources/api2/src/Controller/AdminEventWorkerController.php` (préfixe `/admin/events/worker`)

**Worker CLI conservé tel quel** :
- `sources/live/event_worker.php` : lit `kp_event_worker_config` toutes les `delay_event` secondes, génère les fichiers de cache via `CacheMatch::Event()`. Voir `event_worker.php:201-216` : `sendHeartbeat()` écrit directement en DB via PDO, donc le worker n'a aucune dépendance HTTP vers `api_worker.php`. Aucune modification requise.

**Contexte de travail** : N'utilise PAS le `workContextStore`. L'événement est sélectionné localement dans la page (même approche que `/tv`).

---

## 2. Architecture

### 2.1 Avant migration (état actuel)

```
┌─────────────────────────┐
│  Browser (event.php)    │  ← session PHP legacy + jQuery
│  - poll status (5s)     │
│  - start/stop/pause     │
│  - monitor modal        │
└──────────┬──────────────┘
           │ HTTP (cookies)
           ▼
┌─────────────────────────┐         ┌──────────────────────┐
│  api_worker.php         │ ──UPSERT──►│ kp_event_worker_   │
│  ajax_cache_event.php   │ ───SELECT──►│   config            │
└─────────────────────────┘         └──────────┬───────────┘
                                               │ poll (CLI)
                                               ▼
                                    ┌──────────────────────┐
                                    │ event_worker.php     │
                                    │ (CLI process)        │
                                    │ - reads config       │
                                    │ - writes JSON cache  │
                                    │ - heartbeat (PDO)    │
                                    └──────────┬───────────┘
                                               │ writes
                                               ▼
                                    ┌──────────────────────┐
                                    │ /live/cache/*.json   │
                                    └──────────┬───────────┘
                                               │ poll (XHR)
                                               ▼
                                    ┌──────────────────────┐
                                    │ TV overlays          │
                                    └──────────────────────┘
```

### 2.2 Après migration (cible)

```
┌─────────────────────────────┐
│  Nuxt 4 (cache-manager.vue) │  ← JWT + profile guard ≤ 2
│  pages/live/cache-manager   │
│  - poll status (5s)         │
│  - start/stop/pause/resume  │
│  - monitor modal            │
└──────────┬──────────────────┘
           │ HTTPS + Bearer JWT
           ▼
┌─────────────────────────────┐         ┌──────────────────────┐
│ AdminEventWorkerController  │ ──UPSERT──►│ kp_event_worker_   │
│ /admin/events/worker/*      │ ───SELECT──►│   config            │
│ (Symfony, IsGranted ROLE_   │         └──────────┬───────────┘
│  ADMIN)                     │                    │ poll (CLI)
└─────────────────────────────┘                    ▼
                                        ┌──────────────────────┐
                                        │ event_worker.php     │
                                        │ ★ INCHANGÉ ★         │
                                        │ - heartbeat via PDO  │
                                        └──────────┬───────────┘
                                                   ▼
                                        ┌──────────────────────┐
                                        │ /live/cache/*.json   │
                                        └──────────────────────┘

(legacy event.php / api_worker.php / ajax_cache_event.php :
 conservés, marqués @deprecated, encore utilisés par les pages
 legacy GestionOperations.tpl et kptv.tpl)
```

### 2.3 Points clés

- Le worker CLI n'a **aucune** dépendance modifiée. Il continue à lire/écrire `kp_event_worker_config` via PDO direct.
- L'UI app4 ne parle plus à `api_worker.php`. Toutes les actions UI passent par api2 (Symfony, JWT, profil ≤ 2).
- `ajax_cache_event.php` est remplacé par `GET /admin/events/worker/{idEvent}/monitor` (logique SQL portée en Doctrine DBAL).
- Les fichiers legacy restent en place car ils sont encore référencés par d'autres pages legacy (cf. §10).

---

## 3. Endpoints API2 à créer

Tous les endpoints sont déclarés dans **un nouveau contrôleur** :

- **Fichier** : `sources/api2/src/Controller/AdminEventWorkerController.php`
- **Préfixe de route** : `/admin/events/worker`
- **Annotations classe** :
  ```php
  #[Route('/admin/events/worker')]
  #[IsGranted('ROLE_ADMIN')]
  #[OA\Tag(name: '36. App4 - Event Cache Worker')]
  ```
- **Injection** : `Doctrine\DBAL\Connection $connection` (pas d'entités Doctrine, on reste en SQL natif comme dans `AdminTvController`).

### 3.1 GET /admin/events/worker/status

Retourne **toutes** les configs en état `running` ou `paused`, enrichies (équivalent à `getWorkerConfigs()` + `enrichConfig()` de `api_worker.php:268-321`).

**Réponse** :
```json
[
  {
    "id": 12,
    "idEvent": 222,
    "dateEvent": "2025-07-15",
    "hourEvent": "09:30:00",
    "hourEventInitial": "09:30:00",
    "offsetEvent": 15,
    "pitchEvent": 4,
    "delayEvent": 10,
    "status": "running",
    "lastExecution": "2026-04-26 14:22:18",
    "createdAt": "2026-04-26 13:00:00",
    "updatedAt": "2026-04-26 14:22:18",
    "executionCount": 432,
    "errorMessage": null,
    "secondsSinceLastExecution": 4,
    "currentSimulatedTime": "10:42:00",
    "isRunning": true,
    "isPaused": false,
    "isStopped": false,
    "isHealthy": true
  }
]
```

**SQL** (identique à `api_worker.php:270-274`) :
```sql
SELECT *,
       UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(last_execution) AS seconds_since_last_execution
FROM kp_event_worker_config
WHERE status IN ('running', 'paused')
ORDER BY id_event ASC
```

**Champs calculés** (à reproduire fidèlement d'après `api_worker.php:291-321`) :
- `currentSimulatedTime` = `date('H:i:s', strtotime(date_event + ' ' + hour_event_initial) + execution_count * delay_event)`
- `isHealthy` = `secondsSinceLastExecution < (delay_event * 3)` (sinon le worker semble figé)

### 3.2 POST /admin/events/worker/start

Upsert d'une configuration et passage à `running`.

**Body JSON** :
```json
{
  "idEvent": 222,
  "dateEvent": "2025-07-15",
  "hourEvent": "09:30",
  "offsetEvent": 15,
  "pitchEvent": 4,
  "delayEvent": 10
}
```

**Validation** : `idEvent`, `dateEvent`, `hourEvent` obligatoires (HTTP 400 sinon — cf. `api_worker.php:40-42`).

**Logique** :
1. `SELECT id FROM kp_event_worker_config WHERE id_event = ? AND status IN ('running','paused')`
2. Si trouvé → `UPDATE` (avec `error_message = NULL` et `status='running'`)
3. Sinon → `INSERT` (avec `status='running'`, `hour_event_initial = hour_event`)

**Note importante** : `hour_event_initial` est figé à l'insertion **et** lors de chaque (re)start (cf. `api_worker.php:55-56`). C'est la référence pour calculer `currentSimulatedTime`.

**Réponse** : la config créée/mise à jour, enrichie (même format qu'un élément du tableau §3.1).

### 3.3 POST /admin/events/worker/stop et /admin/events/worker/{idEvent}/stop

Arrête un événement (ou tous si `{idEvent}` absent).

**Deux routes Symfony** dans le même contrôleur :
```php
#[Route('/stop', methods: ['POST'])]
public function stopAll(): JsonResponse { /* stop all */ }

#[Route('/{idEvent}/stop', methods: ['POST'], requirements: ['idEvent' => '\d+'])]
public function stopOne(int $idEvent): JsonResponse { /* stop one */ }
```

**Logique** :
```sql
UPDATE kp_event_worker_config
SET status='stopped', updated_at=NOW()
WHERE [id_event = ? AND] status IN ('running','paused')
```

**Réponse** : la liste actualisée (même format que §3.1) pour rafraîchir l'UI sans second appel.

### 3.4 POST /admin/events/worker/{idEvent}/pause

```sql
UPDATE kp_event_worker_config SET status='paused', updated_at=NOW()
WHERE id_event = ? AND status='running'
```

**Réponse** : liste actualisée (§3.1).

### 3.5 POST /admin/events/worker/{idEvent}/resume

```sql
UPDATE kp_event_worker_config SET status='running', updated_at=NOW()
WHERE id_event = ? AND status='paused'
```

**Réponse** : liste actualisée (§3.1).

### 3.6 PATCH /admin/events/worker/{idEvent}

Met à jour à chaud les paramètres `offsetEvent` / `pitchEvent` / `delayEvent` sans changer `status` (équivalent action `update` de `api_worker.php:190-231`). Méthode `PATCH` retenue plutôt que `POST /update` pour respecter REST.

**Body** (tous optionnels, au moins un requis) :
```json
{ "offsetEvent": 20, "pitchEvent": 6, "delayEvent": 15 }
```

**Réponse** : la config mise à jour (enrichie).

### 3.7 GET /admin/events/worker/{idEvent}/dates

Remplace `Btn_Events()` (cf. `event.php:215-243`). Retourne la liste des dates de matchs pour l'événement, avec l'heure du **premier** match de chaque date (pour pré-remplir `hour_event` quand l'admin clique sur un bouton "date rapide").

**SQL** (simplifié — la sous-requête legacy est inutile) :
```sql
SELECT m.Date_match  AS dateMatch,
       MIN(m.Heure_match) AS heureMatch
FROM kp_match m
LEFT JOIN kp_evenement_journee ej ON m.Id_journee = ej.Id_journee
WHERE ej.Id_evenement = ?
GROUP BY m.Date_match
ORDER BY m.Date_match
```

**Réponse** :
```json
[
  { "dateMatch": "2025-07-15", "heureMatch": "09:30:00" },
  { "dateMatch": "2025-07-16", "heureMatch": "09:00:00" }
]
```

### 3.8 GET /admin/events/worker/{idEvent}/monitor

Retourne, pour chaque pitch, le match en cours et le match suivant. Remplace `ajax_cache_event.php`.

**Query params** :
- `dateEvent` (string YYYY-MM-DD, requis)
- `hourEvent` (string HH:MM, requis — heure « réelle » côté UI, généralement `currentSimulatedTime` du worker)
- `offsetEvent` (int, défaut 15)
- `pitchEvent` (int, défaut 4)

**Logique** (port natif depuis `CacheMatch::Event()` — `create_cache_match.php:360-417`) :
1. Calculer `hourEventWork = hourEvent + offsetEvent` (en minutes — utilitaires HHMM↔MM à porter localement, cf. `commun/MyTools.php`).
2. ```sql
   SELECT a.*, b.Code_competition
   FROM kp_match a
   JOIN kp_journee b ON a.Id_journee = b.Id
   JOIN kp_evenement_journee c ON b.Id = c.Id_journee
   WHERE c.Id_evenement = ?
     AND a.Date_match = ?
     AND a.Publication = 'O'
     AND a.Terrain IN (?, ?, ?, ...)
   ORDER BY a.Heure_match, a.Terrain
   ```
3. Pour chaque pitch (1..pitchEvent), appliquer la logique :
   - **GetBestMatch** : dernier match dont `Heure_match <= hourEventWork` ET statut ≠ 'ATT'.
   - **GetNextMatch** : premier match dont `Heure_match > hourEventWork` ET statut = 'ATT'.

**Important** : contrairement à `CacheMatch::Event()`, l'endpoint api2 **ne doit pas** écrire les fichiers JSON (`Pitch()`, `MatchGlobal()`). C'est le rôle exclusif du worker CLI. L'endpoint `/monitor` sert uniquement à afficher l'état dans l'UI.

**Réponse** :
```json
{
  "pitches": [
    {
      "pitch": "1",
      "game": 79290575,
      "num": 101,
      "time": "09:30:00",
      "next": { "id": 79290579, "time": "10:00:00", "num": 105 }
    },
    {
      "pitch": "2",
      "game": null,
      "num": null,
      "time": null,
      "next": { "id": null, "time": null, "num": null }
    }
  ],
  "time": {
    "currentTime": "10:42",
    "workingTime": "10:57"
  }
}
```

---

## 4. Endpoints existants à réutiliser

### 4.1 GET /admin/tv/events

Confirmé dans `sources/api2/src/Controller/AdminTvController.php:36-53`. Retourne tous les événements publiés (`Publication='O'`) :

```json
[
  { "id": 222, "libelle": "ECA European Championships",
    "lieu": "Avranches (FRA)", "dateDebut": "2025-07-15", "dateFin": "2025-07-20" }
]
```

→ Réutilisé tel quel pour le dropdown « Event ». **Ne pas créer** de doublon `/admin/events/worker/events`.

---

## 5. Page Vue

### 5.1 Fichier

`sources/app4/pages/live/cache-manager.vue` (nouveau dossier `pages/live/` à créer).

### 5.2 Squelette

```vue
<script setup lang="ts">
import { useAuthStore } from '~/stores/authStore'
import type {
  WorkerConfig, WorkerEvent, WorkerDate, WorkerMonitor, WorkerForm
} from '~/types/eventWorker'

definePageMeta({ layout: 'admin', middleware: 'auth' })

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()
const toast = useToast()

if (authStore.profile > 2) navigateTo('/')

// ─── State ───
const events = ref<WorkerEvent[]>([])
const dates = ref<WorkerDate[]>([])
const configs = ref<WorkerConfig[]>([])
const form = ref<WorkerForm>({
  idEvent: null,
  dateEvent: new Date().toISOString().slice(0, 10),
  hourEvent: '',
  offsetEvent: 15,
  pitchEvent: 4,
  delayEvent: 10,
})
const monitorOpen = ref(false)
const monitorConfig = ref<WorkerConfig | null>(null)
const monitorData = ref<WorkerMonitor | null>(null)

let statusTimer: ReturnType<typeof setInterval> | null = null
let monitorTimer: ReturnType<typeof setInterval> | null = null

// ─── Computed ───
const hasActive = computed(() => configs.value.length > 0)
const hasRunning = computed(() => configs.value.some(c => c.isRunning))

// ─── API calls ───
async function loadEvents() {
  events.value = await api.get<WorkerEvent[]>('/admin/tv/events')
}
async function loadDates(idEvent: number) {
  dates.value = await api.get<WorkerDate[]>(`/admin/events/worker/${idEvent}/dates`)
}
async function loadStatus() {
  configs.value = await api.get<WorkerConfig[]>('/admin/events/worker/status')
}
async function startWorker() {
  if (!form.value.idEvent || !form.value.dateEvent || !form.value.hourEvent) {
    toast.add({ title: t('eventCacheManager.errors.missing_fields'), color: 'warning' })
    return
  }
  await api.post('/admin/events/worker/start', form.value)
  toast.add({ title: t('eventCacheManager.toasts.started'), color: 'success' })
  await loadStatus()
}
async function pauseWorker(idEvent: number)  { await api.post(`/admin/events/worker/${idEvent}/pause`);  await loadStatus() }
async function resumeWorker(idEvent: number) { await api.post(`/admin/events/worker/${idEvent}/resume`); await loadStatus() }
async function stopWorker(idEvent: number)   { /* confirm + POST stop */ }
async function stopAll()                     { /* confirm + POST /stop sans id */ }

// ─── Monitor modal ───
async function openMonitor(c: WorkerConfig) {
  monitorConfig.value = c
  monitorOpen.value = true
  await refreshMonitor()
  if (monitorTimer) clearInterval(monitorTimer)
  monitorTimer = setInterval(refreshMonitor, c.delayEvent * 1000)
}
async function refreshMonitor() {
  if (!monitorConfig.value) return
  monitorData.value = await api.get<WorkerMonitor>(
    `/admin/events/worker/${monitorConfig.value.idEvent}/monitor`,
    {
      dateEvent: monitorConfig.value.dateEvent,
      hourEvent: monitorConfig.value.currentSimulatedTime.slice(0, 5),
      offsetEvent: monitorConfig.value.offsetEvent,
      pitchEvent: monitorConfig.value.pitchEvent,
    }
  )
}
function closeMonitor() {
  monitorOpen.value = false
  monitorConfig.value = null
  monitorData.value = null
  if (monitorTimer) { clearInterval(monitorTimer); monitorTimer = null }
}

// ─── Watchers ───
watch(() => form.value.idEvent, async (id) => {
  dates.value = []
  if (id) await loadDates(id)
})

// ─── Lifecycle ───
onMounted(async () => {
  const now = new Date()
  form.value.hourEvent = `${String(now.getHours()).padStart(2,'0')}:${String(now.getMinutes()).padStart(2,'0')}`
  await Promise.all([loadEvents(), loadStatus()])
  statusTimer = setInterval(loadStatus, 5000)
})
onBeforeUnmount(() => {
  if (statusTimer) clearInterval(statusTimer)
  if (monitorTimer) clearInterval(monitorTimer)
})
</script>
```

### 5.3 Sections du template

```
┌──────────────────────────────────────────────────────────────┐
│ Event Cache Manager                                          │
│ (page header + sous-titre)                                   │
├──────────────────────────────────────────────────────────────┤
│ § Active workers (UCard)                                     │
│   - Si configs.length === 0 : message "Aucun worker actif"   │
│   - Sinon : pour chaque config (UCard + UBadge):             │
│       Event #id | Status (running/paused) | Healthy badge    │
│       Date | Heure simulée | Pitches | Delay | Executions    │
│       Actions: [Monitor] [Pause/Resume] [Stop]               │
│   - Bouton global [Stop All] si hasRunning                   │
├──────────────────────────────────────────────────────────────┤
│ § New worker configuration (UCard)                           │
│   - USelect Event (events)                                   │
│   - Boutons rapides date (dates) → set form.date+heure       │
│   - UInput type=date  (dateEvent)                            │
│   - UInput type=time  (hourEvent)                            │
│   - UInput type=number (offsetEvent)  [warm-up min]          │
│   - UInput type=number (pitchEvent)   [nb terrains]          │
│   - UInput type=number (delayEvent)   [refresh sec]          │
│   - UButton [Start Worker]                                   │
└──────────────────────────────────────────────────────────────┘

UModal Monitor (overlay) :
  ┌─────────────────────────────────────────────────────────┐
  │ Live Monitoring - Event #222                  [×]       │
  │ Date 2025-07-15 | Initial 09:30 | Refresh every 10s     │
  │ ┌──────┬──────────────┬──────────────────────────────┐ │
  │ │ Pitch│ Current game │ Next game                    │ │
  │ │      │ Time Num ID  │ Time Num ID                  │ │
  │ ├──────┼──────────────┼──────────────────────────────┤ │
  │ │  1   │ 09:30 #101   │ 10:00 #105                   │ │
  │ │  2   │ Waiting...   │ 10:00 #106                   │ │
  │ │  3   │ 09:30 #103   │ Waiting...                   │ │
  │ │  4   │ 09:30 #104   │ 10:00 #108                   │ │
  │ └──────┴──────────────┴──────────────────────────────┘ │
  │ Last update: 14:22:18                                   │
  └─────────────────────────────────────────────────────────┘
```

### 5.4 Types TypeScript

À placer dans `sources/app4/types/eventWorker.ts` :

```typescript
export interface WorkerEvent {
  id: number
  libelle: string
  lieu: string
  dateDebut: string
  dateFin: string
}

export interface WorkerDate {
  dateMatch: string  // YYYY-MM-DD
  heureMatch: string // HH:MM:SS
}

export interface WorkerConfig {
  id: number
  idEvent: number
  dateEvent: string
  hourEvent: string
  hourEventInitial: string
  offsetEvent: number
  pitchEvent: number
  delayEvent: number
  status: 'running' | 'paused' | 'stopped'
  lastExecution: string | null
  createdAt: string
  updatedAt: string
  executionCount: number
  errorMessage: string | null
  secondsSinceLastExecution: number | null
  currentSimulatedTime: string
  isRunning: boolean
  isPaused: boolean
  isStopped: boolean
  isHealthy: boolean
}

export interface WorkerForm {
  idEvent: number | null
  dateEvent: string
  hourEvent: string
  offsetEvent: number
  pitchEvent: number
  delayEvent: number
}

export interface WorkerMonitorPitch {
  pitch: string
  game: number | null
  num: number | null
  time: string | null
  next: { id: number | null; time: string | null; num: number | null }
}

export interface WorkerMonitor {
  pitches: WorkerMonitorPitch[]
  time: { currentTime: string; workingTime: string }
}
```

---

## 6. Composants UI

Aucun nouveau composant partagé n'est strictement nécessaire — la page peut être autonome. Tous les éléments sont des primitives Nuxt UI déjà disponibles dans le projet.

| Élément du template | Composant Nuxt UI |
|---------------------|-------------------|
| Cartes worker actif / configuration | `UCard` |
| Boutons d'action (Start, Stop, Pause, Resume, Monitor) | `UButton` |
| Badge statut (running/paused/stopped) | `UBadge` (color=success/warning/error) |
| Indicateur de santé (heartbeat OK/KO) | `UIcon` (heroicons:check-circle / exclamation-triangle) |
| Sélection événement | `USelect` ou `USelectMenu` |
| Boutons rapides date | boucle `UButton size="xs"` |
| Champs date / heure / nombres | `UInput` (type=date / time / number) |
| Modal monitoring | `UModal` |
| Tableau pitches | `UTable` ou table HTML simple stylée |
| Confirmation stop | composant existant `sources/app4/components/admin/ConfirmModal.vue` |
| Toasts succès/erreur | `useToast()` |

**Optionnel** : si la page grossit, extraire :
- `sources/app4/components/admin/eventWorker/StatusList.vue` (rendu des cards par config)
- `sources/app4/components/admin/eventWorker/ConfigForm.vue` (formulaire de start)
- `sources/app4/components/admin/eventWorker/MonitorModal.vue` (modal monitoring)

Décision recommandée : commencer **inline** (~250 lignes) et extraire seulement si besoin.

---

## 7. i18n

Toutes les clés dans une nouvelle section `eventCacheManager` de `sources/app4/i18n/locales/fr.json` et `en.json`.

### 7.1 Clé de menu

À ajouter dans le bloc `menu` (cf. `fr.json:14-38`) :

| Clé | FR | EN |
|-----|----|----|
| `menu.event_cache_manager` | `"Cache d'événement"` | `"Event cache"` |

### 7.2 Bloc `eventCacheManager` (FR)

```json
{
  "eventCacheManager": {
    "title": "Gestion du cache d'événement",
    "subtitle": "Contrôle du worker de génération automatique des caches",
    "active_workers": {
      "title": "Workers actifs",
      "none": "Aucun worker actif",
      "stop_all": "Tout arrêter",
      "monitor": "Monitor",
      "pause": "Pause",
      "resume": "Reprendre",
      "stop": "Arrêter",
      "executions": "Exécutions",
      "last_execution": "Dernière",
      "current_time": "Heure simulée",
      "initial_time": "Heure initiale",
      "pitches": "Terrains",
      "delay": "Délai",
      "warmup": "Warm-up",
      "healthy": "Sain",
      "unhealthy": "Inactif (heartbeat manqué)"
    },
    "form": {
      "title": "Nouvelle configuration",
      "event": "Événement",
      "event_placeholder": "Sélectionner un événement",
      "quick_date": "Sélection rapide",
      "date": "Date",
      "hour": "Heure de départ",
      "hour_help": "Heure initiale de référence",
      "offset": "Warm-up (min)",
      "pitch": "Nombre de terrains",
      "delay": "Délai de rafraîchissement (sec)",
      "start": "Démarrer le worker"
    },
    "status": {
      "running": "En cours",
      "paused": "En pause",
      "stopped": "Arrêté"
    },
    "monitor": {
      "title": "Monitoring temps réel",
      "event": "Événement",
      "date": "Date",
      "initial_time": "Heure initiale",
      "refresh_every": "Rafraîchissement toutes les {seconds}s",
      "pitch": "Terrain",
      "current_game": "Match en cours",
      "next_game": "Match suivant",
      "time": "Heure",
      "num": "N°",
      "match_id": "ID match",
      "waiting": "En attente...",
      "last_update": "Dernière mise à jour : {time}",
      "load_failed": "Impossible de charger le monitoring"
    },
    "confirm": {
      "stop_one": "Arrêter le worker pour l'événement #{id} ?",
      "stop_all": "Arrêter TOUS les workers actifs ?"
    },
    "toasts": {
      "started": "Worker démarré avec succès",
      "stopped": "Worker arrêté",
      "paused": "Worker en pause",
      "resumed": "Worker repris",
      "stop_all_done": "Tous les workers ont été arrêtés"
    },
    "errors": {
      "missing_fields": "Événement, date et heure sont obligatoires"
    }
  }
}
```

### 7.3 Version EN

Mêmes clés, traductions anglaises. Important (mémoire projet) : « Match » → « Game » en EN. Donc :
- `current_game`: `"Current game"`
- `next_game`: `"Next game"`
- `match_id`: `"Game ID"`
- `confirm.stop_one`: `"Stop worker for event #{id}?"`
- `form.event`: `"Event"`
- `active_workers.title`: `"Active workers"`
- etc.

---

## 8. Menu integration

Modification dans `sources/app4/components/admin/Header.vue` lignes 192-208.

**Diff exact** :

```ts
  // Live: TV
  const live: MenuItem[] = []
  if (profile <= 2) {
    live.push({
      to: '/tv',
      icon: 'heroicons:tv',
      label: t('menu.tv')
    })
+   live.push({
+     to: '/live/cache-manager',
+     icon: 'heroicons:server-stack',
+     label: t('menu.event_cache_manager')
+   })
  }
  if (live.length > 0) {
    groups.push({
      key: 'live',
      label: t('menu.live'),
      icon: 'heroicons:signal',
      items: live
    })
  }
```

**Icône suggérée** : `heroicons:server-stack` (cohérent avec une notion de worker/processus). Alternative : `heroicons:bolt`.

**Rappel** : le lien "Event cache generator" dans `sources/app4/pages/tv/index.vue:226-232` pointe encore vers `${backendBaseUrl}/live/event.php`. Une fois la nouvelle page validée, **remplacer** ce lien par un `NuxtLink` vers `/live/cache-manager`.

---

## 9. Worker CLI : INCHANGÉ

`sources/live/event_worker.php` reste tel quel. Confirmation par lecture :
- `event_worker.php:188-196` : `getWorkerConfigs()` lit `kp_event_worker_config` directement via PDO.
- `event_worker.php:201-216` : `sendHeartbeat()` écrit directement via PDO (`UPDATE ... SET last_execution=NOW(), execution_count=execution_count+1 ...`). **Aucun appel HTTP à `api_worker.php`**.

→ La migration de l'UI n'a aucun impact sur le worker. La table `kp_event_worker_config` est le contrat partagé.

**Tableau de référence** : `kp_event_worker_config`. Schéma défini dans `SQL/20251111_create_event_worker_config.sql`. Aucune migration DB nécessaire.

---

## 10. Fichiers legacy : conservation et documentation

**Décision retenue** : les fichiers PHP legacy sont **conservés indéfiniment**, marqués `@deprecated`, et leur usage par d'autres pages legacy est documenté.

### 10.1 Vérification effectuée

`grep` exhaustif sur `sources/` (hors `wordpress_archive`, `vendor`, `smarty/templates_c`, `.output`, `.nuxt`, `node_modules`) confirme que les fichiers `live/event.php`, `live/api_worker.php`, `live/ajax_cache_event.php`, `live/js/event.js` sont référencés en dehors du dossier `live/` par :

| Fichier appelant | Référence | Statut |
|---|---|---|
| `sources/app4/pages/tv/index.vue:226-232` | `<a :href="\`${backendBaseUrl}/live/event.php\`">` | À remplacer par `NuxtLink to="/live/cache-manager"` après mise en place de la nouvelle page |
| `sources/smarty/templates/GestionOperations.tpl:381` | `<a href="{$url_base}/live/event.php" target="_blank">Event Cache Worker</a>` | Page legacy d'admin — laisser tel quel (tant que `GestionOperations.tpl` est utilisé) |
| `sources/smarty/templates/kptv.tpl:1207` | `<a id="event_params" href="live/event.php" target="_blank">` | Page legacy TV — laisser tel quel (tant que `kptv.tpl` est utilisé) |

→ Les pages `GestionOperations.tpl` et `kptv.tpl` étant elles-mêmes encore actives, la suppression des fichiers `live/event.php` & co. casserait ces UI. Conservation justifiée.

### 10.2 Marquage `@deprecated`

Ajouter en en-tête de chaque fichier ci-dessous un bloc PHPDoc / commentaire JS :

**`sources/live/event.php`** :
```php
<?php
/**
 * @deprecated Cette page est dépréciée depuis la migration vers app4.
 *             Utiliser à la place : /live/cache-manager (Nuxt 4).
 *             Spec : DOC/specs/PAGE_EVENT_CACHE_MANAGER.md
 *             Conservée car référencée par GestionOperations.tpl et kptv.tpl.
 */
include_once('base.php');
// ...
```

**`sources/live/api_worker.php`**, **`sources/live/ajax_cache_event.php`**, **`sources/live/js/event.js`** : même type d'en-tête (avec syntaxe `/** */` pour le `.js`).

### 10.3 Mise à jour du README

Ajouter une note en haut de `sources/live/EVENT_WORKER_README.md` :

> ⚠️ **Migration app4** : depuis [date], le contrôle du worker est disponible dans app4 à l'URL `/live/cache-manager` (auth JWT, profil ≤ 2). Les fichiers `event.php`, `api_worker.php`, `ajax_cache_event.php` et `js/event.js` restent fonctionnels mais sont marqués `@deprecated`. Le worker CLI `event_worker.php` est inchangé et continue de tourner indépendamment de l'UI utilisée.

### 10.4 Conservés intacts

- `sources/live/event_worker.php` (worker CLI — utilisé en production)
- `sources/live/create_cache_match.php` (utilisé par le worker et par d'autres pages live)

---

## 11. Risques & open questions

### 11.1 Format des heures

La BD stocke `TIME` (HH:MM:SS) pour `hour_event` / `hour_event_initial`. L'UI envoie `HH:MM`. Le contrôleur api2 doit normaliser (concaténer `:00` ou utiliser `STR_TO_DATE`) pour rester compatible avec `event_worker.php:101-102` qui fait `strtotime($config['date_event'] . ' ' . $config['hour_event_initial'])`.

### 11.2 Format `currentSimulatedTime`

Le worker calcule en `microtime` et reformate en `H:i:s`. L'API `/status` renvoie le même format. Pour le monitor, l'UI renvoie en query string un `hourEvent` au format `HH:MM` (extrait avec `slice(0, 5)`).

### 11.3 Concurrence sur `start`

Si deux admins start simultanément le même `id_event`, le `SELECT … WHERE status IN (…)` puis `INSERT/UPDATE` n'est pas atomique. Risque très faible (même `id_event`). Mitigation possible : `INSERT ... ON DUPLICATE KEY UPDATE` avec un index unique sur `id_event`. À ne pas faire dans un premier temps (l'unicité actuelle n'est pas garantie côté schéma) — à noter en TODO.

### 11.4 Side-effect potentiel de `GetNextMatch` legacy

La méthode `GetNextMatch` legacy (cf. `create_cache_match.php:467-469`) crée un fichier `_match_global.json` à la volée. **Ne pas reproduire** ce side-effect dans le port api2 : le monitoring est read-only, c'est le worker CLI qui produit les fichiers de cache.

### 11.5 Scalabilité du polling 5 s

Estimation : 100 admins simultanés × 1 requête/5 s = 20 req/s sur `/admin/events/worker/status`. Acceptable, requête SQL très rapide (index sur `status`). Pas de cache HTTP nécessaire dans un premier temps.

---

## 12. Plan d'implémentation

### Phase 1 — Backend api2

1. **Créer le contrôleur** `sources/api2/src/Controller/AdminEventWorkerController.php` :
   - Inject `Doctrine\DBAL\Connection $connection`.
   - Méthode privée `enrichConfig(array $row): array` (port de `api_worker.php:291-321`).
   - Méthode privée `fetchActiveConfigs(): array` (port de `api_worker.php:268-286`).
2. **Implémenter les 9 endpoints** §3.1 → §3.8 dans l'ordre :
   - `status` (GET)
   - `start` (POST + body JSON)
   - `stop` × 2 (avec et sans `idEvent`)
   - `pause`, `resume` (POST par `idEvent`)
   - `update` → `PATCH /{idEvent}`
   - `dates` (GET)
   - `monitor` (GET) — **port natif** de `CacheMatch::Event()` + `GetBestMatch` + `GetNextMatch`, sans écriture de fichiers.
3. **Tester via Swagger** (URL `/api2/api`) avec un JWT admin valide.

### Phase 2 — Frontend Nuxt

4. **Créer le type** `sources/app4/types/eventWorker.ts` (cf. §5.4).
5. **Créer la page** `sources/app4/pages/live/cache-manager.vue` (cf. §5.2).
6. **Ajouter les clés i18n** dans `sources/app4/i18n/locales/fr.json` et `en.json` (cf. §7).
7. **Ajouter le menu** dans `sources/app4/components/admin/Header.vue` (cf. §8).

### Phase 3 — Validation

8. **Tests manuels** end-to-end (cf. §13).
9. **Vérifier compatibilité worker** : démarrer une config via la nouvelle page, vérifier que le CLI `event_worker.php` la détecte et génère bien les caches.

### Phase 4 — Cleanup soft (legacy conservé)

10. **Mettre à jour `pages/tv/index.vue`** : remplacer le lien "Event cache generator" (`pages/tv/index.vue:226-232`) par un `NuxtLink to="/live/cache-manager"`.
11. **Marquer `@deprecated`** les 4 fichiers legacy (cf. §10.2).
12. **Mettre à jour `sources/live/EVENT_WORKER_README.md`** (cf. §10.3).

---

## 13. Vérification end-to-end

### 13.1 Pré-requis

- Worker CLI lancé : `cd sources/live && php event_worker.php > logs/event_worker.log 2>&1 &`
- DB MariaDB avec table `kp_event_worker_config`.
- API2 Symfony en marche.
- App4 Nuxt en marche (`make app4_dev` ou équivalent).
- Compte admin (profil ≤ 2).

### 13.2 Scénario de test

1. **Login** sur app4 avec un compte admin.
2. **Navigation** : vérifier le nouveau lien dans le menu **Administration → Live → Cache d'événement** (icône `server-stack`).
3. **Liste vide** : la page `/live/cache-manager` s'affiche, section "Workers actifs" vide.
4. **Sélectionner un événement** dans le `USelect`. Les boutons "date rapide" doivent s'afficher.
5. **Cliquer un bouton date rapide** : `dateEvent` et `hourEvent` doivent se pré-remplir.
6. **Démarrer** : cliquer **Démarrer le worker**. Toast vert "Worker démarré".
7. **Vérification BDD** :
   ```sql
   SELECT id_event, date_event, hour_event, status, execution_count, last_execution
   FROM kp_event_worker_config WHERE status='running';
   ```
   → 1 ligne avec status=`running`.
8. **Polling status** : section "Workers actifs" se rafraîchit toutes les 5s. `executionCount` augmente, `currentSimulatedTime` avance, `lastExecution` est récent.
9. **Healthy badge** : badge vert "Sain" tant que `secondsSinceLastExecution < delay × 3`.
10. **Monitor modal** : cliquer **Monitor**. Modal s'ouvre, table affiche les pitches avec leur match courant + suivant. Refresh automatique toutes les `delayEvent` secondes.
11. **Pause/Resume** : cliquer **Pause** → status passe à `paused` en DB. Cliquer **Reprendre** → retour à `running`.
12. **Stop** : cliquer **Arrêter** → modal de confirmation → status=`stopped` en DB → la config disparaît de la liste.
13. **Fermer la page** : aucun `setInterval` ne survit (pas de log d'erreur après navigation).
14. **Vérification cache fichiers** : pendant que le worker tourne, vérifier que `sources/live/cache/event{id}_pitch1.json` etc. sont mis à jour (`ls -la --time=ctime sources/live/cache/`).
15. **Stop All** : démarrer 2 configs (2 events différents), cliquer **Tout arrêter** → confirmation → 0 worker actif.

### 13.3 Vérification non-régression

- `sources/live/event.php` reste fonctionnel pour les utilisateurs encore en session legacy (et marqué `@deprecated`).
- Les overlays TV (`live/tv2.php?show=score&pitch=1...`) continuent à recevoir les caches mis à jour par le worker CLI, indépendamment de l'UI utilisée pour le contrôler.
- Les pages legacy `GestionOperations.tpl` et `kptv.tpl` continuent à pointer vers `live/event.php` sans erreur.

---

## 14. Critical Files

### À créer
- `sources/api2/src/Controller/AdminEventWorkerController.php` (~350 lignes)
- `sources/app4/pages/live/cache-manager.vue` (~250 lignes)
- `sources/app4/types/eventWorker.ts` (~60 lignes)

### À modifier
- `sources/app4/components/admin/Header.vue` (+5 lignes, menu)
- `sources/app4/i18n/locales/fr.json` (+1 clé menu + bloc `eventCacheManager`)
- `sources/app4/i18n/locales/en.json` (idem)
- `sources/app4/pages/tv/index.vue` (lien legacy → NuxtLink)
- `sources/live/event.php` (en-tête `@deprecated`)
- `sources/live/api_worker.php` (en-tête `@deprecated`)
- `sources/live/ajax_cache_event.php` (en-tête `@deprecated`)
- `sources/live/js/event.js` (en-tête `@deprecated`)
- `sources/live/EVENT_WORKER_README.md` (note dépréciation + lien vers nouvelle page)

### Référence (lecture seule)
- `sources/live/event_worker.php` (worker CLI inchangé)
- `sources/live/api_worker.php` (référence pour port d'`enrichConfig()`)
- `sources/live/create_cache_match.php` (méthode `Event()` à porter pour /monitor)
- `sources/api2/src/Controller/AdminTvController.php` (modèle de contrôleur)
- `sources/app4/pages/tv/index.vue` (modèle de page admin)
- `SQL/20251111_create_event_worker_config.sql` (schema)

---

**Version** : 1.0
**Date** : 2026-04-26
**Auteur** : Spec rédigée par Claude (Opus 4.7) sur demande de @lgarrigue
