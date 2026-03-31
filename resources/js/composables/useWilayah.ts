import { ref } from 'vue'
import type { Ref } from 'vue'

export interface WilayahOption {
    kode: string
    nama: string
}

interface Cache {
    [key: string]: WilayahOption[]
}

// ── Module-level cache (shared across all composable instances) ──────────────
const cache: Cache = {}

async function fetchOptions<T extends WilayahOption>(url: string): Promise<T[]> {
    if (cache[url]) return cache[url] as T[]
    const res = await fetch(url)
    if (!res.ok) return []
    const json = await res.json()
    cache[url] = json.data ?? []
    return cache[url] as T[]
}

export function useWilayah() {
    // ── Options lists ──────────────────────────────────────────────────────────
    const provinces: Ref<WilayahOption[]> = ref([])
    const cities: Ref<WilayahOption[]> = ref([])
    const districts: Ref<WilayahOption[]> = ref([])
    const villages: Ref<WilayahOption[]> = ref([])

    // ── Selected values (bind to v-select via v-model) ───────────────────────
    const selectedProvince: Ref<WilayahOption | null> = ref(null)
    const selectedCity: Ref<WilayahOption | null> = ref(null)
    const selectedDistrict: Ref<WilayahOption | null> = ref(null)
    const selectedVillage: Ref<WilayahOption | null> = ref(null)

    // ── Loading states ────────────────────────────────────────────────────────
    const loadingCities = ref(false)
    const loadingDistricts = ref(false)
    const loadingVillages = ref(false)

    // ── Load all provinces on init ───────────────────────────────────────────
    async function loadProvinces() {
        provinces.value = await fetchOptions<WilayahOption>('/api/v1/wilayah/provinces')
    }

    // ── Cascading: province → cities ─────────────────────────────────────────
    async function onProvinceChange(province: WilayahOption | null) {
        selectedCity.value = null
        selectedDistrict.value = null
        selectedVillage.value = null
        districts.value = []
        villages.value = []

        if (!province) {
            cities.value = []
            return
        }

        loadingCities.value = true
        try {
            cities.value = await fetchOptions<WilayahOption>(
                `/api/v1/wilayah/cities?province_kode=${province.kode}`
            )
        } finally {
            loadingCities.value = false
        }
    }

    // ── Cascading: city → districts ──────────────────────────────────────────
    async function onCityChange(city: WilayahOption | null) {
        selectedDistrict.value = null
        selectedVillage.value = null
        villages.value = []

        if (!city) {
            districts.value = []
            return
        }

        loadingDistricts.value = true
        try {
            districts.value = await fetchOptions<WilayahOption>(
                `/api/v1/wilayah/districts?city_kode=${city.kode}`
            )
        } finally {
            loadingDistricts.value = false
        }
    }

    // ── Cascading: district → villages ───────────────────────────────────────
    async function onDistrictChange(district: WilayahOption | null) {
        selectedVillage.value = null

        if (!district) {
            villages.value = []
            return
        }

        loadingVillages.value = true
        try {
            villages.value = await fetchOptions<WilayahOption>(
                `/api/v1/wilayah/villages?district_kode=${district.kode}`
            )
        } finally {
            loadingVillages.value = false
        }
    }

    // ── Set initial values (edit mode) ───────────────────────────────────────
    async function setInitial(
        provinceName: string,
        cityName: string,
        districtName: string,
        villageName: string
    ) {
        await loadProvinces()

        if (provinceName) {
            const found = provinces.value.find(p => p.nama === provinceName)
            if (found) {
                selectedProvince.value = found
                await onProvinceChange(found)
            }
        }
        if (cityName) {
            const found = cities.value.find(c => c.nama === cityName)
            if (found) {
                selectedCity.value = found
                await onCityChange(found)
            }
        }
        if (districtName) {
            const found = districts.value.find(d => d.nama === districtName)
            if (found) {
                selectedDistrict.value = found
                await onDistrictChange(found)
            }
        }
        if (villageName) {
            const found = villages.value.find(v => v.nama === villageName)
            if (found) selectedVillage.value = found
        }
    }

    // ── Clear all selections ─────────────────────────────────────────────────
    function clearAll() {
        selectedProvince.value = null
        selectedCity.value = null
        selectedDistrict.value = null
        selectedVillage.value = null
        provinces.value = []
        cities.value = []
        districts.value = []
        villages.value = []
    }

    return {
        // Options
        provinces,
        cities,
        districts,
        villages,
        // Selected
        selectedProvince,
        selectedCity,
        selectedDistrict,
        selectedVillage,
        // Loading
        loadingCities,
        loadingDistricts,
        loadingVillages,
        // Actions
        loadProvinces,
        onProvinceChange,
        onCityChange,
        onDistrictChange,
        setInitial,
        clearAll,
    }
}
