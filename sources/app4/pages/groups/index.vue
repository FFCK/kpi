<script setup lang="ts">
interface Group {
  id: number
  section: number
  sectionName: string
  ordre: number
  codeNiveau: string
  groupe: string
  libelle: string
  libelleEn: string
  competitionCount: number
  distinctCodeCount: number
}

interface GroupFormData {
  section: number
  ordre: number
  codeNiveau: string
  groupe: string
  libelle: string
  libelleEn: string
}

interface GroupsBySection {
  section: number
  sectionName: string
  groups: Group[]
}

definePageMeta({
  layout: 'admin',
  middleware: 'auth'
})

const { t } = useI18n()
const api = useApi()
const authStore = useAuthStore()

// State
const loading = ref(false)
const groups = ref<Group[]>([])
const search = ref('')

// Accordion state: collapsed sections (all expanded by default)
const collapsedSections = ref<Set<number>>(new Set())

// Modal state
const isModalOpen = ref(false)
const editingGroup = ref<Group | null>(null)
const formData = ref<GroupFormData>({
  section: 2,
  ordre: 1,
  codeNiveau: 'NAT',
  groupe: '',
  libelle: '',
  libelleEn: ''
})
const formError = ref('')

// Code change warning
const showCodeChangeWarning = ref(false)

// Delete confirmation modal
const deleteModalOpen = ref(false)
const groupToDelete = ref<Group | null>(null)
const isDeleting = ref(false)
const deleteError = ref('')

// Toast notifications
const toast = useToast()

// Sections for filter and form
const sections = [
  { value: 1, label: () => t('groups.sections.1') },
  { value: 2, label: () => t('groups.sections.2') },
  { value: 3, label: () => t('groups.sections.3') },
  { value: 4, label: () => t('groups.sections.4') },
  { value: 5, label: () => t('groups.sections.5') },
  { value: 100, label: () => t('groups.sections.100') }
]

const niveaux = ['REG', 'NAT', 'INT']

const toggleSection = (sectionId: number) => {
  const newSet = new Set(collapsedSections.value)
  if (newSet.has(sectionId)) {
    newSet.delete(sectionId)
  } else {
    newSet.add(sectionId)
  }
  collapsedSections.value = newSet
}

const isSectionCollapsed = (sectionId: number) => collapsedSections.value.has(sectionId)

const expandAll = () => {
  collapsedSections.value = new Set()
}

const collapseAll = () => {
  collapsedSections.value = new Set(groupsBySection.value.map(s => s.section))
}

// Groups organized by section
const groupsBySection = computed<GroupsBySection[]>(() => {
  const filtered = groups.value.filter(g => {
    if (search.value) {
      const s = search.value.toLowerCase()
      return g.groupe.toLowerCase().includes(s) ||
             g.libelle.toLowerCase().includes(s) ||
             (g.libelleEn && g.libelleEn.toLowerCase().includes(s))
    }
    return true
  })

  const map = new Map<number, GroupsBySection>()
  for (const g of filtered) {
    if (!map.has(g.section)) {
      map.set(g.section, { section: g.section, sectionName: g.sectionName, groups: [] })
    }
    map.get(g.section)!.groups.push(g)
  }

  // Sort by section: 1, 2, 3, 4, 5, 100
  return Array.from(map.values()).sort((a, b) => {
    if (a.section === 100) return 1
    if (b.section === 100) return -1
    return a.section - b.section
  })
})

const totalGroups = computed(() => {
  return groupsBySection.value.reduce((sum, s) => sum + s.groups.length, 0)
})

// Load groups
const loadGroups = async () => {
  loading.value = true
  try {
    const response = await api.get<{ items: Group[], total: number }>('/admin/groups')
    groups.value = response.items
  } catch (error: unknown) {
    const message = (error as { message?: string })?.message || t('groups.error_load')
    toast.add({
      title: t('common.error'),
      description: message,
      color: 'error',
      duration: 3000
    })
  } finally {
    loading.value = false
  }
}

