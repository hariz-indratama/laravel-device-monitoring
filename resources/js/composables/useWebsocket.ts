import { useDeviceStore } from '@/stores/deviceStore'
import { useAlertStore } from '@/stores/alertStore'

export function useWebsocket(userId: number) {
  const deviceStore = useDeviceStore()
  const alertStore = useAlertStore()

  let subscribed = false

  function connect() {
    if (!window.Echo || subscribed) return
    subscribed = true

    window.Echo.private(`user.${userId}`)
      .listen('device.status.updated', (payload: { id: number; name: string; status: string; updated_at: string }) => {
        deviceStore.updateDeviceFromBroadcast(payload)
      })
      .listen('device.alert.created', (payload: { id: number; device_id: number; type: string; message: string; severity: string }) => {
        alertStore.pushAlert(payload as any)
      })
  }

  function disconnect() {
    if (window.Echo) {
      window.Echo.disconnect()
      subscribed = false
    }
  }

  function subscribeToDevice(deviceId: number) {
    if (!window.Echo) return

    window.Echo.private(`device.${deviceId}`)
      .listen('device.status.updated', (payload: { id: number; name: string; status: string; updated_at: string }) => {
        deviceStore.updateDeviceFromBroadcast(payload)
      })
  }

  function unsubscribeFromDevice(deviceId: number) {
    if (!window.Echo) return
    window.Echo.leave(`device.${deviceId}`)
  }

  return { connect, disconnect, subscribeToDevice, unsubscribeFromDevice }
}
