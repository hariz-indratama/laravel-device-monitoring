<script setup lang="ts">
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { Activity, MapPin, Clock, Gamepad2, Tv2 } from 'lucide-vue-next'
import DeviceStatusBadge from './DeviceStatusBadge.vue'
import type { Device } from '@/types/models'

const props = defineProps<{ device: Device }>()
defineEmits<{ click: [device: Device] }>()

// ── Uptime ticker ─────────────────────────────────────────────────────────────
const tick = ref(0)
let timer: ReturnType<typeof setInterval> | null = null

function startTimer() {
  stopTimer()
  timer = setInterval(() => { tick.value++ }, 1000)
}

function stopTimer() {
  if (timer) { clearInterval(timer); timer = null }
}

onMounted(() => { if (props.device.is_on) startTimer() })
onUnmounted(() => stopTimer())

watch(() => props.device.uptime_started_at, (newVal) => {
  if (newVal && props.device.is_on) startTimer()
  else stopTimer()
})
watch(() => props.device.is_on, (isOn) => {
  if (isOn) startTimer()
  else stopTimer()
})

const liveUptime = computed(() => {
  if (!props.device.is_on || !props.device.uptime_started_at) {
    return props.device.formatted_uptime || '—'
  }
  void tick.value
  const start = new Date(props.device.uptime_started_at).getTime()
  const seconds = Math.floor((Date.now() - start) / 1000)
  if (seconds < 0) return '—'

  const days  = Math.floor(seconds / 86400)
  const hours = Math.floor((seconds % 86400) / 3600)
  const mins  = Math.floor((seconds % 3600) / 60)
  const secs  = seconds % 60

  if (days > 0) return `${days}d ${hours}h ${mins}m`
  if (hours > 0) return `${hours}h ${mins}m ${secs}s`
  if (mins > 0) return `${mins}m ${secs}s`
  return `${secs}s`
})

// ── Device type helpers ────────────────────────────────────────────────────────
const deviceTypeConfig = computed(() => {
  switch (props.device.type) {
    case 'arcade':
      return {
        label: 'Arcade',
        icon: Tv2,
        bg: 'bg-purple-50',
        iconBg: 'bg-purple-100',
        iconColor: 'text-purple-600',
        border: 'border-purple-200',
      }
    case 'game_console':
    default:
      return {
        label: 'Game Console',
        icon: Gamepad2,
        bg: 'bg-blue-50',
        iconBg: 'bg-blue-100',
        iconColor: 'text-blue-600',
        border: 'border-blue-200',
      }
  }
})

function formatDate(dateStr: string | null | undefined) {
  if (!dateStr) return '—'
  return new Date(dateStr).toLocaleString('id-ID', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
  })
}

function statusColor(status: string) {
  switch (status) {
    case 'online':  return 'text-emerald-600'
    case 'offline': return 'text-red-600'
    case 'warning': return 'text-amber-600'
    default:        return 'text-gray-500'
  }
}
</script>

<template>
  <div
    class="group relative overflow-hidden bg-white rounded-2xl border border-gray-200 p-4 cursor-pointer
           transition-all duration-300 ease-out
           hover:shadow-xl hover:-translate-y-1 hover:border-emerald-300
           active:scale-[0.98]"
    @click="$emit('click', device)"
  >
    <!-- Top accent bar -->
    <div
      class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300"
      :class="device.is_on
        ? 'bg-linear-to-r from-emerald-400 via-teal-400 to-cyan-400'
        : 'bg-linear-to-r from-gray-300 to-gray-400'"
    />

    <!-- Header -->
    <div class="flex items-start justify-between gap-2">
      <div class="min-w-0 flex-1">
        <div class="flex items-center gap-2 mb-1">
          <!-- Device type badge -->
          <span
            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold border"
            :class="`${deviceTypeConfig.bg} ${deviceTypeConfig.border} ${deviceTypeConfig.iconColor}`"
          >
            <component :is="deviceTypeConfig.icon" class="w-3 h-3" />
            {{ deviceTypeConfig.label }}
          </span>
        </div>
        <h3 class="font-bold text-gray-900 truncate group-hover:text-emerald-700 transition-colors duration-200 text-sm leading-snug">
          {{ device.name }}
        </h3>
        <p class="text-[11px] text-gray-400 font-mono mt-0.5">{{ device.serial_number }}</p>
      </div>
      <div class="transition-transform duration-300 group-hover:scale-110 shrink-0">
        <DeviceStatusBadge :status="device.status" />
      </div>
    </div>

    <!-- Metrics grid -->
    <div class="mt-3 grid grid-cols-2 gap-2">

      <!-- Status -->
      <div
        class="flex items-center gap-2 p-2.5 rounded-xl transition-colors duration-200"
        :class="device.is_on
          ? 'bg-emerald-50 group-hover:bg-emerald-100'
          : 'bg-gray-100 group-hover:bg-gray-200'"
      >
        <Activity class="w-4 h-4 shrink-0" :class="statusColor(device.status)" />
        <div>
          <p class="text-[9px] text-gray-400 uppercase tracking-wide leading-none">Status</p>
          <p class="font-bold text-sm leading-tight mt-0.5" :class="statusColor(device.status)">
            {{ device.is_on ? 'ON' : 'OFF' }}
          </p>
        </div>
      </div>

      <!-- Uptime -->
      <div class="flex items-center gap-2 p-2.5 rounded-xl bg-blue-50/60 group-hover:bg-blue-50 transition-colors duration-200">
        <Clock class="w-4 h-4 shrink-0 text-blue-500" />
        <div class="min-w-0">
          <p class="text-[9px] text-gray-400 uppercase tracking-wide leading-none">Uptime</p>
          <p class="font-bold text-sm text-blue-600 leading-tight mt-0.5 truncate">
            {{ liveUptime }}
          </p>
        </div>
      </div>

    </div>

    <!-- Outlet -->
    <div
      v-if="device.outlet?.name || device.outlet_location"
      class="mt-2 flex items-center gap-1.5 text-xs text-gray-500"
    >
      <MapPin class="w-3.5 h-3.5 text-purple-400 shrink-0" />
      <span class="truncate">{{ device.outlet?.name || device.outlet_location }}</span>
    </div>

    <!-- Assigned date -->
    <div
      v-if="device.assigned_at"
      class="mt-1 text-[10px] text-gray-400"
    >
      Ditambahkan {{ formatDate(device.assigned_at) }}
    </div>

    <!-- Pulse dot for online -->
    <div
      v-if="device.is_on"
      class="absolute top-3 right-3 w-2 h-2 rounded-full bg-emerald-400 animate-pulse"
    />
  </div>
</template>
