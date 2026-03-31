<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { ChevronDown, Check, Search, X } from 'lucide-vue-next'

interface Option {
    kode: string
    nama: string
}

const props = withDefaults(defineProps<{
    modelValue: Option | null
    options: Option[]
    placeholder?: string
    disabled?: boolean
    searchable?: boolean
}>(), {
    placeholder: 'Pilih...',
    disabled: false,
    searchable: true,
})

const emit = defineEmits<{
    'update:modelValue': [value: Option | null]
}>()

const open = ref(false)
const search = ref('')
const triggerRef = ref<HTMLElement>()
const dropdownRef = ref<HTMLElement>()

const filteredOptions = computed(() => {
    if (!search.value.trim()) return props.options
    const q = search.value.toLowerCase()
    return props.options.filter(o => o.nama.toLowerCase().includes(q))
})

const displayLabel = computed(() => props.modelValue?.nama ?? '')

function toggle() {
    if (props.disabled) return
    open.value = !open.value
    if (open.value) search.value = ''
}

function select(option: Option) {
    emit('update:modelValue', option)
    open.value = false
    search.value = ''
}

function clear(e: Event) {
    e.stopPropagation()
    emit('update:modelValue', null)
}

function onClickOutside(e: MouseEvent) {
    if (!open.value) return
    if (triggerRef.value?.contains(e.target as Node)) return
    if (dropdownRef.value?.contains(e.target as Node)) return
    open.value = false
    search.value = ''
}

function onKeydown(e: KeyboardEvent) {
    if (e.key === 'Escape') {
        open.value = false
        search.value = ''
    }
}

onMounted(() => {
    document.addEventListener('mousedown', onClickOutside)
    document.addEventListener('keydown', onKeydown)
})
onBeforeUnmount(() => {
    document.removeEventListener('mousedown', onClickOutside)
    document.removeEventListener('keydown', onKeydown)
})
</script>

<template>
    <div class="relative w-full">
        <!-- Trigger -->
        <button
            ref="triggerRef"
            type="button"
            class="relative flex w-full items-center justify-between rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-left transition-colors focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:ring-offset-2"
            :class="{
                'opacity-50 cursor-not-allowed pointer-events-none': disabled,
                'border-emerald-400 ring-2 ring-emerald-500/20': open,
            }"
            @click="toggle"
        >
            <span :class="displayLabel ? 'text-gray-700' : 'text-gray-400'">
                {{ displayLabel || placeholder }}
            </span>
            <div class="flex items-center gap-1">
                <button
                    v-if="modelValue && !disabled"
                    type="button"
                    class="p-0.5 rounded hover:bg-gray-100 text-gray-400"
                    @click="clear"
                >
                    <X class="w-3.5 h-3.5" />
                </button>
                <ChevronDown
                    class="w-4 h-4 text-gray-400 transition-transform"
                    :class="{ 'rotate-180': open }"
                />
            </div>
        </button>

        <!-- Dropdown -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-if="open"
                ref="dropdownRef"
                class="absolute z-50 mt-1 w-full rounded-lg border border-gray-200 bg-white shadow-lg"
            >
                <!-- Search -->
                <div v-if="searchable" class="p-2 border-b border-gray-100">
                    <div class="relative">
                        <Search class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400" />
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Cari..."
                            class="w-full h-8 pl-8 pr-3 rounded-md border border-gray-200 text-sm text-gray-700 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400"
                        />
                    </div>
                </div>

                <!-- Options -->
                <ul class="max-h-60 overflow-y-auto p-1">
                    <li
                        v-for="option in filteredOptions"
                        :key="option.kode"
                        class="flex items-center justify-between px-3 py-2 text-sm rounded-md cursor-pointer transition-colors"
                        :class="
                            modelValue?.kode === option.kode
                                ? 'bg-emerald-50 text-emerald-700 font-medium'
                                : 'text-gray-700 hover:bg-gray-50'
                        "
                        @click="select(option)"
                    >
                        <span class="truncate">{{ option.nama }}</span>
                        <Check
                            v-if="modelValue?.kode === option.kode"
                            class="w-4 h-4 text-emerald-600 shrink-0"
                        />
                    </li>

                    <li
                        v-if="filteredOptions.length === 0"
                        class="px-3 py-4 text-sm text-center text-gray-400"
                    >
                        Tidak ditemukan
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>
