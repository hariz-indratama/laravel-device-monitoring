<script setup lang="ts">
import { ref, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import Sidebar from './Sidebar.vue'
import TopNavbar from './TopNavbar.vue'
import { useBroadcast } from '@/composables/useBroadcast'
import { Menu } from 'lucide-vue-next'
import Toast from '@/Components/ui/Toast.vue'

defineProps<{ title?: string }>()

const { connectionStatus } = useBroadcast()
const sidebarOpen = ref(false)

function openSidebar()  { sidebarOpen.value = true  }
function closeSidebar() { sidebarOpen.value = false }

const page = usePage()
const flashMessage = ref('')
const flashType = ref<'success' | 'error' | 'info'>('success')

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            flashMessage.value = flash.success
            flashType.value = 'success'
        } else if (flash?.error) {
            flashMessage.value = flash.error
            flashType.value = 'error'
        }
    },
    { immediate: true }
)
</script>

<template>
    <Toast :message="flashMessage" :type="flashType" @close="flashMessage = ''" />

    <div class="flex min-h-screen bg-slate-50">

        <!-- Mobile: backdrop overlay -->
        <Transition name="overlay">
            <div
                v-if="sidebarOpen"
                class="fixed inset-0 z-30 bg-black/50 backdrop-blur-sm lg:hidden"
                @click="closeSidebar"
            />
        </Transition>

        <!-- Desktop sidebar (always visible at lg+) -->
        <Sidebar
            :connection-status="connectionStatus"
            class="hidden lg:flex"
            @close="closeSidebar"
        />

        <!-- Mobile sidebar drawer -->
        <Transition name="drawer">
            <Sidebar
                v-if="sidebarOpen"
                :connection-status="connectionStatus"
                class="fixed inset-y-0 left-0 z-40 lg:hidden"
                @close="closeSidebar"
            />
        </Transition>

        <!-- Main content -->
        <div class="flex-1 flex flex-col min-w-0">
            <!-- Top Navbar (desktop & mobile) -->
            <TopNavbar @open-sidebar="openSidebar" />

            <!-- Page content -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 overflow-auto">
                <header v-if="title" class="mb-6 hidden lg:block">
                    <h1 class="text-2xl font-bold text-gray-900">{{ title }}</h1>
                </header>
                <slot />
            </main>
        </div>
    </div>
</template>

<style scoped>
/* Backdrop overlay */
.overlay-enter-active,
.overlay-leave-active {
    transition: opacity 0.2s ease;
}
.overlay-enter-from,
.overlay-leave-to {
    opacity: 0;
}

/* Mobile drawer slide-in */
.drawer-enter-active {
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.drawer-leave-active {
    transition: transform 0.2s ease-in;
}
.drawer-enter-from,
.drawer-leave-to {
    transform: translateX(-100%);
}
</style>
