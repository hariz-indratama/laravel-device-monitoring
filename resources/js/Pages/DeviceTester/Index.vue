<script setup lang="ts">
import { ref, computed, onUnmounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import Button from '@/Components/ui/button/Button.vue'
import Input from '@/Components/ui/input/Input.vue'
import Select from '@/Components/ui/select/Select.vue'
import SelectContent from '@/Components/ui/select/SelectContent.vue'
import SelectItem from '@/Components/ui/select/SelectItem.vue'
import SelectTrigger from '@/Components/ui/select/SelectTrigger.vue'
import SelectValue from '@/Components/ui/select/SelectValue.vue'

// ── Types ─────────────────────────────────────────────────────────────────────
interface Device {
  id: number
  name: string
  serial_number: string
  type: string
  status: string
  outlet: string | null
  uptime: string | null
}

interface LogEntry {
  id: number
  time: string
  device: string
  serial: string
  status: 'on' | 'off'
  start_uptime: string | null
  end_uptime: string | null
  error: string | null
}

// ── Props ──────────────────────────────────────────────────────────────────────
defineProps<{ devices: Device[] }>()

// ── State ─────────────────────────────────────────────────────────────────────
const selectedSerial = ref<string>('')
const deviceStatus = ref<'on' | 'off'>('on')
const startUptime = ref<string>('')
const endUptime = ref<string>('')

const sending = ref(false)
const autoSend = ref(false)
const autoInterval = ref<number>(5)
let intervalTimer: ReturnType<typeof setInterval> | null = null

const logs = ref<LogEntry[]>([])
let logCounter = 0

// ── Computed ─────────────────────────────────────────────────────────────────
const selectedDevice = computed<Device | undefined>(
  () => (page.props.devices as Device[]).find((d) => d.serial_number === selectedSerial.value)
)


function fmtDateTime(now: Date = new Date()) {
  return now.toISOString().slice(0, 16).replace('T', ' ')
}

function fmtTime(now: Date = new Date()) {
  return now.toLocaleTimeString('en-US', { hour12: false })
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function payload() {
  return {
    status: deviceStatus.value,
    start_uptime: startUptime.value || null,
    end_uptime: endUptime.value || null,
  }
}

// ── Actions ───────────────────────────────────────────────────────────────────
async function sendData() {
  if (!selectedSerial.value) return
  sending.value = true

  try {
    await window.axios.post(
      `${API_BASE}/devices/${selectedSerial.value}/data`,
      payload()
    )
    logs.value.unshift({
      id: ++logCounter,
      time: fmtTime(),
      device: selectedDevice.value?.name ?? selectedSerial.value,
      serial: selectedSerial.value,
      status: deviceStatus.value,
      start_uptime: startUptime.value || null,
      end_uptime: endUptime.value || null,
      error: null,
    })
    if (logs.value.length > 50) logs.value.pop()
  } catch (err: any) {
    const message =
      err.response?.data?.message ?? err.response?.data ?? err.message ?? 'Unknown error'
    logs.value.unshift({
      id: ++logCounter,
      time: fmtTime(),
      device: selectedDevice.value?.name ?? selectedSerial.value,
      serial: selectedSerial.value,
      status: deviceStatus.value,
      start_uptime: startUptime.value || null,
      end_uptime: endUptime.value || null,
      error: String(message),
    })
    if (logs.value.length > 50) logs.value.pop()
  } finally {
    sending.value = false
  }
}

function applyPreset(status: 'on' | 'off', start?: string, end?: string) {
  deviceStatus.value = status
  startUptime.value = start ?? ''
  endUptime.value = end ?? ''
}

function toggleAutoSend() {
  autoSend.value = !autoSend.value
  if (autoSend.value) {
    intervalTimer = setInterval(sendData, autoInterval.value * 1000)
  } else {
    if (intervalTimer) clearInterval(intervalTimer)
    intervalTimer = null
  }
}

watch(autoInterval, (val) => {
  if (autoSend.value && intervalTimer) {
    clearInterval(intervalTimer)
    intervalTimer = setInterval(sendData, val * 1000)
  }
})

onUnmounted(() => {
  if (intervalTimer) clearInterval(intervalTimer)
})

// ── Presets ───────────────────────────────────────────────────────────────────
const presets = [
  {
    label: 'Power On',
    status: 'on' as const,
    color: 'bg-emerald-100 text-emerald-700 border-emerald-300 hover:bg-emerald-200',
  },
  {
    label: 'Power Off',
    status: 'off' as const,
    color: 'bg-red-100 text-red-700 border-red-300 hover:bg-red-200',
  },
  {
    label: 'On (Random Uptime)',
    status: 'on' as const,
    color: 'bg-blue-100 text-blue-700 border-blue-300 hover:bg-blue-200',
  },
]

const page = usePage<{ devices: Device[] }>()

const API_BASE = import.meta.env.VITE_API_BASE ?? '/api/v1'
</script>

<template>
  <AppLayout title="Device Tester">
    <div class="p-6 max-w-7xl mx-auto space-y-6">

      <!-- Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Device Dummy Tester</h1>
        <p class="mt-1 text-sm text-muted-foreground">
          Kirim status on/off dummy ke device untuk menguji sistem monitoring secara end-to-end.
        </p>
      </div>

      <div class="grid lg:grid-cols-5 gap-6">

        <!-- Left: Controls -->
        <div class="lg:col-span-3 space-y-5">

          <!-- Device Selector -->
          <div class="bg-white rounded-xl border border-border p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">
              1. Pilih Device
            </h2>
            <Select
              v-model="selectedSerial"
              class="w-full"
            >
              <SelectTrigger class="w-full">
                <SelectValue placeholder="-- Pilih device --" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem
                  v-for="device in page.props.devices"
                  :key="device.serial_number"
                  :value="device.serial_number"
                >
                  <span class="flex items-center gap-2">
                    <span
                      class="w-2 h-2 rounded-full shrink-0"
                      :class="{
                        'bg-emerald-500': device.status === 'online',
                        'bg-red-500': device.status === 'offline',
                        'bg-amber-500': device.status === 'warning',
                      }"
                    />
                    {{ device.name }}
                    <span class="text-xs text-gray-400">({{ device.serial_number }})</span>
                    <span v-if="device.outlet" class="text-xs text-gray-400">— {{ device.outlet }}</span>
                  </span>
                </SelectItem>
              </SelectContent>
            </Select>

            <!-- Device info -->
            <div v-if="selectedDevice" class="mt-3 grid grid-cols-3 gap-3">
              <div class="bg-slate-50 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 mb-1">Current Status</div>
                <div
                  class="text-sm font-semibold capitalize"
                  :class="selectedDevice.status === 'online' ? 'text-emerald-600' : 'text-red-500'"
                >
                  {{ selectedDevice.status }}
                </div>
              </div>
              <div class="bg-slate-50 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 mb-1">Uptime Started</div>
                <div class="text-sm font-semibold text-gray-800">
                  {{ selectedDevice.uptime ? new Date(selectedDevice.uptime).toLocaleString() : '—' }}
                </div>
              </div>
              <div class="bg-slate-50 rounded-lg p-3 text-center">
                <div class="text-xs text-gray-500 mb-1">Type</div>
                <div class="text-sm font-semibold text-gray-800 capitalize">
                  {{ selectedDevice.type }}
                </div>
              </div>
            </div>
          </div>

          <!-- Status & Uptime -->
          <div class="bg-white rounded-xl border border-border p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">
              2. Atur Status & Uptime
            </h2>

            <!-- Status Toggle -->
            <div class="mb-5">
              <div class="flex items-center justify-between mb-2">
                <label class="text-sm font-medium text-gray-700">Device Status</label>
                <span
                  class="text-xs font-semibold px-2 py-0.5 rounded-full uppercase"
                  :class="deviceStatus === 'on'
                    ? 'bg-emerald-100 text-emerald-700'
                    : 'bg-red-100 text-red-600'"
                >
                  {{ deviceStatus }}
                </span>
              </div>
              <div class="flex gap-3">
                <button
                  type="button"
                  class="flex-1 py-3 rounded-xl border-2 font-semibold text-sm transition-all"
                  :class="deviceStatus === 'on'
                    ? 'border-emerald-500 bg-emerald-50 text-emerald-700'
                    : 'border-gray-200 bg-white text-gray-400 hover:border-gray-300'"
                  @click="deviceStatus = 'on'"
                >
                  ON
                </button>
                <button
                  type="button"
                  class="flex-1 py-3 rounded-xl border-2 font-semibold text-sm transition-all"
                  :class="deviceStatus === 'off'
                    ? 'border-red-500 bg-red-50 text-red-600'
                    : 'border-gray-200 bg-white text-gray-400 hover:border-gray-300'"
                  @click="deviceStatus = 'off'"
                >
                  OFF
                </button>
              </div>
            </div>

            <!-- Start Uptime -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Start Uptime</label>
              <Input
                v-model="startUptime"
                type="datetime-local"
                class="w-full"
              />
              <p class="text-xs text-gray-400 mt-1">Waktu device mulai aktif (kosongkan jika tidak diperlukan)</p>
            </div>

            <!-- End Uptime -->
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">End Uptime</label>
              <Input
                v-model="endUptime"
                type="datetime-local"
                class="w-full"
              />
              <p class="text-xs text-gray-400 mt-1">Waktu device mati / selesai (kosongkan jika tidak diperlukan)</p>
            </div>
          </div>

          <!-- Presets -->
          <div class="bg-white rounded-xl border border-border p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-3 uppercase tracking-wide">
              3. Preset
            </h2>
            <div class="flex flex-wrap gap-2">
              <button
                v-for="preset in presets"
                :key="preset.label"
                type="button"
                class="px-3 py-1.5 text-xs font-medium rounded-full border transition-colors"
                :class="preset.color"
                @click="applyPreset(preset.status)"
              >
                {{ preset.label }}
              </button>
            </div>
          </div>

          <!-- Send Controls -->
          <div class="bg-white rounded-xl border border-border p-5 shadow-sm">
            <h2 class="text-sm font-semibold text-gray-700 mb-4 uppercase tracking-wide">
              4. Kirim Data
            </h2>

            <div class="flex items-center gap-3">
              <Button
                variant="success"
                size="lg"
                class="flex-1"
                :disabled="!selectedSerial || sending"
                :loading="sending"
                @click="sendData"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                  <line x1="22" y1="2" x2="11" y2="13"/>
                  <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
                {{ sending ? 'Sending…' : 'Send Data' }}
              </Button>

              <div class="h-10 w-px bg-gray-200" />

              <!-- Auto-send toggle -->
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors"
                  :class="autoSend ? 'bg-emerald-600' : 'bg-gray-300'"
                  @click="toggleAutoSend"
                >
                  <span
                    class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform"
                    :class="autoSend ? 'translate-x-6' : 'translate-x-1'"
                  />
                </button>
                <span class="text-xs font-medium cursor-pointer select-none" @click="toggleAutoSend">
                  Auto
                </span>
              </div>

              <!-- Interval -->
              <div v-if="autoSend" class="flex items-center gap-1">
                <Input
                  v-model="autoInterval"
                  type="number"
                  class="w-16 text-center"
                  :min="1"
                  :max="60"
                  :step="1"
                />
                <span class="text-xs text-gray-500">sec</span>
              </div>
            </div>

            <!-- Auto-send indicator -->
            <div v-if="autoSend" class="mt-3 flex items-center gap-2 text-xs text-emerald-600">
              <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-600"></span>
              </span>
              Auto-send active every {{ autoInterval }}s
            </div>
          </div>
        </div>

        <!-- Right: Activity Log -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-xl border border-border shadow-sm flex flex-col" style="height: 680px">
            <div class="px-5 py-4 border-b border-border flex items-center justify-between">
              <h2 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                Activity Log
              </h2>
              <button
                v-if="logs.length"
                type="button"
                class="text-xs text-gray-400 hover:text-gray-600 transition-colors"
                @click="logs = []"
              >
                Clear
              </button>
            </div>

            <div class="flex-1 overflow-y-auto">
              <!-- Empty state -->
              <div
                v-if="logs.length === 0"
                class="flex flex-col items-center justify-center h-full text-gray-400"
              >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 mb-3 opacity-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                  <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                </svg>
                <p class="text-sm">No activity yet.</p>
                <p class="text-xs mt-1">Send status to see the log here.</p>
              </div>

              <!-- Log entries -->
              <div class="divide-y divide-gray-100">
                <div
                  v-for="log in logs"
                  :key="log.id"
                  class="px-4 py-3 hover:bg-slate-50 transition-colors"
                >
                  <!-- Header -->
                  <div class="flex items-start justify-between gap-2 mb-1">
                    <div class="flex items-center gap-1.5 min-w-0">
                      <!-- Status dot -->
                      <span
                        class="w-1.5 h-1.5 rounded-full shrink-0 mt-1"
                        :class="log.error ? 'bg-red-500' : log.status === 'on' ? 'bg-emerald-500' : 'bg-red-400'"
                      />
                      <span class="text-xs font-semibold text-gray-800 truncate">
                        {{ log.device }}
                      </span>
                    </div>
                    <span class="text-xs text-gray-400 shrink-0">{{ log.time }}</span>
                  </div>

                  <!-- Serial -->
                  <div class="text-xs text-gray-400 mb-1.5 ml-3 font-mono">
                    {{ log.serial }}
                  </div>

                  <!-- Status badge -->
                  <div class="flex flex-wrap gap-1.5 mb-1.5 ml-3">
                    <span
                      class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded font-semibold"
                      :class="log.status === 'on'
                        ? 'bg-emerald-100 text-emerald-700'
                        : 'bg-red-100 text-red-600'"
                    >
                      {{ log.status === 'on' ? '● ON' : '○ OFF' }}
                    </span>
                    <span
                      v-if="log.start_uptime"
                      class="inline-flex items-center gap-0.5 text-xs px-1.5 py-0.5 rounded bg-slate-100 text-slate-600"
                    >
                      Start: {{ new Date(log.start_uptime).toLocaleString() }}
                    </span>
                    <span
                      v-if="log.end_uptime"
                      class="inline-flex items-center gap-0.5 text-xs px-1.5 py-0.5 rounded bg-slate-100 text-slate-600"
                    >
                      End: {{ new Date(log.end_uptime).toLocaleString() }}
                    </span>
                  </div>

                  <!-- Error -->
                  <div
                    v-if="log.error"
                    class="ml-3 mt-1 px-2 py-1 bg-red-50 border border-red-200 rounded text-xs text-red-600"
                  >
                    ❌ {{ log.error }}
                  </div>

                  <!-- Success -->
                  <div v-if="!log.error" class="ml-3 text-xs text-emerald-600">
                    ✅ Status logged successfully
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Endpoint info -->
          <div class="mt-3 p-3 bg-slate-900 rounded-lg">
            <div class="text-xs text-slate-400 mb-1 font-semibold uppercase tracking-wide">
              API Endpoint
            </div>
            <div class="font-mono text-xs text-emerald-400 break-all">
              POST {{ API_BASE }}/devices/{{ selectedSerial || '{serial_number}' }}/data
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
