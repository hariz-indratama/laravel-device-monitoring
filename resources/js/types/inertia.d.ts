import type { Device, DeviceAlert, DeviceLog, DeviceStats, PaginatedData, Outlet } from './models'

declare module '@inertiajs/core' {
  interface PageProps {
    auth: {
      id: number
      name: string
      role: 'owner' | 'staff'
    } | null
    errors: Record<string, string>
    flash: {
      success?: string
      error?: string
    }
  }
}

declare module '@inertiajs/core' {
  interface Page<Props extends Record<string, unknown> = Record<string, unknown>> {
    props: Props & import('@inertiajs/core').PageProps
  }
}
