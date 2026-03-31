/**
 * Global WebSocket broadcast composable.
 * Initializes Laravel Echo/Reverb subscriptions on app mount.
 *
 * All events (status, alert, log) are received via the user.{id} private channel
 * which covers every device the user owns. Device.{id} subscriptions are
 * added lazily when specific device IDs are passed via subscribeToDevice().
 */
import { onMounted, onUnmounted } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { useConnectionStatus, useEcho } from '@laravel/echo-vue'
import { useDeviceStore } from '@/stores/deviceStore'
import { useAlertStore } from '@/stores/alertStore'
import type { ConnectionStatus } from 'laravel-echo'
import type { DeviceAlert, DeviceLog } from '@/types/models'

// Track active subscriptions for cleanup
const activeSubscriptions: Array<() => void> = []

function setupUserChannel(userId: number) {
    const deviceStore = useDeviceStore()
    const alertStore = useAlertStore()

    const sub = useEcho(`user.${userId}`, [
        'device.status.updated',
        'device.alert.created',
        'device.log.created',
    ], (payload: any) => {
        if (payload.type === 'device.status.updated') {
            deviceStore.updateDeviceFromBroadcast(payload)
        } else if (payload.type === 'device.alert.created') {
            handleAlertCreated(payload)
        } else if (payload.type === 'device.log.created') {
            deviceStore.updateLatestLog(payload)
        }
    })

    activeSubscriptions.push(sub.leaveChannel)

    function handleAlertCreated(payload: {
        id: number
        device_id: number
        type: string
        type_label: string
        message: string
        severity: string
        data: Record<string, unknown> | null
        created_at: string
    }) {
        const alert = {
            id: payload.id,
            device_id: payload.device_id,
            type: payload.type as DeviceAlert['type'],
            type_label: payload.type_label,
            message: payload.message,
            severity: payload.severity as DeviceAlert['severity'],
            severity_label: '',
            severity_color: '',
            data: payload.data,
            resolved_at: null,
            created_at: payload.created_at,
        } as DeviceAlert

        alertStore.pushAlert(alert)
        deviceStore.pushAlert(alert)
    }
}

function subscribeToDevice(deviceId: number) {
    const deviceStore = useDeviceStore()
    const alertStore = useAlertStore()

    const sub = useEcho(`device.${deviceId}`, [
        'device.status.updated',
        'device.alert.created',
        'device.log.created',
    ], (payload: any) => {
        if (payload.type === 'device.status.updated') {
            deviceStore.updateDeviceFromBroadcast(payload)
        } else if (payload.type === 'device.alert.created') {
            const alert = {
                id: payload.id,
                device_id: payload.device_id,
                type: payload.type as DeviceAlert['type'],
                type_label: payload.type_label,
                message: payload.message,
                severity: payload.severity as DeviceAlert['severity'],
                severity_label: '',
                severity_color: '',
                data: payload.data,
                resolved_at: null,
                created_at: payload.created_at,
            } as DeviceAlert
            alertStore.pushAlert(alert)
            deviceStore.pushAlert(alert)
        } else if (payload.type === 'device.log.created') {
            deviceStore.updateLatestLog(payload)
        }
    })

    activeSubscriptions.push(sub.leaveChannel)
}

function unsubscribeFromDevice(deviceId: number) {
    useEcho(`device.${deviceId}`, [], () => {}).leaveChannel()
}

export function useBroadcast() {
    const page = usePage()
    const connectionStatus = useConnectionStatus()
    const userId = page.props.auth?.id as number | undefined

    onMounted(() => {
        if (userId) {
            setupUserChannel(userId)
        }
    })

    onUnmounted(() => {
        // Clean up all active subscriptions
        activeSubscriptions.forEach(cleanup => cleanup())
        activeSubscriptions.length = 0
    })

    return {
        connectionStatus,
        subscribeToDevice,
        unsubscribeFromDevice,
    }
}
