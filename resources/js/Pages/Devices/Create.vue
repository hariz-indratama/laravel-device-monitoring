<script setup lang="ts">
import { ref } from "vue"
import { usePage, useForm, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import type { Outlet } from "@/types/models";

const page = usePage<{ outlets: Outlet[] }>();
const form = useForm({
    name: "",
    serial_number: "",
    type: "game_console",
    outlet_id: "",
});

const showConfirmModal = ref(false);

function openConfirm() {
    // Basic validation before showing modal
    if (!form.name.trim()) {
        return;
    }
    showConfirmModal.value = true;
}

function submit() {
    form.post('/devices', {
        onError: (errors) => {
            console.error('Device create error:', errors);
        },
    });
}
</script>

<template>
    <AppLayout title="Add Device">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6">
                <Link href="/devices" class="text-gray-400 hover:text-gray-600"
                    >← Devices</Link
                >
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-700">Add Device</span>
            </div>

            <form
                @submit.prevent="openConfirm"
                class="bg-white rounded-xl border p-6 space-y-6"
            >
                <!-- Header -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Add Device
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Tambahkan device baru di bawah ini.
                    </p>
                </div>

                <!-- Device Info -->
                <div class="space-y-4">
                    <div>
                        <Label>Device Name *</Label>
                        <Input
                            v-model="form.name"
                            placeholder="PS5 Ruang Arcade Utama"
                            required
                        />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div>
                        <Label>Serial Number *</Label>
                        <Input
                            v-model="form.serial_number"
                            placeholder="SN-ABC123"
                            required
                        />
                        <p
                            v-if="form.errors.serial_number"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.serial_number }}
                        </p>
                    </div>
                    <div>
                        <Label>Outlet *</Label>
                        <select
                            v-model="form.outlet_id"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 cursor-pointer"
                        >
                            <option value="" disabled>Select outlet</option>
                            <option v-for="outlet in page.props.outlets" :key="outlet.id" :value="outlet.id">
                                {{ outlet.name }}
                            </option>
                        </select>
                        <p
                            v-if="form.errors.outlet_id"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.outlet_id }}
                        </p>
                    </div>
                    <div>
                        <Label>Device Type *</Label>
                        <select
                            v-model="form.type"
                            class="w-full h-10 px-3 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 cursor-pointer"
                        >
                            <option value="game_console">Game Console</option>
                            <option value="arcade">Arcade</option>
                        </select>
                        <p
                            v-if="form.errors.type"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.type }}
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end gap-3 mt-8 pt-4 border-t">
                    <Link href="/devices">
                        <Button variant="secondary" type="button"
                            >Cancel</Button
                        >
                    </Link>
                    <Button
                        type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Saving..." : "Save Device" }}
                    </Button>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <ConfirmModal
            v-model:open="showConfirmModal"
            title="Tambah Device Baru?"
            :description="`Device '${form.name}' dengan serial number '${form.serial_number}' akan ditambahkan. Lanjutkan?`"
            confirm-text="Ya, Tambahkan"
            cancel-text="Batal"
            variant="success"
            :loading="form.processing"
            @confirm="submit"
        />
    </AppLayout>
</template>
