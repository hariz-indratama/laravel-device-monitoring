<script setup lang="ts">
import { ref, computed } from "vue";
import { Link, usePage, router } from "@inertiajs/vue3";
import {
    Bell,
    Search,
    ChevronDown,
    User,
    LogOut,
    Settings,
    ShieldCheck,
    Menu,
} from "lucide-vue-next";

// eslint-disable-next-line @typescript-eslint/no-explicit-any
const page = usePage() as any;
const auth = computed(() => page.props.auth);

const emit = defineEmits<{ openSidebar: [] }>();

// ── User dropdown ─────────────────────────────────────────────────────────────
const userMenuOpen = ref(false);

function toggleUserMenu() {
    userMenuOpen.value = !userMenuOpen.value;
}

function closeUserMenu() {
    userMenuOpen.value = false;
}

function logout() {
    router.post("/logout");
}

// ── Notifications ─────────────────────────────────────────────────────────────
const alertCount = computed(() => page.props.stats?.active_alerts ?? 0);

// ── Global search ────────────────────────────────────────────────────────────
const searchQuery = ref("");

function submitSearch() {
    if (!searchQuery.value.trim()) return;
    router.get(
        "/devices",
        { search: searchQuery.value.trim() },
        { preserveScroll: true },
    );
    searchQuery.value = "";
}

// ── Initials ─────────────────────────────────────────────────────────────────
const initials = computed(() => {
    return auth.value?.name?.charAt(0)?.toUpperCase() || "U";
});

const roleLabel = computed(() => {
    if (auth.value?.role === "owner") return "Owner";
    return "Staff";
});
</script>

