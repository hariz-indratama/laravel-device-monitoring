<script setup lang="ts">
import { ref, computed } from 'vue'
import { usePage, Link, router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/Components/ui/button/Button.vue'
import Input from '@/Components/ui/input/Input.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'
import type { DeviceAlert } from '@/types/models'

interface AlertStats {
  total: number
  critical: number
  warning: number
  unresolved: number
  resolved: number
}

const page = usePage<{
  alerts: {
    data: DeviceAlert[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  stats: AlertStats
  filters: { search?: string; severity?: string; status?: string }
}>()

const filters = ref({ ...page.props.filters })
const selectedIds = ref<Set<number>>(new Set())
const resolving = ref(false)

// ── Modal state ────────────────────────────────────────────────────────────────
const showResolveModal = ref(false)
const showBulkResolveModal = ref(false)
const pendingAlert = ref<DeviceAlert | null>(null)

function openResolveModal(alert: DeviceAlert) {
    pendingAlert.value = alert
    showResolveModal.value = true
}

function openBulkResolveModal() {
    showBulkResolveModal.value = true
}

// ── Computed ──────────────────────────────────────────────────────────────────
const allSelected = computed(
  () =>
    page.props.alerts.data.length > 0 &&
    page.props.alerts.data.every((a) => selectedIds.value.has(a.id))
)

const severityClass = (severity: string) => {
  switch (severity) {
    case 'critical': return { bar: 'border-l-red-500', bg: 'bg-red-50', badge: 'bg-red-100 text-red-700', dot: 'bg-red-500', icon: '🔴' }
    case 'warning':  return { bar: 'border-l-amber-500', bg: 'bg-amber-50', badge: 'bg-amber-100 text-amber-700', dot: 'bg-amber-500', icon: '🟡' }
    case 'info':     return { bar: 'border-l-blue-500', bg: 'bg-blue-50', badge: 'bg-blue-100 text-blue-700', icon: '🔵' }
    default:         return { bar: 'border-l-gray-300', bg: 'bg-gray-50', badge: 'bg-gray-100 text-gray-600', icon: '⚪' }
  }
}

const typeIcon = (type: string) => {
  switch (type) {
    case 'temperature_high': return '🌡️'
    case 'temperature_low': return '❄️'
    case 'battery_low': return '🔋'
    case 'device_offline': return '📴'
    case 'device_online': return '✅'
    case 'sensor_malfunction': return '⚠️'
    case 'heartbeat_missed': return '💓'
    default: return '📢'
  }
}

// ── Filters ───────────────────────────────────────────────────────────────────
function applyFilters() {
  const params: Record<string, string> = {}
  if (filters.value.search) params.search = filters.value.search
  if (filters.value.severity) params.severity = filters.value.severity
  if (filters.value.status) params.status = filters.value.status
  router.get('/alerts', params)
}

function clearFilters() {
  filters.value = { search: '', severity: '', status: '' }
  router.get('/alerts')
}

// ── Selection ─────────────────────────────────────────────────────────────────
function toggleSelect(id: number) {
  if (selectedIds.value.has(id)) selectedIds.value.delete(id)
  else selectedIds.value.add(id)
}

function toggleSelectAll() {
  if (allSelected.value) selectedIds.value.clear()
  else page.props.alerts.data.forEach((a) => selectedIds.value.add(a.id))
}

// ── Actions ───────────────────────────────────────────────────────────────────
function confirmResolve() {
  if (!pendingAlert.value) return
  resolving.value = true
  router.post(`/alerts/${pendingAlert.value.id}/resolve`, {}, {
    onFinish: () => {
      resolving.value = false
      showResolveModal.value = false
      pendingAlert.value = null
    },
    onSuccess: () => {
      selectedIds.value.delete(pendingAlert.value!.id)
    },
  })
}

function confirmBulkResolve() {
  if (selectedIds.value.size === 0) return
  resolving.value = true
  router.post('/alerts/bulk-resolve', { ids: [...selectedIds.value] }, {
    onFinish: () => {
      resolving.value = false
      showBulkResolveModal.value = false
    },
    onSuccess: () => {
      selectedIds.value.clear()
    },
  })
}
</script>

<template>
  <AppLayout title="Alerts">
    <div class="p-6 space-y-5">

      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Alerts</h1>
          <p class="mt-1 text-sm text-muted-foreground">
            Monitor dan kelola semua alert dari device IoT.
          </p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl border border-border p-4 shadow-sm">
          <div class="text-xs font-medium text-gray-500 uppercase tracking-wide mb-1">Total</div>
          <div class="text-2xl font-bold text-gray-900">{{ page.props.stats.total }}</div>
        </div>
        <div class="bg-white rounded-xl border border-l-4 border-l-red-500 border-border p-4 shadow-sm">
          <div class="text-xs font-medium text-red-600 uppercase tracking-wide mb-1">Critical</div>
          <div class="text-2xl font-bold text-red-700">{{ page.props.stats.critical }}</div>
        </div>
        <div class="bg-white rounded-xl border border-l-4 border-l-amber-500 border-border p-4 shadow-sm">
          <div class="text-xs font-medium text-amber-600 uppercase tracking-wide mb-1">Warning</div>
          <div class="text-2xl font-bold text-amber-700">{{ page.props.stats.warning }}</div>
        </div>
        <div class="bg-white rounded-xl border border-l-4 border-l-orange-500 border-border p-4 shadow-sm">
          <div class="text-xs font-medium text-orange-600 uppercase tracking-wide mb-1">Unresolved</div>
          <div class="text-2xl font-bold text-orange-700">{{ page.props.stats.unresolved }}</div>
        </div>
        <div class="bg-white rounded-xl border border-l-4 border-l-emerald-500 border-border p-4 shadow-sm">
          <div class="text-xs font-medium text-emerald-600 uppercase tracking-wide mb-1">Resolved</div>
          <div class="text-2xl font-bold text-emerald-700">{{ page.props.stats.resolved }}</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl border border-border p-4 shadow-sm">
        <div class="flex flex-wrap gap-3 items-end">
          <!-- Search -->
          <div class="flex-1 min-w-48">
            <label class="text-xs font-medium text-gray-600 mb-1 block">Search</label>
            <Input
              v-model="filters.search"
              placeholder="Search message..."
              @keyup.enter="applyFilters"
            />
          </div>

          <!-- Severity -->
          <div class="min-w-36">
            <label class="text-xs font-medium text-gray-600 mb-1 block">Severity</label>
            <select
              v-model="filters.severity"
              class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
              <option value="">All</option>
              <option value="critical">Critical</option>
              <option value="warning">Warning</option>
              <option value="info">Info</option>
            </select>
          </div>

          <!-- Status -->
          <div class="min-w-36">
            <label class="text-xs font-medium text-gray-600 mb-1 block">Status</label>
            <select
              v-model="filters.status"
              class="h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
            >
              <option value="">All</option>
              <option value="unresolved">Unresolved</option>
              <option value="resolved">Resolved</option>
            </select>
          </div>

          <!-- Buttons -->
          <div class="flex gap-2">
            <Button @click="applyFilters" class="bg-emerald-600 hover:bg-emerald-700">
              <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
              Filter
            </Button>
            <Button variant="ghost" @click="clearFilters">Clear</Button>
          </div>
        </div>
      </div>

      <!-- Bulk Action Bar -->
      <div
        v-if="selectedIds.size > 0"
        class="bg-emerald-50 border border-emerald-200 rounded-xl p-3 flex items-center justify-between"
      >
        <span class="text-sm font-medium text-emerald-700">
          {{ selectedIds.size }} alert(s) selected
        </span>
        <div class="flex gap-2">
          <Button
            variant="ghost"
            size="sm"
            @click="selectedIds.clear()"
          >
            Deselect all
          </Button>
          <Button
            size="sm"
            variant="success"
            :loading="resolving"
            @click="openBulkResolveModal"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            Resolve Selected
          </Button>
        </div>
      </div>

      <!-- Alert List -->
      <div class="bg-white rounded-xl border border-border shadow-sm overflow-hidden">
        <!-- Table Header -->
        <div class="grid grid-cols-[auto_1fr_auto_auto_auto] gap-4 px-5 py-3 border-b border-border bg-slate-50 text-xs font-semibold text-gray-500 uppercase tracking-wide">
          <div class="flex items-center">
            <input
              type="checkbox"
              class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
              :checked="allSelected"
              @change="toggleSelectAll"
            />
          </div>
          <div>Alert</div>
          <div class="text-center">Severity</div>
          <div class="text-center">Status</div>
          <div class="text-right">Action</div>
        </div>

        <!-- Empty State -->
        <div
          v-if="page.props.alerts.data.length === 0"
          class="flex flex-col items-center justify-center py-20 text-gray-400"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-12 h-12 mb-4 opacity-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          <p class="text-base font-medium">No alerts found</p>
          <p class="text-sm mt-1">Try adjusting your filters.</p>
        </div>

        <!-- Alert Rows -->
        <div
          v-for="alert in page.props.alerts.data"
          :key="alert.id"
          :class="[
            'grid grid-cols-[auto_1fr_auto_auto_auto] gap-4 px-5 py-4 border-b border-gray-100 items-start hover:bg-slate-50 transition-colors',
            severityClass(alert.severity).bar + ' border-l-4',
          ]"
        >
          <!-- Checkbox -->
          <div class="flex items-center pt-0.5">
            <input
              type="checkbox"
              class="w-4 h-4 rounded border-gray-300 text-emerald-600 focus:ring-emerald-500 cursor-pointer"
              :checked="selectedIds.has(alert.id)"
              :disabled="!!alert.resolved_at"
              @change="toggleSelect(alert.id)"
            />
          </div>

          <!-- Alert Info -->
          <div class="min-w-0">
            <div class="flex items-center gap-2 mb-1">
              <span class="text-sm">{{ typeIcon(alert.type) }}</span>
              <span class="text-xs font-medium text-gray-600 uppercase">{{ alert.type_label }}</span>
              <span v-if="alert.device" class="text-xs text-gray-400">
                — {{ alert.device.name }}
              </span>
            </div>
            <p class="text-sm font-medium text-gray-900 leading-snug">{{ alert.message }}</p>

            <!-- Alert data details -->
            <div v-if="alert.data && Object.keys(alert.data).length > 0" class="mt-1.5 flex flex-wrap gap-2">
              <span
                v-if="alert.data.temperature != null"
                class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600"
              >
                🌡️ {{ alert.data.temperature }}°C
              </span>
              <span
                v-if="alert.data.battery != null"
                class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600"
              >
                🔋 {{ alert.data.battery }}%
              </span>
              <span
                v-if="alert.data.threshold != null"
                class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded bg-slate-100 text-slate-600"
              >
                Threshold: {{ alert.data.threshold }}
              </span>
            </div>

            <!-- Meta -->
            <p class="mt-1.5 text-xs text-gray-400">
              {{ new Date(alert.created_at).toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'short' }) }}
              <span v-if="alert.resolved_by"> • Resolved by {{ alert.resolved_by.name }}</span>
            </p>
          </div>

          <!-- Severity Badge -->
          <div class="flex justify-center pt-0.5">
            <span
              :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold uppercase', severityClass(alert.severity).badge]"
            >
              <span class="w-1.5 h-1.5 rounded-full" :class="severityClass(alert.severity).dot" />
              {{ alert.severity }}
            </span>
          </div>

          <!-- Status Badge -->
          <div class="flex justify-center pt-0.5">
            <span
              v-if="!alert.resolved_at"
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-orange-100 text-orange-700"
            >
              Unresolved
            </span>
            <span
              v-else
              class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              Resolved
            </span>
          </div>

          <!-- Action -->
          <div class="flex justify-end pt-0.5">
            <button
              v-if="!alert.resolved_at"
              type="button"
              class="inline-flex items-center gap-1 text-xs font-medium text-emerald-600 hover:text-emerald-800 transition-colors px-3 py-1.5 rounded-md hover:bg-emerald-50"
              @click="openResolveModal(alert)"
            >
              <svg xmlns="http://www.w3.org/2000/svg" class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              Resolve
            </button>
            <span v-else class="text-xs text-gray-400 italic">—</span>
          </div>
        </div>

        <!-- Pagination -->
        <div
          v-if="page.props.alerts.last_page > 1"
          class="flex items-center justify-between px-5 py-3 border-t border-border bg-slate-50"
        >
          <p class="text-xs text-gray-500">
            Showing {{ page.props.alerts.data.length }} of {{ page.props.alerts.total }} alerts
          </p>
          <div class="flex gap-1">
            <Link
              v-if="page.props.alerts.current_page > 1"
              :href="`/alerts?page=${page.props.alerts.current_page - 1}`"
              class="px-3 py-1.5 rounded text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
            >
              ← Prev
            </Link>
            <template v-for="p in page.props.alerts.last_page" :key="p">
              <Link
                :href="`/alerts?page=${p}`"
                :class="[
                  'w-9 h-9 flex items-center justify-center rounded text-sm font-medium transition-colors',
                  p === page.props.alerts.current_page
                    ? 'bg-emerald-600 text-white'
                    : 'text-gray-600 hover:bg-gray-100',
                ]"
              >
                {{ p }}
              </Link>
            </template>
            <Link
              v-if="page.props.alerts.current_page < page.props.alerts.last_page"
              :href="`/alerts?page=${page.props.alerts.current_page + 1}`"
              class="px-3 py-1.5 rounded text-sm font-medium text-gray-600 hover:bg-gray-100 transition-colors"
            >
              Next →
            </Link>
          </div>
        </div>
      </div>
    </div>

    <!-- Resolve Single Alert Modal -->
    <ConfirmModal
        v-model:open="showResolveModal"
        title="Resolve Alert?"
        :description="pendingAlert ? `Alert '${pendingAlert.message}' akan ditandai sebagai resolved. Lanjutkan?` : ''"
        confirm-text="Ya, Resolve"
        cancel-text="Batal"
        variant="success"
        :loading="resolving"
        @confirm="confirmResolve"
    />

    <!-- Bulk Resolve Modal -->
    <ConfirmModal
        v-model:open="showBulkResolveModal"
        title="Resolve Multiple Alerts?"
        :description="`${selectedIds.size} alert(s) yang dipilih akan ditandai sebagai resolved. Lanjutkan?`"
        confirm-text="Ya, Resolve Semua"
        cancel-text="Batal"
        variant="success"
        :loading="resolving"
        @confirm="confirmBulkResolve"
    />
  </AppLayout>
</template>
