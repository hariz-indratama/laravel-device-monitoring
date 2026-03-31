<script setup lang="ts">
import { ref, computed } from "vue";
import { router, Link } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/Components/ui/button";
import ConfirmModal from "@/Components/ConfirmModal.vue";
import { MapPin, PhoneIncoming, Cpu, ArrowRight, Trash2, Upload, Download, X, ChevronDown, Search } from "lucide-vue-next";

// Inertia v2: page props are passed as defineProps (no usePage needed)
const props = defineProps<{
    outlets: {
        data: Array<{
            id: number | string;
            name: string;
            address?: string;
            phone?: string;
            devices_count?: number;
            created_at: string;
            updated_at: string;
        }>;
        total?: number;
        per_page?: number;
        current_page?: number;
        last_page?: number;
        from?: number;
        to?: number;
    };
    filters?: { search?: string; sort?: string };
    flash?: { success?: string; error?: string };
}>();

const outlets = computed(() => props.outlets ?? { data: [] });
const filters = computed(() => props.filters ?? { search: "", sort: "" });
const flash = computed(() => props.flash ?? {});

// ── State ────────────────────────────────────────────────────────────────────
const searchQuery = ref(filters.value.search || "");
const sortBy = ref(filters.value.sort || "");
const showImportModal = ref(false);

// ── Delete modal ───────────────────────────────────────────────────────────────
const showDeleteModal = ref(false);
const pendingDeleteId = ref<unknown>(null);
const pendingDeleteName = ref("");
const deleting = ref(false);

function openDeleteModal(id: unknown, name: string) {
    pendingDeleteId.value = id;
    pendingDeleteName.value = name;
    showDeleteModal.value = true;
}

async function confirmDelete() {
    if (pendingDeleteId.value === null) return;
    deleting.value = true;
    showDeleteModal.value = false;

    router.delete(`/outlets/${Number(pendingDeleteId.value)}`, {
        preserveScroll: true,
        onSuccess: (page: any) => {
            deleting.value = false;
            const props = page.props as any;
            if (props.flash?.success) {
                showSuccessAlert.value = props.flash.success;
                setTimeout(() => (showSuccessAlert.value = ""), 4000);
            }
            if (props.flash?.error) {
                showErrorAlert.value = props.flash.error;
                setTimeout(() => (showErrorAlert.value = ""), 4000);
            }
        },
        onError: () => {
            deleting.value = false;
            showErrorAlert.value = "Gagal menghapus outlet.";
            setTimeout(() => (showErrorAlert.value = ""), 4000);
        },
    });
}
const importFile = ref<File | null>(null);
const importDragOver = ref(false);
const importLoading = ref(false);
const importError = ref("");

// ── Computed flash ────────────────────────────────────────────────────────────
const flashSuccess = computed(() => flash.value.success || "");
const flashError = computed(() => flash.value.error || "");

// ── Alert state ───────────────────────────────────────────────────────────────
const showSuccessAlert = ref("");
const showErrorAlert = ref("");

// ── Search & Sort ─────────────────────────────────────────────────────────────
function applyFilters() {
    router.get("/outlets", {
        search: searchQuery.value || undefined,
        sort: sortBy.value || undefined,
    }, { preserveScroll: true });
}

function clearSearch() {
    searchQuery.value = "";
    router.get("/outlets", { sort: sortBy.value || undefined }, { preserveScroll: true });
}

// ── Import Modal ───────────────────────────────────────────────────────────────
function openImportModal() {
    importFile.value = null;
    importError.value = "";
    importDragOver.value = false;
    showImportModal.value = true;
}

function closeImportModal() {
    if (importLoading.value) return;
    showImportModal.value = false;
}

function onFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files?.[0]) {
        importFile.value = target.files[0];
        importError.value = "";
    }
}

function onDrop(event: DragEvent) {
    event.preventDefault();
    importDragOver.value = false;
    const file = event.dataTransfer?.files?.[0];
    if (file) {
        const ext = file.name.split(".").pop()?.toLowerCase();
        if (["csv", "txt", "xlsx"].includes(ext ?? "")) {
            importFile.value = file;
            importError.value = "";
        } else {
            importError.value = "Only CSV, TXT, or XLSX files are accepted.";
        }
    }
}

