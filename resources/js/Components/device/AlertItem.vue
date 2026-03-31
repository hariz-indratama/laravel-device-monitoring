<script setup lang="ts">
import {
  AlertOctagon,
  AlertTriangle,
  Info,
  CheckCircle2,
} from 'lucide-vue-next'
import type { DeviceAlert } from '@/types/models'

defineProps<{ alert: DeviceAlert }>()
defineEmits<{ resolve: [id: number] }>()

const severityConfig = {
  critical: {
    wrapper: 'border-l-red-500 bg-red-50/70',
    icon:    AlertOctagon,
    iconColor: 'text-red-500',
    iconBg:    'bg-red-100',
    label:    'text-red-600',
    dot:      'bg-red-500',
  },
  warning: {
    wrapper: 'border-l-amber-500 bg-amber-50/70',
    icon:    AlertTriangle,
    iconColor: 'text-amber-500',
    iconBg:    'bg-amber-100',
    label:    'text-amber-600',
    dot:      'bg-amber-500',
  },
  info: {
    wrapper: 'border-l-blue-500 bg-blue-50/70',
    icon:    Info,
    iconColor: 'text-blue-500',
    iconBg:    'bg-blue-100',
    label:    'text-blue-600',
    dot:      'bg-blue-500',
  },
}

const defaultConfig = {
  wrapper: 'border-l-gray-300 bg-gray-50/70',
  icon:    Info,
  iconColor: 'text-gray-400',
  iconBg:    'bg-gray-100',
  label:    'text-gray-500',
  dot:      'bg-gray-400',
}

function getConfig(severity: string) {
  return severityConfig[severity as keyof typeof severityConfig] ?? defaultConfig
}
</script>

<template>
  <div
    class="group relative overflow-hidden rounded-xl border-l-4 p-3 transition-all duration-300 hover:shadow-md hover:-translate-x-0.5"
    :class="getConfig(alert.severity).wrapper"
  >
    <!-- Critical pulse -->
    <div
      v-if="alert.severity === 'critical' && !alert.resolved_at"
      class="absolute inset-0 rounded-xl animate-pulse opacity-5 pointer-events-none"
      :class="getConfig(alert.severity).dot"
    />

    <div class="flex items-start gap-3">
      <!-- Icon -->
      <div
        class="mt-0.5 shrink-0 w-8 h-8 rounded-lg flex items-center justify-center transition-transform duration-300 group-hover:scale-110"
        :class="getConfig(alert.severity).iconBg"
      >
        <component
          :is="getConfig(alert.severity).icon"
          class="w-4 h-4 transition-transform duration-300 group-hover:rotate-12"
          :class="getConfig(alert.severity).iconColor"
        />
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <div class="flex items-center gap-2 flex-wrap">
          <span
            class="text-[10px] font-bold uppercase tracking-widest"
            :class="getConfig(alert.severity).label"
          >
            {{ alert.severity }}
          </span>
          <span class="text-[10px] text-gray-300">•</span>
          <span class="text-[10px] text-gray-400 uppercase tracking-wide">{{ alert.type_label }}</span>

          <!-- Unresolved dot -->
          <span
            v-if="!alert.resolved_at"
            class="ml-auto w-1.5 h-1.5 rounded-full animate-pulse"
            :class="getConfig(alert.severity).dot"
          />
        </div>

        <p class="mt-1 text-sm font-medium text-gray-800 leading-snug">{{ alert.message }}</p>

        <p v-if="alert.device" class="mt-0.5 text-xs text-gray-400 flex items-center gap-1">
          <span>{{ alert.device.name }}</span>
        </p>

        <p class="mt-1 text-[10px] text-gray-300 tabular-nums">
          {{ new Date(alert.created_at).toLocaleString('id-ID') }}
        </p>
      </div>

      <!-- Action -->
      <div class="shrink-0">
        <button
          v-if="!alert.resolved_at"
          class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg
                 border border-transparent transition-all duration-200
                 bg-white text-emerald-600 hover:bg-emerald-50 hover:border-emerald-200
                 active:scale-95"
          @click.stop="$emit('resolve', alert.id)"
        >
          <CheckCircle2 class="w-3 h-3" />
          Resolve
        </button>
        <span
          v-else
          class="flex items-center gap-1 text-xs font-semibold px-3 py-1.5 rounded-lg
                 bg-emerald-50 text-emerald-500"
        >
          <CheckCircle2 class="w-3 h-3" />
          Done
        </span>
      </div>
    </div>
  </div>
</template>