// Load on mount
onMounted(() => {
  loadGroups()
})

// Modal handlers
const openAddModal = () => {
  editingGroup.value = null
  // Pre-fill ordre with max+1 for default section
  const sectionGroups = groups.value.filter(g => g.section === 2)
  const maxOrdre = sectionGroups.length > 0 ? Math.max(...sectionGroups.map(g => g.ordre)) : 0
  formData.value = {
    section: 2,
    ordre: maxOrdre + 1,
    codeNiveau: 'NAT',
    groupe: '',
    libelle: '',
    libelleEn: ''
  }
  formError.value = ''
  showCodeChangeWarning.value = false
  isModalOpen.value = true
}

const openEditModal = (group: Group) => {
  editingGroup.value = group
  formData.value = {
    section: group.section,
    ordre: group.ordre,
    codeNiveau: group.codeNiveau,
    groupe: group.groupe,
    libelle: group.libelle,
    libelleEn: group.libelleEn || ''
  }
  formError.value = ''
  showCodeChangeWarning.value = false
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  editingGroup.value = null
  formError.value = ''
  showCodeChangeWarning.value = false
}

// Update max ordre when section changes in form
const onSectionChange = () => {
  const sectionGroups = groups.value.filter(g => g.section === formData.value.section)
  const maxOrdre = sectionGroups.length > 0 ? Math.max(...sectionGroups.map(g => g.ordre)) : 0
  if (!editingGroup.value) {
    formData.value.ordre = maxOrdre + 1
  }
}

// Watch for group code changes in edit mode
const onGroupeInput = () => {
  if (editingGroup.value && formData.value.groupe !== editingGroup.value.groupe) {
    showCodeChangeWarning.value = true
  } else {
    showCodeChangeWarning.value = false
  }
}

// Save group (create or update)
const saveGroup = async () => {
  formError.value = ''

  // Client-side validation
  if (!formData.value.groupe.trim()) {
    formError.value = t('groups.form.groupe') + ' ' + t('common.error').toLowerCase()
    return
  }
  if (!formData.value.libelle.trim()) {
    formError.value = t('groups.form.libelle') + ' ' + t('common.error').toLowerCase()
    return
  }

  // Check ordre uniqueness within section
  const duplicate = groups.value.find(g =>
    g.section === formData.value.section &&
    g.ordre === formData.value.ordre &&
    (!editingGroup.value || g.id !== editingGroup.value.id)
  )
  if (duplicate) {
    formError.value = t('groups.error_ordre_duplicate', { ordre: formData.value.ordre })
    return
  }

  loading.value = true
  try {
    if (editingGroup.value) {
      await api.put(`/admin/groups/${editingGroup.value.id}`, formData.value)
      toast.add({
        title: t('common.success'),
        description: t('groups.success_updated'),
        color: 'success',
        duration: 3000
      })
    } else {
      await api.post('/admin/groups', formData.value)
      toast.add({
        title: t('common.success'),
        description: t('groups.success_created'),
        color: 'success',
        duration: 3000
      })
    }
    closeModal()
    loadGroups()
  } catch (error: unknown) {
    formError.value = (error as { message?: string })?.message || t('groups.error_save')
  } finally {
    loading.value = false
  }
}

// Delete handlers
const openDeleteModal = (group: Group) => {
  groupToDelete.value = group
  deleteError.value = ''
  deleteModalOpen.value = true
}

const confirmDelete = async () => {
  if (!groupToDelete.value) return

  isDeleting.value = true
  deleteError.value = ''
  try {
    await api.del(`/admin/groups/${groupToDelete.value.id}`)
    toast.add({
      title: t('common.success'),
      description: t('groups.success_deleted'),
      color: 'success',
      duration: 3000
    })
    deleteModalOpen.value = false
    groupToDelete.value = null
    loadGroups()
  } catch (error: unknown) {
    deleteError.value = (error as { message?: string })?.message || t('groups.error_delete')
  } finally {
    isDeleting.value = false
  }
}

