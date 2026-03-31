<script setup lang="ts">
import { onMounted } from "vue";
import { useForm, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Label } from "@/Components/ui/label";
import { Input } from "@/Components/ui/input";
import { Button } from "@/Components/ui/button";
import { useWilayah } from "@/composables/useWilayah";
import WilayahSelect from "@/Components/ui/WilayahSelect.vue";

const form = useForm({
    name: "",
    address: "",
    phone: "",
    email: "",
    provinsi: "",
    kota: "",
    kecamatan: "",
    desa: "",
    kode_pos: "",
});

// Strip non-numeric characters on input
function filterNumeric(value: string) {
    return value.replace(/\D/g, "");
}

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
    loadProvinces,
    onProvinceChange,
    onCityChange,
    onDistrictChange,
} = useWilayah();

onMounted(() => {
    loadProvinces();
});
</script>

<template>
    <AppLayout title="Add Outlet">
        <div class="max-w-2xl mx-auto">
            <div class="flex items-center gap-3 mb-6">
                <Link href="/outlets" class="text-gray-400 hover:text-gray-600"
                    >← Outlets</Link
                >
                <span class="text-gray-300">/</span>
                <span class="font-medium text-gray-700">Add Outlet</span>
            </div>

            <form
                @submit.prevent="form.post('/outlets')"
                class="bg-white rounded-xl border p-6 space-y-6"
            >
                <!-- Header -->
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        Informasi Outlet
                    </h2>
                    <p class="text-sm text-gray-500 mt-0.5">
                        Lengkapi data outlet di bawah ini.
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
                            <Input
                                v-model="form.name"
                                placeholder="Gedung A"
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
                            <Label>Address</Label>
                            <Input
                                v-model="form.address"
                                placeholder="Jl. Sudirman No. 1"
                            />
                        </div>
                        <div>
                            <Label>Phone</Label>
                            <Input
                                v-model="form.phone"
                                placeholder="021123456"
                                inputmode="numeric"
                                @input="form.phone = filterNumeric(($event.target as HTMLInputElement).value)"
                            />
                        </div>
                        <div>
                            <Label>Email</Label>
                            <Input
                                v-model="form.email"
                                type="email"
                                placeholder="outlet@example.com"
                            />
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
                            <Input
                                v-model="form.kode_pos"
                                placeholder="12345"
                                inputmode="numeric"
                                @input="form.kode_pos = filterNumeric(($event.target as HTMLInputElement).value)"
                            />
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
                        :disabled="form.processing"
                    >
                        {{ form.processing ? "Saving..." : "Save Outlet" }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