<template>
    <header
        class="relative z-30 flex items-center justify-between h-16 px-4 lg:px-6 bg-white border-b border-slate-200/80 shadow-sm shadow-slate-100/50"
    >
        <!-- ── Left: Logo + Search ────────────────────────────────────────────── -->
        <div class="flex items-center gap-4 flex-1">
            <!-- Mobile hamburger -->
            <button
                class="lg:hidden p-2 -ml-2 rounded-xl hover:bg-slate-100 active:bg-slate-200 transition-colors"
                aria-label="Open menu"
                @click="$emit('openSidebar')"
            >
                <Menu class="w-5 h-5 text-slate-600" />
            </button>

            <!-- Logo -->
            <Link
                href="/dashboard"
                class="flex items-center gap-2.5 shrink-0 group"
            >
                <div
                    class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center shadow-sm shadow-emerald-200 group-hover:scale-105 transition-transform duration-200"
                >
                    <svg
                        class="w-4.5 h-4.5 text-white"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2.5"
                    >
                        <path
                            d="M12 2L2 7l10 5 10-5-10-5z"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                        <path
                            d="M2 17l10 5 10-5M2 12l10 5 10-5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        />
                    </svg>
                </div>
                <div class="hidden sm:block">
                    <span
                        class="text-sm font-bold text-slate-800 tracking-tight group-hover:text-emerald-700 transition-colors"
                    >
                        Device<span class="text-emerald-600">Monitor</span>
                    </span>
                </div>
            </Link>

            <!-- Divider -->
            <div class="hidden sm:block w-px h-6 bg-slate-200" />

            <!-- Global Search -->
            <form
                @submit.prevent="submitSearch"
                class="hidden md:flex items-center flex-1 max-w-sm"
            >
                <div class="relative w-full">
                    <Search
                        class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-slate-400 pointer-events-none"
                    />
                    <input
                        v-model="searchQuery"
                        type="text"
                        placeholder="Search devices, outlets..."
                        class="w-full h-9 pl-9 pr-4 rounded-xl border border-slate-200 bg-slate-50 text-sm text-slate-700 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-400 focus:bg-white transition-all"
                    />
                </div>
            </form>
        </div>

        <!-- ── Right: Actions ────────────────────────────────────────────────── -->
        <div class="flex items-center gap-1">
            <!-- Alerts Link -->
            <Link
                href="/alerts"
                class="relative flex items-center justify-center w-9 h-9 rounded-xl text-slate-500 hover:text-slate-700 hover:bg-slate-100 transition-all duration-200 group"
                title="Alerts"
            >
                <Bell class="w-5 h-5" />
                <!-- Alert badge -->
                <Transition name="badge">
                    <span
                        v-if="alertCount > 0"
                        class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] flex items-center justify-center px-1 rounded-full bg-red-500 text-white text-[10px] font-bold leading-none shadow-sm"
                    >
                        {{ alertCount > 99 ? "99+" : alertCount }}
                    </span>
                </Transition>
            </Link>

            <!-- Divider -->
            <div class="w-px h-6 bg-slate-200 mx-1" />

            <!-- User Menu -->
            <div class="relative" @mouseleave="closeUserMenu">
                <!-- Trigger -->
                <button
                    type="button"
                    class="flex items-center cursor-pointer gap-2.5 px-2 py-1.5 rounded-xl hover:bg-slate-100 transition-all duration-200 group"
                    @click="toggleUserMenu"
                >
                    <!-- Avatar -->
                    <div
                        class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 flex items-center justify-center text-white text-xs font-bold shadow-sm shadow-emerald-200 group-hover:scale-105 transition-transform duration-200"
                    >
                        {{ initials }}
                    </div>
                    <!-- Name + Role -->
                    <div class="hidden lg:block text-left">
                        <p
                            class="text-sm font-semibold text-slate-700 leading-tight group-hover:text-emerald-700 transition-colors"
                        >
                            {{ auth?.name }}
                        </p>
                        <p
                            class="text-[11px] text-slate-400 leading-tight flex items-center gap-1"
                        >
                            <ShieldCheck
                                v-if="auth?.role === 'owner'"
                                class="w-3 h-3 text-purple-500"
                            />
                            {{ roleLabel }}
                        </p>
                    </div>
                    <!-- Chevron -->
                    <ChevronDown
                        class="hidden lg:block w-4 h-4 text-slate-400 transition-transform duration-200"
                        :class="{ 'rotate-180': userMenuOpen }"
                    />
                </button>

                <!-- Dropdown -->
                <Transition name="dropdown">
                    <div
                        v-if="userMenuOpen"
                        class="absolute right-0 top-full mt-2 w-56 bg-white rounded-2xl border border-slate-200/80 shadow-xl shadow-slate-900/10 overflow-hidden"
                    >
                        <!-- User info header -->
                        <div
                            class="px-4 py-3 border-b border-slate-100 bg-slate-50/50"
                        >
                            <p class="text-sm font-semibold text-slate-800">
                                {{ auth?.name }}
                            </p>
                            <p class="text-xs text-slate-400 mt-0.5">
                                {{ auth?.email }}
                            </p>
                        </div>

                        <!-- Menu items -->
                        <div class="py-1.5">
                            <Link
                                href="/users"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:text-emerald-700 hover:bg-emerald-50/60 transition-colors"
                            >
                                <ShieldCheck class="w-4 h-4 text-slate-400" />
                                User Management
                            </Link>
                            <Link
                                href="/profile"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:text-emerald-700 hover:bg-emerald-50/60 transition-colors"
                            >
                                <User class="w-4 h-4 text-slate-400" />
                                My Profile
                            </Link>
                            <Link
                                href="/settings"
                                class="flex items-center gap-3 px-4 py-2.5 text-sm text-slate-600 hover:text-emerald-700 hover:bg-emerald-50/60 transition-colors"
                            >
                                <Settings class="w-4 h-4 text-slate-400" />
                                Settings
                            </Link>
                        </div>

                        <!-- Logout -->
                        <div class="border-t border-slate-100 py-1.5">
                            <button
                                type="button"
                                class="w-full flex items-center cursor-pointer gap-3 px-4 py-2.5 text-sm text-red-500 hover:text-red-700 hover:bg-red-50/60 transition-colors"
                                @click="logout"
                            >
                                <LogOut class="w-4 h-4" />
                                Sign Out
                            </button>
                        </div>
                    </div>
                </Transition>
            </div>
        </div>
    </header>
</template>

<style scoped>
/* ─── Dropdown animation ─────────────────────────────────────────────────────── */
.dropdown-enter-active {
    transition: all 0.2s cubic-bezier(0.22, 1, 0.36, 1);
}
.dropdown-leave-active {
    transition: all 0.15s ease-in;
}
.dropdown-enter-from {
    opacity: 0;
    transform: translateY(-8px) scale(0.97);
}
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-4px) scale(0.98);
}

/* ─── Badge animation ────────────────────────────────────────────────────────── */
.badge-enter-active {
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}
.badge-leave-active {
    transition: all 0.15s ease-in;
}
.badge-enter-from {
    opacity: 0;
    transform: scale(0);
}
.badge-leave-to {
    opacity: 0;
    transform: scale(0);
}
</style>
