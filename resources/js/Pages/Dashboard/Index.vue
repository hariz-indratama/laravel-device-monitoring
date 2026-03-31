<script setup lang="ts">
import { onMounted, onUnmounted } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import DeviceStats from "@/Components/device/DeviceStats.vue";
import DeviceCard from "@/Components/device/DeviceCard.vue";
import AlertItem from "@/Components/device/AlertItem.vue";
import { useWebsocket } from "@/composables/useWebsocket";
import { useDeviceStore } from "@/stores/deviceStore";
import { useAlertStore } from "@/stores/alertStore";
import type { DeviceStats as Stats, Device, DeviceAlert } from "@/types/models";
import type { PageProps } from "@inertiajs/core";

const page = usePage<
    {
        stats: Stats;
        devices: Device[];
        recentAlerts: DeviceAlert[];
    } & PageProps
>();

const deviceStore = useDeviceStore();
const alertStore = useAlertStore();
const userId = page.props.auth?.id ?? 0;

deviceStore.setStats(page.props.stats);
deviceStore.setDevices(page.props.devices);
alertStore.setAlerts(page.props.recentAlerts);

const { connect, disconnect } = useWebsocket(userId);
onMounted(() => connect());
onUnmounted(() => disconnect());
</script>

<template>
    <AppLayout title="Dashboard">
        <div class="space-y-6 sm:space-y-8">

            <!-- Stats row -->
            <div style="animation: fadeInUp 0.5s ease-out both;">
                <DeviceStats :stats="deviceStore.stats" />
            </div>

            <!-- Mobile: stacked layout | Desktop: 3-col grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Devices -->
                <div class="lg:col-span-2 order-1">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-1.5 h-5 rounded-full bg-emerald-500 inline-block shrink-0" />
                            <span>Devices</span>
                            <span class="hidden sm:inline text-xs font-normal text-gray-400">({{ deviceStore.devices.length }})</span>
                        </h2>
                        <Link
                            href="/devices"
                            class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1 transition-colors duration-200 group"
                        >
                            View all
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>

                    <TransitionGroup
                        name="card"
                        tag="div"
                        class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3"
                    >
                        <DeviceCard
                            v-for="device in deviceStore.devices.slice(0, 6)"
                            :key="device.id"
                            :device="device"
                            @click="(d) => $inertia.get(`/devices/${d.id}`)"
                        />
                    </TransitionGroup>

                    <div
                        v-if="deviceStore.devices.length === 0"
                        class="text-center py-12 rounded-2xl bg-gray-50 border border-dashed border-gray-200"
                    >
                        <p class="text-gray-400 text-sm">No devices found.</p>
                    </div>
                </div>

                <!-- Alerts -->
                <div class="order-2 lg:order-2">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-base font-bold text-gray-800 flex items-center gap-2">
                            <span class="w-1.5 h-5 rounded-full bg-rose-500 inline-block shrink-0" />
                            <span>Active Alerts</span>
                            <span
                                v-if="alertStore.alerts.length > 0"
                                class="hidden sm:inline-flex items-center justify-center w-5 h-5 rounded-full bg-rose-100 text-rose-600 text-[10px] font-bold"
                            >
                                {{ alertStore.alerts.length }}
                            </span>
                        </h2>
                        <Link
                            href="/alerts"
                            class="text-sm text-emerald-600 hover:text-emerald-700 font-semibold flex items-center gap-1 transition-colors duration-200 group"
                        >
                            View all
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </Link>
                    </div>

                    <TransitionGroup
                        name="alert"
                        tag="div"
                        class="space-y-2"
                    >
                        <AlertItem
                            v-for="alert in alertStore.alerts.slice(0, 8)"
                            :key="alert.id"
                            :alert="alert"
                            @resolve="alertStore.resolveAlert"
                        />
                    </TransitionGroup>

                    <div
                        v-if="alertStore.alerts.length === 0"
                        class="text-center py-8 rounded-2xl bg-emerald-50/60 border border-dashed border-emerald-200"
                    >
                        <svg class="w-8 h-8 mx-auto mb-2 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="text-emerald-600 text-sm font-medium">No active alerts</p>
                        <p class="text-emerald-400 text-xs mt-0.5">All systems are running normally</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(16px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Device card list transitions */
.card-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.card-leave-active {
    transition: all 0.3s ease-in;
    position: absolute;
}
.card-enter-from {
    opacity: 0;
    transform: scale(0.9) translateY(12px);
}
.card-leave-to {
    opacity: 0;
    transform: scale(0.9);
}
.card-move {
    transition: transform 0.4s ease;
}

/* Alert item list transitions */
.alert-enter-active {
    transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.alert-leave-active {
    transition: all 0.3s ease-in;
    position: absolute;
    width: calc(100% - 0rem);
}
.alert-enter-from {
    opacity: 0;
    transform: translateX(-16px);
}
.alert-leave-to {
    opacity: 0;
    transform: translateX(16px);
}
.alert-move {
    transition: transform 0.4s ease;
}
</style>
