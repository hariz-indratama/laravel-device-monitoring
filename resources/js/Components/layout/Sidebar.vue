<script setup lang="ts">
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { X, LayoutDashboard, Monitor, Building2, Bell, Zap, Users } from 'lucide-vue-next'
import type { ConnectionStatus } from 'laravel-echo'

const props = defineProps<{
  connectionStatus?: ConnectionStatus
}>()

defineEmits<{ close: [] }>()

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const page = usePage() as any
const auth = computed(() => page.props.auth)

const navItems = computed(() => {
  const base = [
    { label: 'Dashboard',     href: '/dashboard',     icon: LayoutDashboard },
    { label: 'Devices',       href: '/devices',        icon: Monitor        },
    { label: 'Outlets',      href: '/outlets',        icon: Building2      },
    { label: 'Alerts',        href: '/alerts',         icon: Bell           },
    { label: 'Device Tester', href: '/device-tester',  icon: Zap            },
  ]
  if (auth.value?.role === 'owner') {
    base.splice(4, 0, { label: 'Users', href: '/users', icon: Users })
  }
  return base
})

const currentPath = computed(() => page.url)

const wsStatus = computed(() => {
  switch (props.connectionStatus) {
    case 'connected':     return { label: 'Live',         color: 'bg-emerald-400', textColor: 'text-emerald-400' }
    case 'connecting':   return { label: 'Connecting…',  color: 'bg-amber-400',   textColor: 'text-amber-400'   }
    case 'disconnected': return { label: 'Offline',       color: 'bg-red-400',     textColor: 'text-red-400'     }
    default:             return { label: 'WebSocket Off',  color: 'bg-slate-500',   textColor: 'text-slate-500'   }
  }
})
</script>

<template>
  <aside class="w-64 min-h-screen bg-slate-900 text-white flex flex-col shrink-0">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-700">
      <div>
        <h1 class="text-lg font-bold tracking-tight">
          <span class="text-emerald-400">Device</span>Monitor
        </h1>
        <p class="text-xs text-slate-400 mt-0.5">IoT Monitoring System</p>
      </div>
      <!-- Close button (mobile only) -->
      <button
        class="lg:hidden p-1.5 rounded-lg hover:bg-slate-800 transition-colors"
        aria-label="Close menu"
        @click="$emit('close')"
      >
        <X class="w-5 h-5 text-slate-400" />
      </button>
    </div>

    <!-- WebSocket status -->
    <div class="px-5 py-2.5 flex items-center gap-2">
      <span
        class="w-1.5 h-1.5 rounded-full shrink-0 transition-colors animate-pulse"
        :class="wsStatus.color"
        :title="wsStatus.label"
      />
      <span class="text-xs" :class="wsStatus.textColor">{{ wsStatus.label }}</span>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-3 py-2 space-y-0.5 overflow-y-auto">
      <Link
        v-for="item in navItems"
        :key="item.href"
        :href="item.href"
        :class="[
          'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-150',
          currentPath.startsWith(item.href)
            ? 'bg-emerald-600 text-white shadow-sm'
            : 'text-slate-300 hover:bg-slate-800 hover:text-white active:scale-[0.98]',
        ]"
      >
        <component :is="item.icon" class="w-5 h-5 shrink-0" />
        <span>{{ item.label }}</span>
        <!-- Active indicator dot -->
        <span
          v-if="currentPath.startsWith(item.href)"
          class="ml-auto w-1.5 h-1.5 rounded-full bg-white/60"
        />
      </Link>
    </nav>

    <!-- User section -->
    <div class="px-4 py-4 border-t border-slate-700">
      <div class="flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center text-sm font-bold shrink-0 select-none">
          {{ auth?.name?.charAt(0)?.toUpperCase() || 'U' }}
        </div>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium truncate">{{ auth?.name }}</p>
          <p class="text-xs text-slate-400 capitalize">{{ auth?.role }}</p>
        </div>
        <Link
          href="/logout"
          method="post"
          as="button"
          class="p-1.5 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors active:scale-90"
          title="Logout"
        >
          <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
          </svg>
        </Link>
      </div>
    </div>
  </aside>
</template>
