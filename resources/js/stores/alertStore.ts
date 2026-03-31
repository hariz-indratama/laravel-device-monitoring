import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { DeviceAlert } from '@/types/models'

export const useAlertStore = defineStore('alerts', () => {
  const alerts = ref<DeviceAlert[]>([])
  const loading = ref(false)

  const unresolvedAlerts = computed(() => alerts.value.filter(a => !a.resolved_at))
  const criticalAlerts = computed(() => alerts.value.filter(a => a.severity === 'critical' && !a.resolved_at))
  const warningAlerts = computed(() => alerts.value.filter(a => a.severity === 'warning' && !a.resolved_at))

  function setAlerts(data: DeviceAlert[]) {
    alerts.value = data
  }

  function pushAlert(alert: DeviceAlert) {
    alerts.value.unshift(alert)
  }

  function resolveAlert(id: number) {
    const idx = alerts.value.findIndex(a => a.id === id)
    if (idx !== -1) {
      alerts.value[idx] = { ...alerts.value[idx], resolved_at: new Date().toISOString() }
    }
  }

  return {
    alerts,
    loading,
    unresolvedAlerts,
    criticalAlerts,
    warningAlerts,
    setAlerts,
    pushAlert,
    resolveAlert,
  }
})
