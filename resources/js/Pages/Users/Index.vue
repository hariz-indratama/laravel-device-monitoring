<script setup lang="ts">
import { computed } from "vue";
import { router, Link, usePage } from "@inertiajs/vue3";
import AppLayout from "@/layouts/AppLayout.vue";
import { Button } from "@/Components/ui/button";
import { UserPlus, Search, X, ChevronDown, ShieldCheck, UserCog, Trash2 } from "lucide-vue-next";

// usePage() pattern
// eslint-disable-next-line @typescript-eslint/no-explicit-any
const page = usePage() as any;
const users = page.props.users ?? { data: [] };
const filters = page.props.filters ?? { search: "", role: "" };
const auth = page.props.auth ?? {};
const flash = page.props.flash ?? {};

// ── State ────────────────────────────────────────────────────────────────────
const searchQuery = computed({
    get: () => filters.search || "",
    set: (v) => {
        applyFilters({ search: v || undefined });
    },
});
const roleFilter = computed({
    get: () => filters.role || "",
    set: (v) => {
        applyFilters({ role: v || undefined });
    },
});

// ── Flash ─────────────────────────────────────────────────────────────────────
const flashSuccess = computed(() => flash.success || "");
const flashError = computed(() => flash.error || "");
const showFlash = computed(() => flashSuccess.value || flashError.value);

// ── Filters ──────────────────────────────────────────────────────────────────
function applyFilters(overrides: Record<string, unknown> = {}) {
    router.get("/users", {
        search: overrides.search !== undefined ? overrides.search : filters.search || undefined,
        role: overrides.role !== undefined ? overrides.role : filters.role || undefined,
    }, { preserveScroll: true });
}

function clearFilters() {
    router.get("/users", {}, { preserveScroll: true });
}

// ── Role helpers ──────────────────────────────────────────────────────────────
function getRoleInfo(role: string): { label: string; color: string; bg: string; icon: typeof ShieldCheck } {
    if (role === "owner") {
        return { label: "Owner", color: "text-purple-700", bg: "bg-purple-50 border-purple-200", icon: ShieldCheck };
    }
    return { label: "Staff", color: "text-slate-600", bg: "bg-slate-50 border-slate-200", icon: UserCog };
}

// ── Pagination URL ─────────────────────────────────────────────────────────────
function pageUrl(p: number): string {
    const params = new URLSearchParams();
    params.set("page", String(p));
    if (filters.search) params.set("search", filters.search);
    if (filters.role) params.set("role", filters.role);
    return "/users?" + params.toString();
}

// ── Delete ────────────────────────────────────────────────────────────────────
function destroy(id: number, name: string) {
    if (!confirm(`Delete user "${name}"? This action cannot be undone.`)) return;
    router.delete(`/users/${id}`);
}
</script>

