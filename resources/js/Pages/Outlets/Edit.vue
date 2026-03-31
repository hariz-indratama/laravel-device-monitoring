<script setup lang="ts">
import { ref, onMounted } from "vue";
import { usePage, Link, router } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import { useWilayah } from "@/composables/useWilayah";
import WilayahSelect from "@/Components/ui/WilayahSelect.vue";

const page = usePage<{ outlet: any } & import("@inertiajs/core").PageProps>();

const form = ref({
    name: page.props.outlet.name,
    address: page.props.outlet.address || "",
    phone: page.props.outlet.phone || "",
    email: page.props.outlet.email || "",
    provinsi: page.props.outlet.provinsi || "",
    kota: page.props.outlet.kota || "",
    kecamatan: page.props.outlet.kecamatan || "",
    desa: page.props.outlet.desa || "",
    kode_pos: page.props.outlet.kode_pos || "",
});

const {
    provinces,
    cities,
    districts,
    villages,
    selectedProvince,
    selectedCity,
    selectedDistrict,
    selectedVillage,
    loadingCities,
    loadingDistricts,
    loadingVillages,
    onProvinceChange,
    onCityChange,
    onDistrictChange,
    setInitial,
} = useWilayah();

// Strip non-numeric characters on input
function filterNumeric(value: string) {
    return value.replace(/\D/g, "");
}

onMounted(async () => {
    await setInitial(
        page.props.outlet.provinsi,
        page.props.outlet.kota,
        page.props.outlet.kecamatan,
        page.props.outlet.desa
    );
});

const errors = ref<Record<string, string>>({});
const processing = ref(false);
const showConfirmModal = ref(false);

function openConfirm() {
    showConfirmModal.value = true;
}

function submit() {
    processing.value = true;
    errors.value = {};
    router.patch(`/outlets/${page.props.outlet.id}`, form.value, {
        onError: (err) => {
            errors.value = err as Record<string, string>;
            processing.value = false;
        },
        onFinish: () => {
            processing.value = false;
            showConfirmModal.value = false;
        },
    });
}
</script>

<template>
    <AppLayout title="Edit Outlet">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6">
                <Link href="/outlets" class="text-gray-400 hover:text-gray-600"
                    >← Outlets</Link
                >
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-700">{{
                    page.props.outlet.name
                }}</span>
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-700">Edit</span>
            </div>

            <form
                @submit.prevent="openConfirm"
                class="bg-white rounded-xl border p-6 space-y-6"
            >
                <!-- Header -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Edit Outlet
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Perbarui data outlet di bawah ini.
                    </p>
                </div>

                <!-- Two Column Layout -->
                <div class="grid md:grid-cols-2 gap-6">
                    <!-- Left: Basic Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            Detail
                        </h3>
                        <div>
                            <Label>Outlet Name *</Label>
                            <Input v-model="form.name" required />
                            <p
                                v-if="errors.name"
                                class="mt-1 text-xs text-red-600"
                            >
                                {{ errors.name }}
                            </p>
                        </div>
                        <div>
                            <Label>Address</Label>
                            <Input v-model="form.address" />
                        </div>
                        <div>
                            <Label>Phone</Label>
                            <Input v-model="form.phone" inputmode="numeric" pattern="[0-9]*" />
                        </div>
                        <div>
                            <Label>Email</Label>
                            <Input v-model="form.email" type="email" />
                        </div>
                    </div>

                    <!-- Right: Location Info -->
                    <div class="space-y-4">
                        <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                            Lokasi
                        </h3>
                        <!-- Provinsi -->
                        <div>
                            <Label>Provinsi</Label>
                            <WilayahSelect
                                v-model="selectedProvince"
                                :options="provinces"
                                placeholder="Pilih Provinsi"
                                :searchable="true"
                                @update:model-value="(v) => { form.provinsi = v?.nama ?? ''; onProvinceChange(v); }"
                            />
                        </div>
                        <!-- Kabupaten/Kota -->
                        <div>
                            <Label>Kabupaten/Kota</Label>
                            <WilayahSelect
                                v-model="selectedCity"
                                :options="cities"
                                placeholder="Pilih Kabupaten/Kota"
                                :searchable="true"
                                :disabled="!selectedProvince || loadingCities"
                                @update:model-value="(v) => { form.kota = v?.nama ?? ''; onCityChange(v); }"
                            />
                        </div>
                        <!-- Kecamatan -->
                        <div>
                            <Label>Kecamatan</Label>
                            <WilayahSelect
                                v-model="selectedDistrict"
                                :options="districts"
                                placeholder="Pilih Kecamatan"
                                :searchable="true"
                                :disabled="!selectedCity || loadingDistricts"
                                @update:model-value="(v) => { form.kecamatan = v?.nama ?? ''; onDistrictChange(v); }"
                            />
                        </div>
                        <!-- Desa/Kelurahan -->
                        <div>
                            <Label>Desa/Kelurahan</Label>
                            <WilayahSelect
                                v-model="selectedVillage"
                                :options="villages"
                                placeholder="Pilih Desa/Kelurahan"
                                :searchable="true"
                                :disabled="!selectedDistrict || loadingVillages"
                                @update:model-value="(v) => { form.desa = v?.nama ?? ''; }"
                            />
                        </div>
                        <!-- Kode Pos -->
                        <div>
                            <Label>Kode Pos</Label>
                            <Input v-model="form.kode_pos" inputmode="numeric" pattern="[0-9]*" />
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div
                    class="flex justify-end gap-3 pt-4 border-t border-gray-100"
                >
                    <Link href="/outlets"
                        ><Button variant="secondary" type="button"
                            >Cancel</Button
                        ></Link
                    >
                    <Button
                        type="submit"
                        class="bg-emerald-600 hover:bg-emerald-700"
                        :disabled="processing"
                    >
                        {{ processing ? "Saving..." : "Update Outlet" }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>

    <ConfirmModal
        v-model:open="showConfirmModal"
        title="Update Outlet"
        description="Apakah Anda yakin ingin menyimpan perubahan outlet ini?"
        confirm-text="Ya, Simpan"
        :loading="processing"
        @confirm="submit"
    />
</template>
