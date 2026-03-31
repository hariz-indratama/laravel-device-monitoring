<script setup lang="ts">
import { ref } from "vue";
import { usePage, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import DeviceCard from "@/Components/device/DeviceCard.vue";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/Components/ui/select";
import { Wifi, WifiOff } from "lucide-vue-next";
import type { Device } from "@/types/models";

const page = usePage<
    {
        devices: {
            data: Device[];
            current_page: number;
            last_page: number;
            per_page: number;
            total: number;
            first_page_url: string;
            last_page_url: string;
            next_page_url: string | null;
            prev_page_url: string | null;
            from: number | null;
            to: number | null;
            path: string;
        };
        filters: { search?: string; status?: string };
        stats: { total: number; online: number; offline: number };
    } & import("@inertiajs/core").PageProps
>();

const filters = ref(page.props.filters);
const stats = page.props.stats;

const statuses = [
    { value: "all", label: "All" },
    { value: "online", label: "ON" },
    { value: "offline", label: "OFF" },
    { value: "warning", label: "Warning" },
    { value: "maintenance", label: "Maintenance" },
];

function applyFilter(key: string, value: string) {
    filters.value[key] = value;
    const params: Record<string, string> = {};
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.status && filters.value.status !== "all") params.status = filters.value.status;
    window.location.href = `/devices?${new URLSearchParams(params)}`;
}
</script>

<template>
    <AppLayout title="Devices">
        <div class="space-y-4">
            <!-- Stats Bar -->
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-white rounded-xl border p-4 text-center">
                    <p class="text-xs text-gray-400 uppercase tracking-wide">Total</p>
                    <p class="text-2xl font-bold text-gray-700 mt-1">{{ stats.total }}</p>
                </div>
                <div class="bg-emerald-50 rounded-xl border border-emerald-200 p-4 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <Wifi class="w-3.5 h-3.5 text-emerald-600" />
                        <p class="text-xs text-emerald-600 uppercase tracking-wide font-medium">ON</p>
                    </div>
                    <p class="text-2xl font-bold text-emerald-700 mt-1">{{ stats.online }}</p>
                </div>
                <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-center">
                    <div class="flex items-center justify-center gap-1.5">
                        <WifiOff class="w-3.5 h-3.5 text-red-600" />
                        <p class="text-xs text-red-600 uppercase tracking-wide font-medium">OFF</p>
                    </div>
                    <p class="text-2xl font-bold text-red-700 mt-1">{{ stats.offline }}</p>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="flex flex-wrap gap-3 items-center">
                <Link href="/devices/create">
                    <Button class="bg-emerald-600 hover:bg-emerald-700"
                        >+ Add Device</Button
                    >
                </Link>

                <div class="flex items-center gap-2 flex-1 min-w-0">
                    <Input
                        v-model="filters.search"
                        placeholder="Search by name or serial number..."
                        class="max-w-xs"
                        @keyup.enter="
                            applyFilter('search', filters.search || '')
                        "
                    />
                    <Button
                        variant="secondary"
                        @click="applyFilter('search', filters.search || '')"
                        >Search</Button
                    >
                </div>

                <Select
                    v-model="filters.status"
                    class="w-40"
                    @update:modelValue="(v) => applyFilter('status', v)"
                >
                    <SelectTrigger class="w-40">
                        <SelectValue placeholder="Status" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="s in statuses"
                            :key="s.value"
                            :value="s.value"
                            >{{ s.label }}</SelectItem
                        >
                    </SelectContent>
                </Select>
            </div>

            <!-- Device Grid -->
            <div
                class="grid sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4"
            >
                <DeviceCard
                    v-for="device in page.props.devices.data"
                    :key="device.id"
                    :device="device"
                    @click="(d) => $inertia.get(`/devices/${d.id}`)"
                />
                <div
                    v-if="page.props.devices.data.length === 0"
                    class="col-span-full text-center py-16 text-gray-400"
                >
                    No devices found.
                </div>
            </div>

            <!-- Pagination -->
            <div
                v-if="page.props.devices.last_page > 1"
                class="flex justify-center gap-2"
            >
                <Link
                    v-for="p in page.props.devices.last_page"
                    :key="p"
                    :href="`/devices?page=${p}`"
                    :class="[
                        'px-3 py-1 rounded text-sm font-medium',
                        p === page.props.devices.current_page
                            ? 'bg-emerald-600 text-white'
                            : 'bg-white border text-gray-600 hover:bg-gray-50',
                    ]"
                    >{{ p }}</Link
                >
            </div>
        </div>
    </AppLayout>
</template>