function submitImport() {
    if (!importFile.value) {
        importError.value = "Please select a file first.";
        return;
    }
    importLoading.value = true;
    importError.value = "";

    const formData = new FormData();
    formData.append("file", importFile.value);

    router.post("/outlets/import", formData, {
        onFinish: () => {
            importLoading.value = false;
        },
        onSuccess: () => {
            showImportModal.value = false;
            importFile.value = null;
        },
        onError: (errors) => {
            importError.value = Object.values(errors)[0] as string || "Import failed.";
        },
    });
}

function downloadTemplate() {
    window.location.href = "/outlets/import/template";
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function getDeviceCount(count: number | undefined): { label: string; color: string } {
    if (!count || count === 0) return { label: "No devices", color: "text-gray-400" };
    if (count <= 3) return { label: `${count} device${count > 1 ? "s" : ""}`, color: "text-amber-500" };
    return { label: `${count} devices`, color: "text-emerald-500" };
}

function pageUrl(page: number): string {
    const params = new URLSearchParams();
    params.set("page", String(page));
    if (filters.value.search) params.set("search", filters.value.search);
    if (filters.value.sort) params.set("sort", filters.value.sort);
    return "/outlets?" + params.toString();
}
</script>

<template>
    <AppLayout title="Outlets">
        <!-- Success Alert -->
        <Transition name="alert-slide">
            <div
                v-if="showSuccessAlert"
                class="fixed top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 shadow-lg shadow-emerald-200/50 max-w-sm"
            >
                <div class="w-8 h-8 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-emerald-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-emerald-800">{{ showSuccessAlert }}</p>
                <button class="ml-1 text-emerald-400 hover:text-emerald-600 transition-colors" @click="showSuccessAlert = ''">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </Transition>

        <!-- Error Alert -->
        <Transition name="alert-slide">
            <div
                v-if="showErrorAlert"
                class="fixed top-4 right-4 z-50 flex items-center gap-3 px-4 py-3 rounded-xl bg-red-50 border border-red-200 shadow-lg shadow-red-200/50 max-w-sm"
            >
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-red-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-red-800">{{ showErrorAlert }}</p>
                <button class="ml-1 text-red-400 hover:text-red-600 transition-colors" @click="showErrorAlert = ''">
                    <X class="w-4 h-4" />
                </button>
            </div>
        </Transition>

        <div class="min-h-screen bg-slate-50/60">

            <!-- Page Header -->
            <div class="px-6 pt-8 pb-5">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-mono font-semibold text-emerald-600 uppercase tracking-widest mb-1">
                            Location Management
                        </p>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                            All Outlets
                            <span v-if="outlets.total" class="ml-2 text-sm font-normal text-slate-400">
                                ({{ outlets.total }})
                            </span>
                        </h1>
                    </div>
                    <div class="flex items-center gap-2">
                        <Button
                            variant="outline"
                            size="sm"
                            class="gap-2 border-slate-300 hover:border-slate-400 hover:bg-slate-50"
                            @click="openImportModal"
                        >
                            <Upload class="w-4 h-4" />
                            Import
                        </Button>
                        <Link href="/outlets/create">
                            <Button class="bg-emerald-600 hover:bg-emerald-700 shadow-sm shadow-emerald-200 gap-2">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                    <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                                </svg>
                                Add Outlet
                            </Button>
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Search & Filter Bar -->
            <div class="px-6 pb-5">
                <div class="flex items-center gap-3">
                    <!-- Search -->
                    <div class="relative flex-1 max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none" />
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Search outlets..."
                            class="w-full h-10 pl-9 pr-8 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 transition-all"
                            @keyup.enter="applyFilters"
                            @keyup.esc="clearSearch"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                            @click="clearSearch"
                        >
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Sort Dropdown -->
                    <div class="relative">
                        <select
                            v-model="sortBy"
                            class="h-10 pl-3 pr-8 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 appearance-none focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 cursor-pointer hover:bg-slate-50 transition-all"
                            @change="applyFilters"
                        >
                            <option value="">Latest</option>
                            <option value="name_asc">Name A–Z</option>
                            <option value="name_desc">Name Z–A</option>
                            <option value="devices">Most Devices</option>
                        </select>
                        <ChevronDown class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" />
                    </div>

                    <button
                        v-if="searchQuery || sortBy"
                        type="button"
                        class="h-10 px-3 rounded-xl border border-slate-200 text-xs text-slate-500 hover:bg-slate-50 transition-colors"
                        @click="clearSearch"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Cards Grid -->
            <div class="px-6 pb-12">

                <!-- Empty State -->
                <Transition name="fade-scale">
                    <div
                        v-if="outlets.data.length === 0"
                        class="flex flex-col items-center justify-center py-32 text-center"
                    >
                        <div class="w-20 h-20 rounded-2xl bg-slate-100 flex items-center justify-center mb-5">
                            <MapPin class="w-9 h-9 text-slate-300" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-700 mb-1">
                            {{ searchQuery || sortBy ? "No results found" : "No outlets yet" }}
                        </h3>
                        <p class="text-sm text-slate-400 max-w-xs">
                            {{ searchQuery || sortBy ? "Try adjusting your search or filter." : "Create your first outlet location to start monitoring your IoT devices." }}
                        </p>
                        <div class="flex gap-3 mt-6">
                            <button v-if="searchQuery || sortBy" class="text-sm text-slate-500 hover:text-slate-700" @click="clearSearch">Clear filters</button>
                            <Link v-if="!searchQuery && !sortBy" href="/outlets/create">
                                <Button class="bg-emerald-600 hover:bg-emerald-700 gap-2">
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                        <path d="M12 5v14M5 12h14" stroke-linecap="round" />
                                    </svg>
                                    Create Outlet
                                </Button>
                            </Link>
                        </div>
                    </div>
                </Transition>

                <!-- Outlet Cards -->
                <TransitionGroup
                    tag="div"
                    name="card-list"
                    class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5"
                >
                    <div
                        v-for="(outlet, index) in outlets.data"
                        :key="Number(outlet.id)"
                        class="outlet-card group relative bg-white rounded-2xl border border-slate-200/80 overflow-hidden cursor-pointer transition-all duration-300 hover:border-emerald-200/60 hover:shadow-xl hover:shadow-emerald-900/[0.06] hover:-translate-y-1"
                        :style="{ 'animation-delay': `${index * 60}ms` }"
                        @click="$inertia.get(`/outlets/${Number(outlet.id)}`)"
                    >
                        <!-- Top gradient accent line -->
                        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-emerald-400 via-emerald-500 to-teal-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                        <!-- Subtle dot pattern -->
                        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, #000 1px, transparent 1px); background-size: 16px 16px;" />

                        <div class="relative p-5">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100/60 flex items-center justify-center shrink-0 group-hover:scale-110 group-hover:from-emerald-100 group-hover:to-teal-100 transition-all duration-300">
                                    <MapPin class="w-5 h-5 text-emerald-600" />
                                </div>
                                <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-50 border border-slate-200/60 group-hover:border-emerald-200/60 group-hover:bg-emerald-50/60 transition-all duration-300">
                                    <Cpu class="w-3 h-3 text-slate-400 group-hover:text-emerald-500 transition-colors" />
                                    <span
                                        class="text-xs font-mono font-semibold transition-colors"
                                        :class="getDeviceCount(outlet.devices_count as number).color"
                                    >
                                        {{ getDeviceCount(outlet.devices_count as number).label }}
                                    </span>
                                </div>
                            </div>

                            <!-- Outlet Name -->
                            <h3 class="font-bold text-slate-900 text-base leading-snug mb-1 group-hover:text-emerald-800 transition-colors duration-200">
                                {{ outlet.name }}
                            </h3>

                            <!-- Details -->
                            <div class="space-y-1.5 mb-5">
                                <div v-if="outlet.address" class="flex items-start gap-2">
                                    <svg class="w-3.5 h-3.5 text-slate-300 mt-0.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z" />
                                        <circle cx="12" cy="9" r="2.5" />
                                    </svg>
                                    <span class="text-sm text-slate-500 leading-snug">{{ outlet.address }}</span>
                                </div>
                                <div v-if="outlet.phone" class="flex items-center gap-2">
                                    <PhoneIncoming class="w-3.5 h-3.5 text-slate-300 shrink-0" />
                                    <span class="text-sm text-slate-500 font-mono">{{ outlet.phone }}</span>
                                </div>
                                <div v-if="!outlet.address && !outlet.phone" class="text-sm text-slate-300 italic">
                                    No location details
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="flex items-center justify-between pt-3 border-t border-slate-100 group-hover:border-emerald-100/60 transition-colors duration-300">
                                <button
                                    type="button"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all duration-200 opacity-0 group-hover:opacity-100"
                                    @click.stop="openDeleteModal(outlet.id, outlet.name)"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                    Delete
                                </button>
                                <div class="flex items-center gap-1.5 text-xs font-semibold text-emerald-600 group-hover:gap-2.5 transition-all duration-200">
                                    <span>View</span>
                                    <ArrowRight class="w-3.5 h-3.5" />
                                </div>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>

                <!-- Pagination -->
                <div v-if="outlets.last_page > 1" class="flex items-center justify-between mt-8 px-1">
                    <p class="text-xs text-slate-400">
                        Showing {{ outlets.from }}–{{ outlets.to }} of {{ outlets.total }} outlets
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-if="outlets.current_page > 1"
                            :href="pageUrl(outlets.current_page - 1)"
                            class="px-3 py-1.5 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            ← Prev
                        </Link>
                        <template v-for="p in outlets.last_page" :key="p">
                            <Link
                                v-if="p === 1 || p === outlets.last_page || Math.abs(p - outlets.current_page) <= 1"
                                :href="pageUrl(p)"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-sm transition-colors"
                                :class="p === outlets.current_page
                                    ? 'bg-emerald-600 text-white font-semibold'
                                    : 'text-slate-600 hover:bg-slate-100'"
                            >
                                {{ p }}
                            </Link>
                            <span v-else-if="Math.abs(p - outlets.current_page) === 2" class="px-1 text-slate-400">…</span>
                        </template>
                        <Link
                            v-if="outlets.current_page < outlets.last_page"
                            :href="pageUrl(outlets.current_page + 1)"
                            class="px-3 py-1.5 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Next →
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Import Modal -->
        <Teleport to="body">
            <Transition name="modal">
                <div
                    v-if="showImportModal"
                    class="fixed inset-0 z-50 flex items-center justify-center p-4"
                >
                    <!-- Backdrop -->
                    <div
                        class="absolute inset-0 bg-slate-900/40 backdrop-blur-sm"
                        @click="closeImportModal"
                    />

                    <!-- Modal -->
                    <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
                            <div>
                                <h2 class="font-bold text-slate-900">Import Outlets</h2>
                                <p class="text-xs text-slate-400 mt-0.5">Upload CSV or XLSX file to bulk create outlets</p>
                            </div>
                            <button
                                type="button"
                                class="w-8 h-8 rounded-lg flex items-center justify-center text-slate-400 hover:text-slate-600 hover:bg-slate-100 transition-colors"
                                :disabled="importLoading"
                                @click="closeImportModal"
                            >
                                <X class="w-5 h-5" />
                            </button>
                        </div>

                        <!-- Body -->
                        <div class="px-6 py-5">
                            <!-- Download template -->
                            <div class="flex items-center justify-between px-4 py-3 rounded-xl border border-dashed border-slate-200 bg-slate-50 mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-emerald-100 flex items-center justify-center">
                                        <Download class="w-4 h-4 text-emerald-600" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-slate-700">CSV Template</p>
                                        <p class="text-xs text-slate-400">outlets_template.csv</p>
                                    </div>
                                </div>
                                <button
                                    type="button"
                                    class="text-xs font-medium text-emerald-600 hover:text-emerald-700 flex items-center gap-1 transition-colors"
                                    @click="downloadTemplate"
                                >
                                    <Download class="w-3.5 h-3.5" />
                                    Download
                                </button>
                            </div>

                            <!-- Dropzone -->
                            <div
                                class="relative rounded-xl border-2 border-dashed transition-all duration-200 overflow-hidden"
                                :class="[
                                    importDragOver
                                        ? 'border-emerald-400 bg-emerald-50'
                                        : importFile
                                            ? 'border-emerald-300 bg-emerald-50/50'
                                            : 'border-slate-200 bg-white hover:border-slate-300',
                                    importError ? 'border-red-300' : '',
                                ]"
                                @dragover.prevent="importDragOver = true"
                                @dragleave.prevent="importDragOver = false"
                                @drop.prevent="onDrop"
                            >
                                <input
                                    type="file"
                                    accept=".csv,.txt,.xlsx"
                                    class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
                                    @change="onFileChange"
                                />
                                <div class="flex flex-col items-center justify-center py-10 px-4 text-center">
                                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center mb-3">
                                        <Upload class="w-6 h-6 text-slate-400" />
                                    </div>
                                    <p v-if="!importFile" class="text-sm font-medium text-slate-600 mb-1">
                                        Drop your file here, or <span class="text-emerald-600">browse</span>
                                    </p>
                                    <p v-if="!importFile" class="text-xs text-slate-400">CSV, TXT, or XLSX up to 5MB</p>
                                    <div v-if="importFile" class="text-center">
                                        <p class="text-sm font-semibold text-emerald-700">{{ importFile.name }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ (importFile.size / 1024).toFixed(1) }} KB</p>
                                    </div>
                                </div>
                            </div>

                            <!-- Error -->
                            <Transition name="slide-down">
                                <div v-if="importError" class="mt-3 px-3 py-2.5 rounded-lg bg-red-50 border border-red-200 text-xs text-red-600">
                                    {{ importError }}
                                </div>
                            </Transition>
                        </div>

                        <!-- Footer -->
                        <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50">
                            <button
                                type="button"
                                class="h-9 px-4 rounded-xl text-sm font-medium text-slate-600 hover:bg-slate-100 transition-colors disabled:opacity-50"
                                :disabled="importLoading"
                                @click="closeImportModal"
                            >
                                Cancel
                            </button>
                            <Button
                                class="bg-emerald-600 hover:bg-emerald-700 gap-2 disabled:opacity-60"
                                :disabled="!importFile || importLoading"
                                @click="submitImport"
                            >
                                <svg v-if="importLoading" class="w-4 h-4 animate-spin" viewBox="0 0 24 24" fill="none">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                                </svg>
                                <Upload v-else class="w-4 h-4" />
                                {{ importLoading ? "Importing..." : "Import Outlets" }}
                            </Button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Confirmation Modal -->
        <ConfirmModal
            v-model:open="showDeleteModal"
            title="Hapus Outlet?"
            :description="`Outlet '${pendingDeleteName}' dan semua device di dalamnya akan dihapus. Tindakan ini tidak dapat dibatalkan.`"
            confirm-text="Ya, Hapus"
            cancel-text="Batal"
            variant="destructive"
            :loading="deleting"
            @confirm="confirmDelete"
        />
    </AppLayout>
