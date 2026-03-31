import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Device, DeviceLog, DeviceAlert, DeviceStats } from '@/types/models'

export const useDeviceStore = defineStore('devices', () => {
  const devices = ref<Device[]>([])
  const selectedDevice = ref<Device | null>(null)
  const stats = ref<DeviceStats>({
    total_devices: 0,
    online_devices: 0,
    offline_devices: 0,
    warning_devices: 0,
    active_alerts: 0,
  })
  const recentLogs = ref<DeviceLog[]>([])
  const recentAlerts = ref<DeviceAlert[]>([])
  const loading = ref(false)

  const onlineDevices = computed(() => devices.value.filter(d => d.status === 'online'))
  const offlineDevices = computed(() => devices.value.filter(d => d.status === 'offline'))
  const warningDevices = computed(() => devices.value.filter(d => d.status === 'warning'))

  function setDevices(data: Device[]) {
    devices.value = data
  }

  function setStats(data: DeviceStats) {
    stats.value = data
  }

  function setSelectedDevice(device: Device) {
    selectedDevice.value = device
  }

  function updateDeviceFromBroadcast(payload: {
    id: number
    name: string
    serial_number?: string
    status: string
    uptime_started_at?: string | null
    formatted_uptime?: string
    is_on?: boolean
    is_off?: boolean
    outlet_id?: number
    updated_at?: string
  }) {
    const idx = devices.value.findIndex(d => d.id === payload.id)
    if (idx !== -1) {
      devices.value[idx] = {
        ...devices.value[idx],
        status: payload.status as Device['status'],
        uptime_started_at: payload.uptime_started_at ?? devices.value[idx].uptime_started_at,
        formatted_uptime: payload.formatted_uptime ?? devices.value[idx].formatted_uptime,
        is_on: payload.is_on ?? devices.value[idx].is_on,
        is_off: payload.is_off ?? devices.value[idx].is_off,
        updated_at: payload.updated_at ?? new Date().toISOString(),
      }
    }
    if (selectedDevice.value?.id === payload.id) {
      selectedDevice.value = {
        ...selectedDevice.value,
        status: payload.status as Device['status'],
        uptime_started_at: payload.uptime_started_at ?? selectedDevice.value.uptime_started_at,
        formatted_uptime: payload.formatted_uptime ?? selectedDevice.value.formatted_uptime,
        is_on: payload.is_on ?? selectedDevice.value.is_on,
        is_off: payload.is_off ?? selectedDevice.value.is_off,
        updated_at: payload.updated_at ?? new Date().toISOString(),
      }
    }
  }

  function updateLatestLog(payload: {
    id: number
    device_id: number
    status?: string
    start_uptime?: string | null
    end_uptime?: string | null
    logged_at: string
  }) {
    const idx = devices.value.findIndex(d => d.id === payload.device_id)
    if (idx !== -1) {
      devices.value[idx] = { ...devices.value[idx], latest_log: payload as unknown as DeviceLog }
    }
    if (selectedDevice.value?.id === payload.device_id) {
      selectedDevice.value = { ...selectedDevice.value, latest_log: payload as unknown as DeviceLog }
    }
    // Also push to recent logs
    recentLogs.value.unshift(payload as unknown as DeviceLog)
    if (recentLogs.value.length > 50) recentLogs.value.pop()
  }

  function pushLog(log: DeviceLog) {
    recentLogs.value.unshift(log)
    if (recentLogs.value.length > 50) recentLogs.value.pop()
  }

  function pushAlert(alert: DeviceAlert) {
    recentAlerts.value.unshift(alert)
    if (recentAlerts.value.length > 50) recentAlerts.value.pop()
  }

  return {
    devices,
    selectedDevice,
    stats,
    recentLogs,
    recentAlerts,
    loading,
    onlineDevices,
    offlineDevices,
    warningDevices,
    setDevices,
    setStats,
    setSelectedDevice,
    updateDeviceFromBroadcast,
    updateLatestLog,
    pushLog,
    pushAlert,
  }
})
