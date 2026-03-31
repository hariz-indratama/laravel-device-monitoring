<script setup lang="ts">
import { Link, usePage, router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import DeviceCard from "@/Components/device/DeviceCard.vue";
import { Button } from "@/Components/ui/button";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import { ref, computed } from "vue";

const page = usePage<
    {
        outlet: {
            id: number;
            name: string;
            address: string | null;
            phone: string | null;
            devices: Record<string, unknown>[];
            kode_pos: string | null;
        };
        availableDevices: Array<{
            id: number;
            name: string;
            serial_number: string;
            type: string;
            status: string;
        }>;
        flash?: { success?: string; error?: string };
    } & import("@inertiajs/core").PageProps
>();

const outlet = computed(() => page.props.outlet);
const availableDevices = computed(() => page.props.availableDevices ?? []);

// ── Add Device Modal ───────────────────────────────────────────────────────────
const showAddModal = ref(false);
const adding = ref(false);
const selectedDeviceId = ref<string>("");
const assignedAt = ref<string>("");

// ── Delete Outlet Modal ────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const deleting = ref(false);

function openAddModal() {
    selectedDeviceId.value = "";
    assignedAt.value = "";
    showAddModal.value = true;
}

function closeAddModal() {
    if (adding.value) return;
    showAddModal.value = false;
}

function submitAddDevice() {
    if (!selectedDeviceId.value) return;
    adding.value = true;

    const body: Record<string, string> = {
        device_id: selectedDeviceId.value,
    };
    if (assignedAt.value) {
        body.assigned_at = assignedAt.value;
    }

    router.post(`/outlets/${outlet.value.id}/devices`, body, {
        onFinish: () => {
            adding.value = false;
            showAddModal.value = false;
        },
    });
}

// ── Unassign Device ────────────────────────────────────────────────────────────
function unassignDevice(deviceId: number, deviceName: string) {
    if (!confirm(`Remove "${deviceName}" from ${outlet.value.name}?`)) return;
    router.delete(`/outlets/${outlet.value.id}/devices/${deviceId}`);
}

function deleteOutlet() {
    deleting.value = true;
    router.delete(`/outlets/${outlet.value.id}`, {
        onFinish: () => {
            deleting.value = false;
            showDeleteModal.value = false;
        },
    });
}
</script>

<template>
    <AppLayout title="Outlet Detail">
        <div class="space-y-6">
            <!-- Breadcrumb -->
            <div class="flex items-center gap-3">
                <Link href="/outlets" class="text-gray-400 hover:text-gray-600"
                    >← Outlets</Link
                >
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-700">{{ outlet.name }}</span>
            </div>

            <!-- Outlet Info Card -->
            <div class="bg-white rounded-xl border p-6">
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">
                            {{ outlet.name }}
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">
                            {{ outlet.address || "—" }}
                        </p>
                        <p class="text-gray-500 text-sm">
                            {{ outlet.phone || "—" }}
                        </p>
                        <p class="text-gray-500 text-sm">
                            {{ outlet.kode_pos || "—" }}
                        </p>
                    </div>
                    <div class="flex gap-2">
                        <button
                            type="button"
                            class="inline-flex items-center px-3 py-1.5 text-sm border border-red-200 text-red-600 rounded-md hover:bg-red-50 transition-colors"
                            @click="showDeleteModal = true"
                        >
                            Delete
                        </button>
                        <Link :href="`/outlets/${outlet.id}/edit`">
                            <Button variant="outline" size="sm">Edit</Button>
                        </Link>
                        <Button
                            size="sm"
                            class="bg-emerald-600 hover:bg-emerald-700"
                            @click="openAddModal"
                        >
                            + Add Device
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Devices Section -->
            <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-3">
                    Devices at this outlet
                    <span
                        v-if="outlet.devices?.length"
                        class="ml-2 text-sm font-normal text-gray-400"
                    >
                        ({{ outlet.devices.length }})
                    </span>
                </h3>
                <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div
                        v-for="device in outlet.devices"
                        :key="device.id as number"
                        class="relative"
                    >
                        <DeviceCard
                            :device="device as any"
                            @click="
                                (d: any) => $inertia.get(`/devices/${d.id}`)
                            "
                        />
                        <!-- Remove button -->
                        <button
                            type="button"
                            class="absolute top-2 right-2 z-10 w-6 h-6 rounded-full bg-white border border-red-200 text-red-500 flex items-center justify-center opacity-0 group-hover:opacity-100 hover:bg-red-50 transition-all text-xs"
                            title="Remove from outlet"
                            @click.stop="
                                unassignDevice(
                                    device.id as number,
                                    (device as any).name,
                                )
                            "
                        >
                            ✕
                        </button>
                    </div>
                    <div
                        v-if="!outlet.devices?.length"
                        class="col-span-full text-center py-12 text-gray-400 text-sm rounded-xl border-2 border-dashed border-gray-200"
                    >
                        No devices at this outlet yet.
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Device Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showAddModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <!-- Backdrop -->
                    <div
                        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
                        @click="closeAddModal"
                    />

                    <!-- Modal -->
                    <div
                        class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden"
                    >
                        <!-- Header -->
                        <div
                            class="flex items-center justify-between px-6 py-4 border-b border-slate-100"
                        >
                            <div>
                                <h2 class="font-bold text-slate-900">
                                    Add Device
                                </h2>
                                <p class="text-xs text-slate-400 mt-0.5">
                                    Assign an existing device to this outlet
                                </p>
                            </div>
                            <button
                                type="button"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                                :disabled="adding"
                                @click="closeAddModal"
                            >
                                ✕
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-5 space-y-4">
                            <!-- Available Devices -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1.5"
                                    >Select Device *</label
                                >
                                <select
                                    v-model="selectedDeviceId"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 cursor-pointer"
                                >
                                    <option value="">
                                        -- Select device --
                                    </option>
                                    <option
                                        v-for="device in availableDevices"
                                        :key="device.id"
                                        :value="String(device.id)"
                                    >
                                        {{ device.name }} ({{
                                            device.serial_number
                                        }})
                                    </option>
                                </select>
                                <p
                                    v-if="availableDevices.length === 0"
                                    class="text-xs text-slate-400 mt-1"
                                >
                                    No unassigned devices available. Create a
                                    new device first.
                                </p>
                            </div>

                            <!-- Assignment Date -->
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 mb-1.5"
                                >
                                    Tanggal Penambahan
                                </label>
                                <input
                                    v-model="assignedAt"
                                    type="datetime-local"
                                    class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400"
                                />
                                <p class="text-xs text-slate-400 mt-1">
                                    Kosongkan untuk menggunakan tanggal hari ini
                                </p>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div
                            class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50"
                        >
                            <button
                                type="button"
                                class="h-9 px-4 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors disabled:opacity-50"
                                :disabled="adding"
                                @click="closeAddModal"
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                class="h-9 px-5 rounded-xl text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 transition-colors disabled:opacity-60 flex items-center gap-2"
                                :disabled="!selectedDeviceId || adding"
                                @click="submitAddDevice"
                            >
                                <svg
                                    v-if="adding"
                                    class="w-4 h-4 animate-spin"
                                    viewBox="0 0 24 24"
                                    fill="none"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    />
                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
                                    />
                                </svg>
                                {{ adding ? "Adding..." : "Add Device" }}
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <ConfirmModal
            v-model:open="showDeleteModal"
            title="Delete Outlet"
            description="Apakah Anda yakin ingin menghapus outlet ini?"
            confirm-text="Ya, Hapus"
            variant="destructive"
            :loading="deleting"
            @confirm="deleteOutlet"
        />
    </AppLayout>
</template>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: all 0.25s cubic-bezier(0.22, 1, 0.36, 1);
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
.modal-enter-from .relative,
.modal-leave-to .relative {
    transform: scale(0.95) translateY(8px);
}
</style>