</template>

<style scoped>
/* ─── Entrance Animation ───────────────────────────────────────────────────── */
.outlet-card {
    opacity: 0;
    transform: translateY(16px);
    animation: cardReveal 0.5s cubic-bezier(0.22, 1, 0.36, 1) forwards;
}

@keyframes cardReveal {
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ─── TransitionGroup ─────────────────────────────────────────────────────── */
.card-list-enter-active {
    transition: all 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}
.card-list-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 1, 1);
    position: absolute;
    width: calc(100% - 0px);
}
.card-list-move {
    transition: transform 0.45s cubic-bezier(0.22, 1, 0.36, 1);
}
.card-list-enter-from {
    opacity: 0;
    transform: scale(0.94) translateY(12px);
}
.card-list-leave-to {
    opacity: 0;
    transform: scale(0.96);
}

/* ─── Modal ───────────────────────────────────────────────────────────────── */
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

/* ─── Slide down ──────────────────────────────────────────────────────────── */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.2s ease;
    overflow: hidden;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    max-height: 0;
}
.slide-down-enter-to,
.slide-down-leave-from {
    opacity: 1;
    max-height: 80px;
}

/* ─── Empty State ─────────────────────────────────────────────────────────── */
.fade-scale-enter-active,
.fade-scale-leave-active {
    transition: all 0.35s cubic-bezier(0.22, 1, 0.36, 1);
}
.fade-scale-enter-from,
.fade-scale-leave-to {
    opacity: 0;
    transform: scale(0.95);
}

/* ─── Alert ───────────────────────────────────────────────────────────────── */
.alert-slide-enter-active,
.alert-slide-leave-active {
    transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}
.alert-slide-enter-from,
.alert-slide-leave-to {
    opacity: 0;
    transform: translateX(16px);
}

/* ─── Focus ───────────────────────────────────────────────────────────────── */
button[type="button"] {
    outline: none;
}
button[type="button"]:focus-visible {
    outline: 2px solid #f87171;
    outline-offset: 2px;
}
</style>
