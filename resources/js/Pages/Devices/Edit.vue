<script setup lang="ts">
import { ref } from "vue"
import { useForm, usePage, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import ConfirmModal from "@/Components/ConfirmModal.vue";

const page = usePage<
    {
        device: any;
    } & import("@inertiajs/core").PageProps
>();

const form = useForm({
    name: page.props.device.name,
    serial_number: page.props.device.serial_number,
    type: page.props.device.type,
});

const showConfirmModal = ref(false);

function openConfirm() {
    showConfirmModal.value = true;
}

function submit() {
    form.patch(`/devices/${page.props.device.id}`);
}
</script>

<template>
    <AppLayout title="Edit Device">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6">
                <Link href="/devices" class="text-gray-400 hover:text-gray-600"
                    >← Devices</Link
                >
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-700">{{
                    page.props.device.name
                }}</span>
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-700">Edit</span>
            </div>

            <form
                @submit.prevent="openConfirm"
                class="bg-white rounded-xl border p-6 space-y-5"
            >
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <Label>Device Name *</Label>
                        <Input v-model="form.name" required />
                        <p
                            v-if="form.errors.name"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.name }}
                        </p>
                    </div>
                    <div>
                        <Label>Serial Number *</Label>
                        <Input v-model="form.serial_number" required />
                        <p
                            v-if="form.errors.serial_number"
                            class="mt-1 text-xs text-red-600"
                        >
                            {{ form.errors.serial_number }}
                        </p>
                    </div>
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

                <div class="flex justify-end gap-3 pt-2">
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
                        {{ form.processing ? "Saving..." : "Update Device" }}
                    </Button>
                </div>
            </form>
        </div>

        <!-- Confirmation Modal -->
        <ConfirmModal
            v-model:open="showConfirmModal"
            title="Update Device?"
            :description="`Perubahan pada device '${form.name}' akan disimpan. Lanjutkan?`"
            confirm-text="Ya, Update"
            cancel-text="Batal"
            variant="success"
            :loading="form.processing"
            @confirm="submit"
        />
    </AppLayout>
</template>
