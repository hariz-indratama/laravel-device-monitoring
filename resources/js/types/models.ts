// ─── Device Monitoring Domain Models ────────────────────────────────────────

export type DeviceStatus = 'online' | 'offline' | 'warning' | 'maintenance'
export type AlertSeverity = 'info' | 'warning' | 'critical'
export type AlertType =
  | 'temperature_high'
  | 'temperature_low'
  | 'device_offline'
  | 'device_online'
  | 'battery_low'
  | 'sensor_malfunction'
  | 'heartbeat_missed'

export interface Outlet {
  id: number
  name: string
  address: string | null
  phone: string | null
  user_id: number | null
  devices_count?: number
  created_at: string
  updated_at: string
}

export interface DeviceLog {
  id: number
  device_id: number
  status: 'on' | 'off'
  start_uptime: string | null
  end_uptime: string | null
  logged_at: string
  device?: { id: number; name: string }
}

export interface DeviceAlert {
  id: number
  device_id: number
  type: AlertType
  type_label: string
  message: string
  severity: AlertSeverity
  severity_label: string
  severity_color: string
  data: Record<string, unknown> | null
  resolved_at: string | null
  resolved_by?: { id: number; name: string }
  created_at: string
  device?: { id: number; name: string }
}

export interface Device {
  id: number
  name: string
  serial_number: string
  type: 'game_console' | 'arcade'
  status: DeviceStatus
  status_label: string
  status_color: string
  is_on: boolean
  is_off: boolean
  uptime_started_at: string | null
  uptime_seconds: number | null
  formatted_uptime: string
  assigned_at: string | null
  outlet_id: number | null
  outlet?: Outlet
  outlet_name?: string | null
  outlet_location?: string | null
  latest_log?: DeviceLog | null
  active_alerts?: number
  created_at: string
  updated_at: string
}

export interface DeviceStats {
  total_devices: number
  online_devices: number
  offline_devices: number
  warning_devices: number
  active_alerts: number
}

export interface PaginatedData<T> {
  data: T[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}