// Reorder
const reorder = async (group: Group, direction: 'up' | 'down') => {
  try {
    await api.patch(`/admin/groups/${group.id}/reorder`, { direction })
    toast.add({
      title: t('common.success'),
      description: t('groups.success_reordered'),
      color: 'success',
      duration: 2000
    })
    loadGroups()
  } catch (error: unknown) {
    toast.add({
      title: t('common.error'),
      description: (error as { message?: string })?.message || t('common.error'),
      color: 'error',
      duration: 3000
    })
  }
}

const isFirstInSection = (group: Group, sectionGroups: Group[]) => {
  return sectionGroups[0]?.id === group.id
}

const isLastInSection = (group: Group, sectionGroups: Group[]) => {
  return sectionGroups[sectionGroups.length - 1]?.id === group.id
}
</script>

<template>
  <div>
    <!-- Page header -->
    <div class="mb-6">
      <h1 class="text-2xl font-bold text-gray-900">
        {{ t('groups.title') }}
      </h1>
    </div>

    <!-- Toolbar -->
    <AdminToolbar
      v-model:search="search"
      :search-placeholder="t('common.search')"
      :add-label="t('groups.add')"
      @add="openAddModal"
    >
      <template v-if="groupsBySection.length > 1" #left>
        <button
          class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors disabled:opacity-40 disabled:cursor-default"
          :disabled="collapsedSections.size === groupsBySection.length"
          @click="collapseAll"
        >
          <UIcon name="heroicons:chevron-double-up" class="w-3.5 h-3.5" />
          {{ t('common.collapse_all') }}
        </button>
        <button
          class="flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-gray-600 hover:text-gray-900 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors disabled:opacity-40 disabled:cursor-default"
          :disabled="collapsedSections.size === 0"
          @click="expandAll"
        >
          <UIcon name="heroicons:chevron-double-down" class="w-3.5 h-3.5" />
          {{ t('common.expand_all') }}
        </button>
      </template>
    </AdminToolbar>

    <!-- Desktop Table -->
    <div class="hidden lg:block bg-white rounded-lg shadow overflow-hidden">
      <!-- Loading state -->
      <div v-if="loading && groups.length === 0" class="px-4 py-8 text-center text-gray-500">
        <UIcon name="heroicons:arrow-path" class="w-6 h-6 animate-spin mx-auto mb-2" />
        {{ t('common.loading') }}
      </div>

      <!-- Empty state -->
      <div v-else-if="groupsBySection.length === 0" class="px-4 py-8 text-center text-gray-500">
        {{ t('groups.empty') }}
      </div>

      <!-- Groups by section -->
      <div v-else>
        <div v-for="section in groupsBySection" :key="section.section" class="border-b border-gray-200 last:border-b-0">
          <!-- Section header (accordion toggle) -->
          <button
            class="w-full bg-gray-100 hover:bg-gray-200 px-4 py-2 flex items-center gap-2 transition-colors cursor-pointer"
            @click="toggleSection(section.section)"
          >
            <UIcon
              name="heroicons:chevron-right"
              class="w-4 h-4 text-gray-500 transition-transform"
              :class="{ 'rotate-90': !isSectionCollapsed(section.section) }"
            />
            <span class="text-sm font-semibold text-gray-700">{{ section.sectionName }}</span>
            <span class="text-xs text-gray-500">({{ section.groups.length }})</span>
          </button>

          <!-- Table for this section -->
          <table v-show="!isSectionCollapsed(section.section)" class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-20">
                  {{ t('groups.columns.ordre') }}
                </th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-24">
                  {{ t('groups.columns.niveau') }}
                </th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-28">
                  {{ t('groups.columns.code') }}
                </th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('groups.columns.libelle') }}
                </th>
                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  {{ t('groups.columns.libelle_en') }}
                </th>
                <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase tracking-wider w-24" :title="t('groups.columns.codes_total_hint')">
                  {{ t('groups.columns.competitions') }}
                </th>
                <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase tracking-wider w-36">
                  {{ t('groups.columns.actions') }}
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="group in section.groups"
                :key="group.id"
                class="hover:bg-gray-50 cursor-pointer"
                @click="openEditModal(group)"
              >
                <!-- Ordre with reorder buttons -->
                <td class="px-4 py-2 text-sm text-gray-900" @click.stop>
                  <div class="flex items-center gap-1">
                    <button
                      v-if="!isFirstInSection(group, section.groups)"
                      class="p-0.5 text-gray-400 hover:text-blue-600"
                      :title="'Move up'"
                      @click="reorder(group, 'up')"
                    >
                      <UIcon name="heroicons:chevron-up" class="w-4 h-4" />
                    </button>
                    <span v-else class="w-5" />
                    <button
                      v-if="!isLastInSection(group, section.groups)"
                      class="p-0.5 text-gray-400 hover:text-blue-600"
                      :title="'Move down'"
                      @click="reorder(group, 'down')"
                    >
                      <UIcon name="heroicons:chevron-down" class="w-4 h-4" />
                    </button>
                    <span v-else class="w-5" />
                    <span class="text-gray-500 text-xs ml-1">{{ group.ordre }}</span>
                  </div>
                </td>

                <!-- Niveau -->
                <td class="px-4 py-2 text-sm">
                  <span
                    :class="{
                      'text-blue-700 bg-blue-100': group.codeNiveau === 'INT',
                      'text-green-700 bg-green-100': group.codeNiveau === 'NAT',
                      'text-amber-700 bg-amber-100': group.codeNiveau === 'REG'
                    }"
                    class="px-2 py-0.5 rounded-full text-xs font-medium"
                  >
                    {{ group.codeNiveau }}
                  </span>
                </td>

                <!-- Code -->
                <td class="px-4 py-2 text-sm font-mono font-semibold text-gray-900">
                  {{ group.groupe }}
                </td>

                <!-- Libelle FR -->
                <td class="px-4 py-2 text-sm text-gray-900">
                  {{ group.libelle }}
                </td>

                <!-- Libelle EN -->
                <td class="px-4 py-2 text-sm text-gray-500">
                  {{ group.libelleEn || '-' }}
                </td>

                <!-- Competition count -->
                <td class="px-4 py-2 text-sm text-center">
                  <span
                    v-if="group.competitionCount > 0"
                    class="inline-flex items-center gap-1 text-xs text-gray-700"
                    :title="`${group.distinctCodeCount} code(s), ${group.competitionCount} compétition(s)`"
                  >
                    <span class="px-1.5 py-0.5 rounded-full bg-gray-100 font-medium">{{ group.distinctCodeCount }}</span>
                    <span class="text-gray-400">/</span>
                    <span class="text-gray-500">{{ group.competitionCount }}</span>
                  </span>
                  <span v-else class="text-gray-300">0</span>
                </td>

                <!-- Actions -->
                <td class="px-4 py-2" @click.stop>
                  <div class="flex items-center justify-end gap-1">
                    <button
                      class="p-1.5 text-blue-600"
                      :title="t('common.edit')"
                      @click="openEditModal(group)"
                    >
                      <UIcon name="heroicons:pencil-solid" class="w-6 h-6" />
                    </button>
                    <button
                      v-if="authStore.isSuperAdmin && group.competitionCount === 0"
                      class="p-1.5 text-red-600"
                      :title="t('common.delete')"
                      @click="openDeleteModal(group)"
                    >
                      <UIcon name="heroicons:trash-solid" class="w-6 h-6" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Total -->
        <div class="px-4 py-3 bg-gray-50 text-sm text-gray-600">
          {{ t('groups.total_groups', { count: totalGroups }) }}
        </div>
      </div>
    </div>

    <!-- Mobile Cards -->
    <AdminCardList
      :loading="loading && groups.length === 0"
      :empty="groupsBySection.length === 0"
      :loading-text="t('common.loading')"
      :empty-text="t('groups.empty')"
    >
      <template v-for="section in groupsBySection" :key="section.section">
        <!-- Section header mobile (accordion toggle) -->
        <button
          class="w-full flex items-center gap-2 px-1 py-2 mt-2 first:mt-0 cursor-pointer"
          @click="toggleSection(section.section)"
        >
          <UIcon
            name="heroicons:chevron-right"
            class="w-4 h-4 text-gray-500 transition-transform"
            :class="{ 'rotate-90': !isSectionCollapsed(section.section) }"
          />
          <span class="text-sm font-semibold text-gray-700">{{ section.sectionName }}</span>
          <span class="text-xs text-gray-500">({{ section.groups.length }})</span>
        </button>

        <template v-if="!isSectionCollapsed(section.section)">
        <AdminCard
          v-for="group in section.groups"
          :key="group.id"
        >
          <!-- Header -->
          <template #header>
            <div class="flex items-center gap-2">
              <span class="font-mono font-semibold text-gray-900">{{ group.groupe }}</span>
              <span
                :class="{
                  'text-blue-700 bg-blue-100': group.codeNiveau === 'INT',
                  'text-green-700 bg-green-100': group.codeNiveau === 'NAT',
                  'text-amber-700 bg-amber-100': group.codeNiveau === 'REG'
                }"
                class="px-2 py-0.5 rounded-full text-xs font-medium"
              >
                {{ group.codeNiveau }}
              </span>
            </div>
          </template>
          <template #header-right>
            <span class="text-xs text-gray-500">{{ t('groups.columns.ordre') }}: {{ group.ordre }}</span>
          </template>

          <!-- Content -->
          <div class="text-sm text-gray-900">{{ group.libelle }}</div>
          <div v-if="group.libelleEn" class="text-sm text-gray-500">{{ group.libelleEn }}</div>
          <div class="text-xs text-gray-400 mt-1">
            {{ t('groups.codes_count', { count: group.distinctCodeCount }) }} / {{ t('groups.competition_count', { count: group.competitionCount }) }}
          </div>

          <!-- Footer left: reorder -->
          <template #footer-left>
            <div class="flex items-center gap-1">
              <button
                v-if="!isFirstInSection(group, section.groups)"
                class="p-1 text-gray-400 hover:text-blue-600 border border-gray-200 rounded"
                @click="reorder(group, 'up')"
              >
                <UIcon name="heroicons:chevron-up" class="w-4 h-4" />
              </button>
              <button
                v-if="!isLastInSection(group, section.groups)"
                class="p-1 text-gray-400 hover:text-blue-600 border border-gray-200 rounded"
                @click="reorder(group, 'down')"
              >
                <UIcon name="heroicons:chevron-down" class="w-4 h-4" />
              </button>
            </div>
          </template>

          <!-- Footer right: actions -->
          <template #footer-right>
            <AdminActionButton
              icon="heroicons:pencil-solid"
              @click="openEditModal(group)"
            >
              {{ t('common.edit') }}
            </AdminActionButton>
            <AdminActionButton
              v-if="authStore.isSuperAdmin && group.competitionCount === 0"
              variant="danger"
              icon="heroicons:trash-solid"
              @click="openDeleteModal(group)"
            >
              {{ t('common.delete') }}
            </AdminActionButton>
          </template>
        </AdminCard>
        </template>
      </template>

      <!-- Total mobile -->
      <div v-if="groupsBySection.length > 0" class="px-1 py-2 text-sm text-gray-600">
        {{ t('groups.total_groups', { count: totalGroups }) }}
      </div>
    </AdminCardList>

    <!-- Add/Edit Modal -->
    <AdminModal
      :open="isModalOpen"
      :title="editingGroup ? t('groups.form.edit_title') : t('groups.form.add_title')"
      @close="closeModal"
    >
      <form @submit.prevent="saveGroup">
        <div class="space-y-4">
          <!-- Error message -->
          <div
            v-if="formError"
            class="flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800"
          >
            <UIcon name="heroicons:exclamation-triangle" class="w-5 h-5 flex-shrink-0 mt-0.5" />
            <span class="text-sm">{{ formError }}</span>
          </div>

          <!-- Section + Ordre -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('groups.form.section') }} <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formData.section"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                @change="onSectionChange"
              >
                <option v-for="s in sections" :key="s.value" :value="s.value">
                  {{ s.label() }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('groups.form.ordre') }} <span class="text-red-500">*</span>
              </label>
              <input
                v-model.number="formData.ordre"
                type="number"
                min="1"
                max="99999"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              />
            </div>
          </div>

          <!-- Code groupe + Niveau -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('groups.form.groupe') }} <span class="text-red-500">*</span>
              </label>
              <input
                v-model="formData.groupe"
                type="text"
                :placeholder="t('groups.form.groupe_placeholder')"
                maxlength="10"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                @input="onGroupeInput"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                {{ t('groups.form.code_niveau') }} <span class="text-red-500">*</span>
              </label>
              <select
                v-model="formData.codeNiveau"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              >
                <option v-for="n in niveaux" :key="n" :value="n">
                  {{ t(`groups.niveaux.${n}`) }}
                </option>
              </select>
            </div>
          </div>

          <!-- Code change warning -->
          <div
            v-if="showCodeChangeWarning"
            class="flex items-start gap-3 p-4 bg-amber-50 border border-amber-200 rounded-lg text-amber-800"
          >
            <UIcon name="heroicons:exclamation-triangle" class="w-5 h-5 flex-shrink-0 mt-0.5" />
            <span class="text-sm">
              {{ t('groups.code_change_warning', { oldCode: editingGroup?.groupe, newCode: formData.groupe }) }}
            </span>
          </div>

          <!-- Libelle FR -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('groups.form.libelle') }} <span class="text-red-500">*</span>
            </label>
            <input
              v-model="formData.libelle"
              type="text"
              :placeholder="t('groups.form.libelle_placeholder')"
              maxlength="50"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>

          <!-- Libelle EN -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              {{ t('groups.form.libelle_en') }}
            </label>
            <input
              v-model="formData.libelleEn"
              type="text"
              :placeholder="t('groups.form.libelle_en_placeholder')"
              maxlength="255"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
        </div>

        <!-- Footer -->
        <div class="flex justify-end gap-2 mt-6 pt-4 border-t border-gray-200">
          <button
            type="button"
            class="px-4 py-2 text-gray-700 border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors"
            @click="closeModal"
          >
            {{ t('groups.form.cancel') }}
          </button>
          <button
            type="submit"
            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50"
            :disabled="loading"
          >
            <span v-if="loading" class="flex items-center gap-2">
              <UIcon name="heroicons:arrow-path" class="w-4 h-4 animate-spin" />
              {{ t('groups.form.save') }}
            </span>
            <span v-else>{{ t('groups.form.save') }}</span>
          </button>
        </div>
      </form>
    </AdminModal>

    <!-- Delete confirmation modal -->
    <AdminConfirmModal
      :open="deleteModalOpen"
      :title="t('groups.delete')"
      :message="t('groups.confirm_delete')"
      :item-name="groupToDelete ? `${groupToDelete.groupe} - ${groupToDelete.libelle}` : ''"
      :confirm-text="t('common.delete')"
      :cancel-text="t('common.cancel')"
      :loading="isDeleting"
      @close="deleteModalOpen = false; deleteError = ''"
      @confirm="confirmDelete"
    >
      <!-- Show delete error inside modal -->
      <template v-if="deleteError" #default>
        <div class="mt-3 flex items-start gap-3 p-4 bg-red-50 border border-red-200 rounded-lg text-red-800">
          <UIcon name="heroicons:exclamation-triangle" class="w-5 h-5 flex-shrink-0 mt-0.5" />
          <span class="text-sm">{{ deleteError }}</span>
        </div>
      </template>
    </AdminConfirmModal>

    <!-- Scroll to top button -->
    <AdminScrollToTop :title="t('common.scroll_to_top')" />
  </div>
</template>
