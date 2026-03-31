<script setup lang="ts">
import { ref, onMounted, onUnmounted, watch } from 'vue'
import {
  Monitor,
  Wifi,
  WifiOff,
  AlertTriangle,
  Bell,
} from 'lucide-vue-next'
import type { DeviceStats } from '@/types/models'

const props = defineProps<{ stats: DeviceStats }>()

function useCountUp(target: () => number, duration = 1000) {
  const value = ref(0)
  let timer: ReturnType<typeof setInterval>

  const animate = () => {
    const end = target()
    const from = 0
    const step = Math.ceil(end / (duration / 16))
    value.value = from
    clearInterval(timer)
    timer = setInterval(() => {
      value.value = Math.min(value.value + step, end)
      if (value.value >= end) clearInterval(timer)
    }, 16)
  }

  onMounted(animate)
  onUnmounted(() => clearInterval(timer))
  watch(() => target(), animate)

  return { value }
}

const total   = useCountUp(() => props.stats.total_devices)
const online  = useCountUp(() => props.stats.online_devices)
const offline = useCountUp(() => props.stats.offline_devices)
const warning = useCountUp(() => props.stats.warning_devices)
const alerts  = useCountUp(() => props.stats.active_alerts)

const cards = [
  { label: 'Total Devices', value: total,  icon: Monitor,       bg: 'bg-slate-50',   border: 'border-slate-200',  iconBg: 'bg-slate-100',   iconColor: 'text-slate-600'  },
  { label: 'Online',        value: online, icon: Wifi,          bg: 'bg-emerald-50', border: 'border-emerald-200', iconBg: 'bg-emerald-100', iconColor: 'text-emerald-600' },
  { label: 'Offline',       value: offline,icon: WifiOff,        bg: 'bg-red-50',     border: 'border-red-200',    iconBg: 'bg-red-100',     iconColor: 'text-red-600'    },
  { label: 'Warning',       value: warning,icon: AlertTriangle,  bg: 'bg-amber-50',   border: 'border-amber-200',  iconBg: 'bg-amber-100',   iconColor: 'text-amber-600'  },
  { label: 'Active Alerts', value: alerts, icon: Bell,          bg: 'bg-rose-50',    border: 'border-rose-200',    iconBg: 'bg-rose-100',    iconColor: 'text-rose-600'    },
]
</script>

<template>
  <!-- Mobile: 2-col grid | Tablet sm: 3-col | Desktop md+: 5-col grid -->
  <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-5 md:gap-4">
    <div
      v-for="(card, i) in cards"
      :key="card.label"
      class="group relative overflow-hidden rounded-2xl border p-4 transition-all duration-300 hover:-translate-y-1 hover:shadow-lg"
      :class="[card.bg, card.border]"
      :style="{ animationDelay: `${i * 80}ms` }"
      style="animation: slideUp 0.5s ease-out both;"
    >
      <!-- Decorative circle -->
      <div :class="['absolute -right-6 -top-6 w-24 h-24 rounded-full opacity-10 transition-transform duration-500 group-hover:scale-150', card.iconBg]" />

      <!-- Icon -->
      <div :class="['mb-2 w-9 h-9 rounded-xl flex items-center justify-center transition-transform duration-300 group-hover:scale-110 group-hover:rotate-3', card.iconBg]">
        <component :is="card.icon" :class="['w-4 h-4', card.iconColor]" />
      </div>

      <!-- Label -->
      <p :class="['text-[10px] sm:text-xs uppercase tracking-wide font-semibold leading-tight', card.iconColor]">{{ card.label }}</p>

      <!-- Count -->
      <p :class="['mt-0.5 text-2xl sm:text-3xl font-extrabold tracking-tight', card.iconColor]">{{ card.value.value }}</p>

      <!-- Bottom accent line -->
      <div :class="['absolute bottom-0 left-0 h-0.5 w-0 group-hover:w-full transition-all duration-500 rounded-r-full', card.iconColor.replace('text-', 'bg-')]" />
    </div>
  </div>
</template>

<style scoped>
@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