<template>
    <AppLayout title="Users">
        <div class="min-h-screen bg-slate-50/60">

            <!-- Flash Banner -->
            <Transition name="slide-down">
                <div
                    v-if="showFlash"
                    class="mx-6 mt-6 px-4 py-3 rounded-xl text-sm font-medium"
                    :class="flashSuccess ? 'bg-emerald-50 border border-emerald-200 text-emerald-700' : 'bg-red-50 border border-red-200 text-red-700'"
                >
                    {{ flashSuccess || flashError }}
                    <button
                        class="ml-auto float-right opacity-60 hover:opacity-100"
                        @click="showFlash = false"
                    >
                        <X class="w-4 h-4" />
                    </button>
                </div>
            </Transition>

            <!-- Page Header -->
            <div class="px-6 pt-8 pb-5">
                <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-mono font-semibold text-purple-600 uppercase tracking-widest mb-1">
                            Team Management
                        </p>
                        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">
                            Users
                            <span v-if="users.total" class="ml-2 text-sm font-normal text-slate-400">({{ users.total }})</span>
                        </h1>
                    </div>
                    <Link href="/users/create">
                        <Button class="bg-purple-600 hover:bg-purple-700 shadow-sm shadow-purple-200 gap-2">
                            <UserPlus class="w-4 h-4" />
                            Add User
                        </Button>
                    </Link>
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
                            placeholder="Search by name or email..."
                            class="w-full h-10 pl-9 pr-8 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 transition-all"
                            @keyup.enter="applyFilters({ search: searchQuery })"
                            @keyup.esc="searchQuery = ''; applyFilters({ search: '' })"
                        />
                        <button
                            v-if="searchQuery"
                            type="button"
                            class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition-colors"
                            @click="searchQuery = ''; applyFilters({ search: '' })"
                        >
                            <X class="w-3.5 h-3.5" />
                        </button>
                    </div>

                    <!-- Role Filter -->
                    <div class="relative">
                        <select
                            v-model="roleFilter"
                            class="h-10 pl-3 pr-8 rounded-xl border border-slate-200 bg-white text-sm text-slate-700 appearance-none focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-400 cursor-pointer hover:bg-slate-50 transition-all"
                        >
                            <option value="">All Roles</option>
                            <option value="owner">Owner</option>
                            <option value="staff">Staff</option>
                        </select>
                        <ChevronDown class="absolute right-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-400 pointer-events-none" />
                    </div>

                    <button
                        v-if="searchQuery || roleFilter"
                        type="button"
                        class="h-10 px-3 rounded-xl border border-slate-200 text-xs text-slate-500 hover:bg-slate-50 transition-colors"
                        @click="clearFilters"
                    >
                        Clear
                    </button>
                </div>
            </div>

            <!-- Users Grid -->
            <div class="px-6 pb-12">

                <!-- Empty State -->
                <Transition name="fade-scale">
                    <div
                        v-if="users.data.length === 0"
                        class="flex flex-col items-center justify-center py-32 text-center"
                    >
                        <div class="w-20 h-20 rounded-2xl bg-slate-100 flex items-center justify-center mb-5">
                            <UserCog class="w-9 h-9 text-slate-300" />
                        </div>
                        <h3 class="text-lg font-semibold text-slate-700 mb-1">
                            {{ searchQuery || roleFilter ? "No users found" : "No users yet" }}
                        </h3>
                        <p class="text-sm text-slate-400 max-w-xs">
                            {{ searchQuery || roleFilter ? "Try adjusting your search or filter." : "Add your first team member to start collaborating." }}
                        </p>
                        <div class="flex gap-3 mt-6">
                            <button v-if="searchQuery || roleFilter" class="text-sm text-slate-500 hover:text-slate-700" @click="clearFilters">Clear filters</button>
                            <Link v-if="!searchQuery && !roleFilter && auth.role === 'owner'" href="/users/create">
                                <Button class="bg-purple-600 hover:bg-purple-700 gap-2">
                                    <UserPlus class="w-4 h-4" />
                                    Add User
                                </Button>
                            </Link>
                        </div>
                    </div>
                </Transition>

                <!-- User Cards -->
                <TransitionGroup
                    tag="div"
                    name="card-list"
                    class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5"
                >
                    <div
                        v-for="(user, index) in users.data"
                        :key="Number(user.id)"
                        class="user-card group relative bg-white rounded-2xl border border-slate-200/80 overflow-hidden transition-all duration-300 hover:border-purple-200/60 hover:shadow-xl hover:shadow-purple-900/[0.06] hover:-translate-y-1"
                        :style="{ 'animation-delay': `${index * 60}ms` }"
                    >
                        <!-- Top gradient accent line -->
                        <div class="absolute top-0 left-0 right-0 h-0.5 bg-gradient-to-r from-purple-400 via-purple-500 to-fuchsia-400 opacity-0 group-hover:opacity-100 transition-opacity duration-300" />

                        <!-- Dot pattern -->
                        <div class="absolute inset-0 opacity-[0.03]" style="background-image: radial-gradient(circle, #000 1px, transparent 1px); background-size: 16px 16px;" />

                        <div class="relative p-5">
                            <!-- Card Header -->
                            <div class="flex items-start justify-between mb-4">
                                <!-- Avatar -->
                                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-100 to-fuchsia-50 border border-purple-100 flex items-center justify-center text-purple-700 font-bold text-lg select-none shrink-0">
                                    {{ user.name?.charAt(0)?.toUpperCase() || "?" }}
                                </div>

                                <!-- Role Badge -->
                                <div
                                    class="flex items-center gap-1.5 px-2.5 py-1 rounded-full border text-xs font-semibold"
                                    :class="getRoleInfo(user.role).bg + ' ' + getRoleInfo(user.role).color"
                                >
                                    <component :is="getRoleInfo(user.role).icon" class="w-3 h-3" />
                                    {{ getRoleInfo(user.role).label }}
                                </div>
                            </div>

                            <!-- Name -->
                            <h3 class="font-bold text-slate-900 text-base leading-snug mb-1 group-hover:text-purple-800 transition-colors duration-200">
                                {{ user.name }}
                            </h3>

                            <!-- Details -->
                            <div class="space-y-1.5 mb-5">
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="2" y="4" width="20" height="16" rx="2"/>
                                        <path d="M2 8h20M2 12h20M6 16h12"/>
                                    </svg>
                                    <span class="text-sm text-slate-500 font-mono">{{ user.email }}</span>
                                </div>
                                <div v-if="user.phone" class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07A19.5 19.5 0 0 1 4.69 13 19.79 19.79 0 0 1 1.61 4.34 2 2 0 0 1 3.59 2.18h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L7.91 9.91a16 16 0 0 0 6.18 6.18l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/>
                                    </svg>
                                    <span class="text-sm text-slate-500">{{ user.phone }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-slate-300 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"/>
                                        <line x1="16" y1="2" x2="16" y2="6"/>
                                        <line x1="8" y1="2" x2="8" y2="6"/>
                                        <line x1="3" y1="10" x2="21" y2="10"/>
                                    </svg>
                                    <span class="text-xs text-slate-400">
                                        Joined {{ user.created_at ? new Date(user.created_at).toLocaleDateString("id-ID", { year: "numeric", month: "short", day: "numeric" }) : "—" }}
                                    </span>
                                </div>
                            </div>

                            <!-- Card Footer -->
                            <div class="flex items-center justify-between pt-3 border-t border-slate-100 group-hover:border-purple-100/60 transition-colors duration-300">
                                <!-- Edit Button -->
                                <Link
                                    v-if="auth.role === 'owner'"
                                    :href="`/users/${user.id}/edit`"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-purple-600 hover:bg-purple-50 transition-all duration-200"
                                >
                                    <UserCog class="w-3.5 h-3.5" />
                                    Edit
                                </Link>
                                <div v-else />

                                <!-- Delete Button -->
                                <button
                                    v-if="auth.role === 'owner' && user.id !== auth.id"
                                    type="button"
                                    class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg text-xs font-medium text-slate-400 hover:text-red-500 hover:bg-red-50 transition-all duration-200"
                                    @click="destroy(user.id, user.name)"
                                >
                                    <Trash2 class="w-3.5 h-3.5" />
                                    Delete
                                </button>
                            </div>
                        </div>
                    </div>
                </TransitionGroup>

                <!-- Pagination -->
                <div v-if="users.last_page > 1" class="flex items-center justify-between mt-8 px-1">
                    <p class="text-xs text-slate-400">
                        Showing {{ users.from }}–{{ users.to }} of {{ users.total }} users
                    </p>
                    <div class="flex items-center gap-1">
                        <Link
                            v-if="users.current_page > 1"
                            :href="pageUrl(users.current_page - 1)"
                            class="px-3 py-1.5 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            ← Prev
                        </Link>
                        <template v-for="p in users.last_page" :key="p">
                            <Link
                                v-if="p === 1 || p === users.last_page || Math.abs(p - users.current_page) <= 1"
                                :href="pageUrl(p)"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-sm transition-colors"
                                :class="p === users.current_page
                                    ? 'bg-purple-600 text-white font-semibold'
                                    : 'text-slate-600 hover:bg-slate-100'"
                            >
                                {{ p }}
                            </Link>
                            <span v-else-if="Math.abs(p - users.current_page) === 2" class="px-1 text-slate-400">…</span>
                        </template>
                        <Link
                            v-if="users.current_page < users.last_page"
                            :href="pageUrl(users.current_page + 1)"
                            class="px-3 py-1.5 rounded-lg text-sm border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors"
                        >
                            Next →
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* ─── Entrance Animation ───────────────────────────────────────────────────── */
.user-card {
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

/* ─── Flash ───────────────────────────────────────────────────────────────── */
.slide-down-enter-active,
.slide-down-leave-active {
    transition: all 0.25s ease;
    overflow: hidden;
}
.slide-down-enter-from,
.slide-down-leave-to {
    opacity: 0;
    transform: translateY(-8px);
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
</style>
