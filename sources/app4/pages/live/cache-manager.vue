<script setup lang="ts">
import { useAuthStore } from '~/stores/authStore'
import type { WorkerConfig, WorkerEvent, WorkerDate, WorkerMonitor, WorkerForm } from '~/types/eventWorker'

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
const monitorLastUpdate = ref<string>('')
const confirmStopOpen = ref(false)
const confirmStopAll = ref(false)
const confirmStopIdEvent = ref<number | null>(null)

let statusTimer: ReturnType<typeof setInterval> | null = null
let monitorTimer: ReturnType<typeof setInterval> | null = null

// ─── Computed ───
const hasRunning = computed(() => configs.value.some(c => c.isRunning))

function eventLabel(idEvent: number): string {
  const e = events.value.find(e => e.id === idEvent)
  return e ? e.libelle : ''
}

// ─── API calls ───
async function loadEvents() {
  try { events.value = await api.get<WorkerEvent[]>('/admin/tv/events') } catch {}
}

async function loadDates(idEvent: number) {
  try { dates.value = await api.get<WorkerDate[]>(`/admin/events/worker/${idEvent}/dates`) } catch {}
}

async function loadStatus() {
  try { configs.value = await api.get<WorkerConfig[]>('/admin/events/worker/status') } catch {}
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

async function pauseWorker(idEvent: number) {
  await api.post(`/admin/events/worker/${idEvent}/pause`)
  toast.add({ title: t('eventCacheManager.toasts.paused'), color: 'info' })
  await loadStatus()
}

async function resumeWorker(idEvent: number) {
  await api.post(`/admin/events/worker/${idEvent}/resume`)
  toast.add({ title: t('eventCacheManager.toasts.resumed'), color: 'success' })
  await loadStatus()
}

function askStopWorker(idEvent: number) {
  confirmStopIdEvent.value = idEvent
  confirmStopAll.value = false
  confirmStopOpen.value = true
}

function askStopAll() {
  confirmStopAll.value = true
  confirmStopIdEvent.value = null
  confirmStopOpen.value = true
}

async function confirmStop() {
  confirmStopOpen.value = false
  if (confirmStopAll.value) {
    await api.post('/admin/events/worker/stop')
    toast.add({ title: t('eventCacheManager.toasts.stop_all_done'), color: 'success' })
  } else if (confirmStopIdEvent.value) {
    await api.post(`/admin/events/worker/${confirmStopIdEvent.value}/stop`)
    toast.add({ title: t('eventCacheManager.toasts.stopped'), color: 'success' })
  }
  await loadStatus()
}

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
  try {
    monitorData.value = await api.get<WorkerMonitor>(
      `/admin/events/worker/${monitorConfig.value.idEvent}/monitor`,
      {
        dateEvent: monitorConfig.value.dateEvent,
        hourEvent: monitorConfig.value.currentSimulatedTime.slice(0, 5),
        offsetEvent: monitorConfig.value.offsetEvent,
        pitchEvent: monitorConfig.value.pitchEvent,
      }
    )
    monitorLastUpdate.value = new Date().toLocaleTimeString()
  } catch {}
}

function closeMonitor() {
  monitorOpen.value = false
  monitorConfig.value = null
  monitorData.value = null
  if (monitorTimer) { clearInterval(monitorTimer); monitorTimer = null }
}

function setQuickDate(d: WorkerDate) {
  form.value.dateEvent = d.dateMatch
  form.value.hourEvent = d.heureMatch.slice(0, 5)
}

function statusBadgeClass(c: WorkerConfig): string {
  if (c.isRunning) return 'bg-green-100 text-green-700'
  if (c.isPaused) return 'bg-yellow-100 text-yellow-700'
  return 'bg-header-100 text-header-500'
}

// ─── Watchers ───
watch(() => form.value.idEvent, async (id) => {
  dates.value = []
  if (id) await loadDates(id)
})

// ─── Lifecycle ───
onMounted(async () => {
  const now = new Date()
  form.value.hourEvent = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
  await Promise.all([loadEvents(), loadStatus()])
  statusTimer = setInterval(loadStatus, 5000)
})

onBeforeUnmount(() => {
  if (statusTimer) clearInterval(statusTimer)
  if (monitorTimer) clearInterval(monitorTimer)
})
</script>

