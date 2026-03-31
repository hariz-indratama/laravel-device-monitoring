<script setup lang="ts">
import type { DeviceStatus } from '@/types/models'

defineProps<{ status: DeviceStatus }>()

function config(status: DeviceStatus) {
  switch (status) {
    case 'online':      return { label: 'ON',          cls: 'bg-emerald-100 text-emerald-700' }
    case 'offline':     return { label: 'OFF',         cls: 'bg-red-100 text-red-700' }
    case 'warning':     return { label: 'WARNING',     cls: 'bg-amber-100 text-amber-700' }
    case 'maintenance': return { label: 'MAINTENANCE',  cls: 'bg-slate-100 text-slate-700' }
    default:            return { label: status,       cls: 'bg-gray-100 text-gray-700' }
  }
}
</script>

<template>
  <span :class="['inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold uppercase tracking-wider', config(status).cls]">
    <span class="w-1.5 h-1.5 rounded-full bg-current shrink-0"></span>
    {{ config(status).label }}
  </span>
</template>
