<script setup lang="ts">
import { ref } from "vue"
import { router, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import DeviceStatusBadge from "@/Components/device/DeviceStatusBadge.vue";
import AlertItem from "@/Components/device/AlertItem.vue";
import { Button } from "@/Components/ui/button";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import { Activity, MapPin, Clock, Calendar } from "lucide-vue-next";

// Inertia v2 pass data sebagai component props
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const props = defineProps<{
    device?: any;
    logs?: any[];
    logs_meta?: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    alerts?: any[];
    formatted_uptime?: string;
    active_alerts_count?: number;
}>();

const device = props.device ?? {};
const logs = props.logs ?? [];
const alerts = props.alerts ?? [];
const formatted_uptime = props.formatted_uptime || "—";
const active_alerts_count = props.active_alerts_count ?? 0;
const meta = props.logs_meta ?? { current_page: 1, last_page: 1, total: 0, from: 0, to: 0 };

const showToggleModal = ref(false);
const toggling = ref(false);

function openToggleConfirm() {
    showToggleModal.value = true;
}

function confirmToggle() {
    toggling.value = true;
    router.patch(`/devices/${device.id}/toggle`, {}, {
        onFinish: () => { toggling.value = false },
    });
}

function goToPage(page: number) {
    if (page < 1 || page > meta.last_page) return;
    router.get(`/devices/${device.id}`, { logs_page: page }, { preserveScroll: true });
}

function formatDate(dateStr: string | undefined | null) {
    if (!dateStr) return "—";
    return new Date(dateStr).toLocaleString("id-ID", {
        dateStyle: "medium",
        timeStyle: "short",
    });
}
</script>

<template>
    <AppLayout title="Device Detail">

        <div class="space-y-6">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-3">
                <Link href="/devices" class="text-gray-400 hover:text-gray-600"
                    >← Devices</Link
                >
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-700">{{ device.name }}</span>
            </div>

            <!-- Status On/Off Hero Card -->
            <div
                class="relative overflow-hidden rounded-2xl border p-6"
                :class="
                    device.status === 'online'
                        ? 'bg-emerald-50 border-emerald-200'
                        : 'bg-gray-50 border-gray-200'
                "
            >
                <div
                    class="absolute top-0 right-0 w-64 h-64 rounded-full opacity-10 -translate-y-1/2 translate-x-1/4"
                    :class="device.is_on ? 'bg-emerald-500' : 'bg-gray-500'"
                />

                <div
                    class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                >
                    <!-- Left: Device Info -->
                    <div class="space-y-1">
                        <div class="flex items-center gap-3">
                            <h2 class="text-2xl font-bold text-gray-900">
                                {{ device.name }}
                            </h2>
                            <DeviceStatusBadge :status="device.status" />
                        </div>
                        <p class="text-gray-500 font-mono text-sm">
                            {{ device.serial_number }}
                        </p>
                        <div
                            class="flex items-center gap-4 mt-2 text-sm text-gray-500"
                        >
                            <div class="flex items-center gap-1.5">
                                <MapPin class="w-4 h-4 text-purple-500" />
                                <span>{{ device.outlet?.name || "—" }}</span>
                            </div>
                            <div
                                v-if="device.outlet?.address"
                                class="flex items-center gap-1.5"
                            >
                                <span class="text-gray-300">|</span>
                                <span>{{ device.outlet?.address }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Status Display -->
                    <div
                        class="flex flex-col items-center justify-center p-6 rounded-xl border-2 min-w-35"
                        :class="
                            device.is_on
                                ? 'border-emerald-400 bg-emerald-50'
                                : 'border-gray-300 bg-white'
                        "
                    >
                        <Activity
                            class="w-8 h-8 mb-2"
                            :class="
                                device.status === 'online'
                                    ? 'text-emerald-600'
                                    : device.status === 'offline'
                                      ? 'text-red-600'
                                      : 'text-gray-500'
                            "
                        />
                        <span
                            class="text-4xl font-black tracking-tight"
                            :class="
                                device.status === 'online'
                                    ? 'text-emerald-600'
                                    : device.status === 'offline'
                                      ? 'text-red-600'
                                      : 'text-gray-500'
                            "
                        >
                            {{ device.is_on ? "ON" : "OFF" }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Info Grid -->
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <!-- Uptime -->
                <div
                    class="bg-white rounded-xl border p-5 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2.5 rounded-lg bg-blue-50 text-blue-600">
                            <Clock class="w-5 h-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs text-gray-400 uppercase tracking-wide"
                            >
                                Uptime
                            </p>
                            <p class="text-2xl font-bold text-blue-700 mt-0.5">
                                {{ formatted_uptime }}
                            </p>
                        </div>
                    </div>
                    <div
                        class="text-xs text-gray-400 flex items-center gap-1.5"
                    >
                        <Calendar class="w-3.5 h-3.5" />
                        Started:
                        {{
                            device.uptime_started_at
                                ? formatDate(device.uptime_started_at)
                                : "—"
                        }}
                    </div>
                </div>

                <!-- Outlet Location -->
                <div
                    class="bg-white rounded-xl border p-5 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div
                            class="p-2.5 rounded-lg bg-purple-50 text-purple-600"
                        >
                            <MapPin class="w-5 h-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs text-gray-400 uppercase tracking-wide"
                            >
                                Lokasi Outlet
                            </p>
                            <p
                                class="text-lg font-bold text-purple-700 mt-0.5 truncate"
                            >
                                {{ device.outlet?.name || "—" }}
                            </p>
                        </div>
                    </div>
                    <div
                        v-if="device.outlet?.address"
                        class="text-xs text-gray-400"
                    >
                        {{ device.outlet?.address }}
                    </div>
                </div>

                <!-- Device Info -->
                <div
                    class="bg-white rounded-xl border p-5 hover:shadow-md transition-shadow"
                >
                    <div class="flex items-center gap-3 mb-3">
                        <div class="p-2.5 rounded-lg bg-gray-100 text-gray-600">
                            <Activity class="w-5 h-5" />
                        </div>
                        <div>
                            <p
                                class="text-xs text-gray-400 uppercase tracking-wide"
                            >
                                Device Info
                            </p>
                            <p class="text-lg font-bold text-gray-700 mt-0.5">
                                {{ device.type || "—" }}
                            </p>
                        </div>
                    </div>
                    <div class="text-xs text-gray-400">
                        Created: {{ formatDate(device.created_at) }}
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-3">
                <Link :href="`/devices/${device.id}/edit`">
                    <Button variant="outline" size="sm">Edit Device</Button>
                </Link>
                <Button
                    size="sm"
                    :variant="device.is_on ? 'outline' : 'default'"
                    :class="
                        device.is_on
                            ? 'border-amber-300 text-amber-700 hover:bg-amber-50'
                            : 'bg-emerald-600 hover:bg-emerald-700 text-white border-0'
                    "
                    :loading="toggling"
                    @click="openToggleConfirm"
                >
                    {{ device.is_on ? "Set Offline" : "Set Online" }}
                </Button>
            </div>

            <!-- Recent Logs -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-semibold text-gray-900">Recent Logs</h3>
                    <span class="text-xs text-gray-400">
                        Showing {{ meta.from }}–{{ meta.to }} of {{ meta.total }} entries
                    </span>
                </div>
                <div class="bg-white rounded-xl border overflow-hidden">
                    <table class="w-full text-sm">
                        <thead
                            class="bg-gray-50 text-gray-500 text-xs uppercase"
                        >
                            <tr>
                                <th class="px-4 py-3 text-left">Time</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-left">Start Uptime</th>
                                <th class="px-4 py-3 text-left">End Uptime</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr
                                v-for="log in logs"
                                :key="log.id"
                                class="hover:bg-gray-50"
                            >
                                <td class="px-4 py-2.5 text-gray-500">
                                    {{ formatDate(log?.logged_at) }}
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold"
                                        :class="log?.status === 'on'
                                            ? 'bg-emerald-100 text-emerald-700'
                                            : 'bg-red-100 text-red-600'"
                                    >
                                        {{ log?.status === 'on' ? 'ON' : 'OFF' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5 text-gray-500">
                                    {{ log?.start_uptime ? formatDate(log?.start_uptime) : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-gray-500">
                                    {{ log?.end_uptime ? formatDate(log?.end_uptime) : '—' }}
                                </td>
                            </tr>
                            <tr v-if="!logs.length">
                                <td
                                    colspan="4"
                                    class="px-4 py-6 text-center text-gray-400"
                                >
                                    No logs yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div
                        v-if="meta.last_page > 1"
                        class="flex items-center justify-between px-4 py-3 border-t border-gray-100 bg-gray-50"
                    >
                        <button
                            class="px-3 py-1.5 text-sm rounded-md border border-gray-200 text-gray-600 hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            :disabled="meta.current_page <= 1"
                            @click="goToPage(meta.current_page - 1)"
                        >
                            ← Prev
                        </button>

                        <div class="flex items-center gap-1">
                            <template v-for="p in meta.last_page" :key="p">
                                <button
                                    v-if="
                                        p === 1 ||
                                        p === meta.last_page ||
                                        Math.abs(p - meta.current_page) <= 1
                                    "
                                    class="w-8 h-8 text-sm rounded-md transition-colors"
                                    :class="
                                        p === meta.current_page
                                            ? 'bg-emerald-600 text-white font-semibold'
                                            : 'text-gray-600 hover:bg-gray-100'
                                    "
                                    @click="goToPage(p)"
                                >
                                    {{ p }}
                                </button>
                                <span
                                    v-else-if="
                                        Math.abs(p - meta.current_page) === 2
                                    "
                                    class="px-1 text-gray-400"
                                >
                                    …
                                </span>
                            </template>
                        </div>

                        <button
                            class="px-3 py-1.5 text-sm rounded-md border border-gray-200 text-gray-600 hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed transition-colors"
                            :disabled="meta.current_page >= meta.last_page"
                            @click="goToPage(meta.current_page + 1)"
                        >
                            Next →
                        </button>
                    </div>
                </div>
            </div>

            <!-- Alerts -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    Alerts
                    <span
                        v-if="active_alerts_count > 0"
                        class="ml-2 px-2 py-0.5 bg-red-100 text-red-700 rounded-full text-xs font-bold"
                    >
                        {{ active_alerts_count }}
                    </span>
                </h3>
                <div class="space-y-2">
                    <AlertItem
                        v-for="alert in alerts"
                        :key="alert.id"
                        :alert="alert"
                    />
                    <div
                        v-if="!alerts.length"
                        class="text-center py-6 text-gray-400 text-sm"
                    >
                        No alerts.
                    </div>
                </div>
            </div>
        </div>

        <!-- Toggle Confirmation Modal -->
        <ConfirmModal
            v-model:open="showToggleModal"
            :title="device.is_on ? 'Set Device Offline?' : 'Set Device Online?'"
            :description="device.is_on
                ? `Device '${device.name}' akan设置为 offline. Uptime saat ini: ${formatted_uptime}. Lanjutkan?`
                : `Device '${device.name}' akan设置为 online dan mulai tracking uptime. Lanjutkan?`"
            :confirm-text="device.is_on ? 'Ya, Set Offline' : 'Ya, Set Online'"
            cancel-text="Batal"
            :variant="device.is_on ? 'warning' : 'success'"
            :loading="toggling"
            @confirm="confirmToggle"
        />
    </AppLayout>
</template>
