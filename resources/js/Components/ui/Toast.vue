<script setup lang="ts">
import { ref, watch } from 'vue'
import { CheckCircle, XCircle, Info, X } from 'lucide-vue-next'

const props = defineProps<{
    message: string
    type?: 'success' | 'error' | 'info'
    duration?: number
}>()

const emit = defineEmits<{
    close: []
}>()

const visible = ref(true)

watch(() => props.message, () => {
    visible.value = true
    if (props.duration !== 0) {
        setTimeout(() => { visible.value = false }, props.duration ?? 4000)
    }
}, { immediate: true })

const icon = {
    success: CheckCircle,
    error: XCircle,
    info: Info,
}

const colors = {
    success: 'bg-emerald-50 border-emerald-200 text-emerald-800',
    error: 'bg-red-50 border-red-200 text-red-800',
    info: 'bg-blue-50 border-blue-200 text-blue-800',
}

const iconColors = {
    success: 'text-emerald-500',
    error: 'text-red-500',
    info: 'text-blue-500',
}
</script>

<template>
    <Transition name="toast">
        <div
            v-if="visible && message"
            class="fixed top-4 right-4 z-[100] flex items-center gap-3 px-4 py-3 rounded-lg border shadow-lg min-w-72 max-w-md"
            :class="colors[type ?? 'success']"
        >
            <component :is="icon[type ?? 'success']" class="w-5 h-5 shrink-0" :class="iconColors[type ?? 'success']" />
            <span class="text-sm font-medium flex-1">{{ message }}</span>
            <button
                type="button"
                class="p-0.5 rounded hover:bg-black/5 transition-colors"
                @click="visible = false; emit('close')"
            >
                <X class="w-4 h-4" />
            </button>
        </div>
    </Transition>
</template>

<style scoped>
.toast-enter-active { transition: all 0.3s cubic-bezier(0.22, 1, 0.36, 1); }
.toast-leave-active { transition: all 0.2s ease-in; }
.toast-enter-from   { opacity: 0; transform: translateX(100%); }
.toast-leave-to     { opacity: 0; transform: translateX(100%); }
</style>