<template>
  <div class="px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-header-900">{{ t('eventCacheManager.title') }}</h1>
      <p class="mt-1 text-sm text-header-500">{{ t('eventCacheManager.subtitle') }}</p>
    </div>

    <!-- Active workers -->
    <div class="bg-white border border-header-200 rounded-xl mb-6">
      <div class="flex items-center justify-between px-5 py-4 border-b border-header-200">
        <h2 class="text-base font-semibold text-header-900">{{ t('eventCacheManager.active_workers.title') }}</h2>
        <button
          v-if="hasRunning"
          type="button"
          class="px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors flex items-center gap-1"
          @click="askStopAll"
        >
          <UIcon name="heroicons:stop-circle" class="w-4 h-4" />
          {{ t('eventCacheManager.active_workers.stop_all') }}
        </button>
      </div>

      <div v-if="configs.length === 0" class="px-5 py-10 text-center text-header-400">
        {{ t('eventCacheManager.active_workers.none') }}
      </div>

      <div v-else class="divide-y divide-header-100">
        <div v-for="c in configs" :key="c.id" class="px-5 py-4 flex flex-wrap items-start justify-between gap-4">
          <!-- Info -->
          <div class="space-y-2">
            <div class="flex flex-wrap items-center gap-2">
              <span class="font-semibold text-header-900">Event #{{ c.idEvent }}</span>
              <span v-if="eventLabel(c.idEvent)" class="text-sm text-header-500">{{ eventLabel(c.idEvent) }}</span>
              <span :class="['text-xs font-medium px-2 py-0.5 rounded-full', statusBadgeClass(c)]">
                {{ t(`eventCacheManager.status.${c.status}`) }}
              </span>
              <span :class="['text-xs font-medium px-2 py-0.5 rounded-full flex items-center gap-1', c.isHealthy ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700']">
                <UIcon :name="c.isHealthy ? 'heroicons:check-circle' : 'heroicons:exclamation-triangle'" class="w-3 h-3" />
                {{ c.isHealthy ? t('eventCacheManager.active_workers.healthy') : t('eventCacheManager.active_workers.unhealthy') }}
              </span>
            </div>
            <div class="grid grid-cols-2 gap-x-6 gap-y-1 text-sm sm:grid-cols-4">
              <div><span class="text-header-400">{{ t('eventCacheManager.active_workers.date') }} : </span><span class="text-header-700">{{ c.dateEvent }}</span></div>
              <div><span class="text-header-400">{{ t('eventCacheManager.active_workers.initial_time') }} : </span><span class="text-header-700">{{ c.hourEventInitial.slice(0, 5) }}</span></div>
              <div><span class="text-header-400">{{ t('eventCacheManager.active_workers.current_time') }} : </span><span class="font-medium text-primary-600">{{ c.currentSimulatedTime.slice(0, 5) }}</span></div>
              <div><span class="text-header-400">{{ t('eventCacheManager.active_workers.pitches') }} : </span><span class="text-header-700">{{ c.pitchEvent }}</span></div>
              <div><span class="text-header-400">{{ t('eventCacheManager.active_workers.warmup') }} : </span><span class="text-header-700">{{ c.offsetEvent }} min</span></div>
              <div><span class="text-header-400">{{ t('eventCacheManager.active_workers.delay') }} : </span><span class="text-header-700">{{ c.delayEvent }}s</span></div>
              <div><span class="text-header-400">{{ t('eventCacheManager.active_workers.executions') }} : </span><span class="text-header-700">{{ c.executionCount }}</span></div>
              <div v-if="c.lastExecution"><span class="text-header-400">{{ t('eventCacheManager.active_workers.last_execution') }} : </span><span class="text-header-700">{{ c.lastExecution.slice(11, 19) }}</span></div>
            </div>
          </div>

          <!-- Actions -->
          <div class="flex flex-wrap gap-2">
            <button type="button" class="px-3 py-1.5 text-sm font-medium text-primary-600 bg-primary-50 rounded-lg hover:bg-primary-100 transition-colors flex items-center gap-1" @click="openMonitor(c)">
              <UIcon name="heroicons:magnifying-glass" class="w-4 h-4" />
              {{ t('eventCacheManager.active_workers.monitor') }}
            </button>
            <button v-if="c.isRunning" type="button" class="px-3 py-1.5 text-sm font-medium text-yellow-600 bg-yellow-50 rounded-lg hover:bg-yellow-100 transition-colors flex items-center gap-1" @click="pauseWorker(c.idEvent)">
              <UIcon name="heroicons:pause" class="w-4 h-4" />
              {{ t('eventCacheManager.active_workers.pause') }}
            </button>
            <button v-if="c.isPaused" type="button" class="px-3 py-1.5 text-sm font-medium text-green-600 bg-green-50 rounded-lg hover:bg-green-100 transition-colors flex items-center gap-1" @click="resumeWorker(c.idEvent)">
              <UIcon name="heroicons:play" class="w-4 h-4" />
              {{ t('eventCacheManager.active_workers.resume') }}
            </button>
            <button type="button" class="px-3 py-1.5 text-sm font-medium text-red-600 bg-red-50 rounded-lg hover:bg-red-100 transition-colors flex items-center gap-1" @click="askStopWorker(c.idEvent)">
              <UIcon name="heroicons:x-circle" class="w-4 h-4" />
              {{ t('eventCacheManager.active_workers.stop') }}
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- New configuration form -->
    <div class="bg-white border border-header-200 rounded-xl">
      <div class="px-5 py-4 border-b border-header-200">
        <h2 class="text-base font-semibold text-header-900">{{ t('eventCacheManager.form.title') }}</h2>
      </div>

      <div class="px-5 py-4 space-y-4">
        <!-- Event select -->
        <div>
          <label class="block text-xs font-medium text-header-500 mb-1">{{ t('eventCacheManager.form.event') }}</label>
          <select
            v-model="form.idEvent"
            class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
          >
            <option :value="null">{{ t('eventCacheManager.form.event_placeholder') }}</option>
            <option v-for="e in events" :key="e.id" :value="e.id">#{{ e.id }} — {{ e.libelle }} — {{ e.lieu }}</option>
          </select>
        </div>

        <!-- Quick date buttons -->
        <div v-if="dates.length > 0">
          <p class="mb-2 text-xs font-medium text-header-500">{{ t('eventCacheManager.form.quick_date') }}</p>
          <div class="flex flex-wrap gap-2">
            <button
              v-for="d in dates"
              :key="d.dateMatch"
              type="button"
              class="px-2 py-1 text-xs font-medium text-primary-600 bg-primary-50 border border-primary-200 rounded hover:bg-primary-100 transition-colors"
              @click="setQuickDate(d)"
            >
              {{ d.dateMatch }} ({{ d.heureMatch.slice(0, 5) }})
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
          <div>
            <label class="block text-xs font-medium text-header-500 mb-1">{{ t('eventCacheManager.form.date') }}</label>
            <input v-model="form.dateEvent" type="date" class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
          </div>
          <div>
            <label class="block text-xs font-medium text-header-500 mb-1">{{ t('eventCacheManager.form.hour') }}</label>
            <input v-model="form.hourEvent" type="time" class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
            <span class="text-xs text-header-400 mt-0.5 block">{{ t('eventCacheManager.form.hour_help') }}</span>
          </div>
          <div>
            <label class="block text-xs font-medium text-header-500 mb-1">{{ t('eventCacheManager.form.offset') }}</label>
            <input v-model.number="form.offsetEvent" type="number" min="0" max="120" class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
          </div>
          <div>
            <label class="block text-xs font-medium text-header-500 mb-1">{{ t('eventCacheManager.form.pitch') }}</label>
            <input v-model.number="form.pitchEvent" type="number" min="1" max="20" class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
          </div>
          <div>
            <label class="block text-xs font-medium text-header-500 mb-1">{{ t('eventCacheManager.form.delay') }}</label>
            <input v-model.number="form.delayEvent" type="number" min="5" max="60" class="w-full px-3 py-2 text-sm border border-header-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500" />
          </div>
        </div>

        <div class="pt-2">
          <button
            type="button"
            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2"
            @click="startWorker"
          >
            <UIcon name="heroicons:play-circle" class="w-4 h-4" />
            {{ t('eventCacheManager.form.start') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Stop confirmation modal -->
    <UModal v-model:open="confirmStopOpen">
      <template #content>
        <div class="bg-white rounded-xl p-6">
          <h3 class="text-base font-semibold text-header-900 mb-4">
            {{ confirmStopAll ? t('eventCacheManager.confirm.stop_all') : t('eventCacheManager.confirm.stop_one', { id: confirmStopIdEvent }) }}
          </h3>
          <div class="flex justify-end gap-3">
            <button type="button" class="px-4 py-2 text-sm font-medium text-header-700 bg-header-100 rounded-lg hover:bg-header-200 transition-colors" @click="confirmStopOpen = false">
              {{ t('common.cancel') }}
            </button>
            <button type="button" class="px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700 transition-colors" @click="confirmStop">
              {{ confirmStopAll ? t('eventCacheManager.active_workers.stop_all') : t('eventCacheManager.active_workers.stop') }}
            </button>
          </div>
        </div>
      </template>
    </UModal>

    <!-- Monitor modal -->
    <UModal v-model:open="monitorOpen" size="xl" @close="closeMonitor">
      <template #content>
        <div v-if="monitorConfig" class="bg-white rounded-xl">
          <!-- Modal header -->
          <div class="flex items-start justify-between px-6 py-4 border-b border-header-200">
            <div>
              <h3 class="text-base font-semibold text-header-900">
                {{ t('eventCacheManager.monitor.title') }} — {{ t('eventCacheManager.monitor.event') }} #{{ monitorConfig.idEvent }}
              </h3>
              <p class="mt-0.5 text-sm text-header-500">
                {{ t('eventCacheManager.monitor.date') }} {{ monitorConfig.dateEvent }} ·
                {{ t('eventCacheManager.monitor.initial_time') }} {{ monitorConfig.hourEventInitial.slice(0, 5) }} ·
                {{ t('eventCacheManager.monitor.refresh_every', { seconds: monitorConfig.delayEvent }) }}
              </p>
            </div>
            <button type="button" class="p-1 rounded hover:bg-header-100 text-header-500 hover:text-header-700 transition-colors" @click="closeMonitor">
              <UIcon name="heroicons:x-mark" class="w-5 h-5" />
            </button>
          </div>

          <!-- Monitor content -->
          <div class="px-6 py-4">
            <div v-if="monitorData">
              <table class="w-full text-sm">
                <thead>
                  <tr class="border-b border-header-200 text-left">
                    <th class="pb-2 pr-4 font-semibold text-header-600">{{ t('eventCacheManager.monitor.pitch') }}</th>
                    <th class="pb-2 pr-4 font-semibold text-header-600">{{ t('eventCacheManager.monitor.current_game') }}</th>
                    <th class="pb-2 font-semibold text-header-600">{{ t('eventCacheManager.monitor.next_game') }}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="p in monitorData.pitches" :key="p.pitch" class="border-b border-header-100">
                    <td class="py-2.5 pr-4 font-semibold text-header-800">{{ p.pitch }}</td>
                    <td class="py-2.5 pr-4">
                      <span v-if="p.game" class="font-medium text-header-800">
                        {{ p.time?.slice(0, 5) }} · #{{ p.num }} · ID {{ p.game }}
                      </span>
                      <span v-else class="italic text-header-400">{{ t('eventCacheManager.monitor.waiting') }}</span>
                    </td>
                    <td class="py-2.5">
                      <span v-if="p.next.id" class="text-header-600">
                        {{ p.next.time?.slice(0, 5) }} · #{{ p.next.num }} · ID {{ p.next.id }}
                      </span>
                      <span v-else class="italic text-header-400">{{ t('eventCacheManager.monitor.waiting') }}</span>
                    </td>
                  </tr>
                </tbody>
              </table>
              <div class="mt-3 flex items-center justify-between text-xs text-header-400 pt-2 border-t border-header-100">
                <span>
                  {{ t('eventCacheManager.monitor.time_current') }} {{ monitorData.time.currentTime }} ·
                  {{ t('eventCacheManager.monitor.time_working') }} {{ monitorData.time.workingTime }}
                </span>
                <span v-if="monitorLastUpdate">{{ t('eventCacheManager.monitor.last_update', { time: monitorLastUpdate }) }}</span>
              </div>
            </div>
            <div v-else class="py-6 text-center text-header-400">
              {{ t('eventCacheManager.monitor.load_failed') }}
            </div>
          </div>
        </div>
      </template>
    </UModal>
  </div>
</template>
