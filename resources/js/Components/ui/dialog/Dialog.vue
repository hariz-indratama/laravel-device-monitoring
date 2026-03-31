<script setup>
import {
    DialogContent,
    DialogDescription,
    DialogOverlay,
    DialogPortal,
    DialogRoot,
    DialogTitle,
    DialogTrigger,
} from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    open: { type: Boolean, default: false },
    defaultOpen: Boolean,
    modal: { type: Boolean, default: true },
});
const emit = defineEmits(['update:open']);

const open = computed(() => props.open);
</script>

<script>
import { computed } from 'vue';
export default { name: 'Dialog' };
</script>

<template>
    <DialogRoot :open="open" :modal="modal" @update:open="(v) => emit('update:open', v)">
        <DialogTrigger as-child>
            <slot name="trigger" />
        </DialogTrigger>
        <DialogPortal>
            <DialogOverlay
                class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0"
            />
            <DialogContent
                class="fixed left-1/2 top-1/2 z-50 grid w-full max-w-lg -translate-x-1/2 -translate-y-1/2 gap-4 border bg-background p-6 shadow-lg duration-200 data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[state=closed]:slide-out-to-left-1/2 data-[state=closed]:slide-out-to-top-[48%] data-[state=open]:slide-in-from-left-1/2 data-[state=open]:slide-in-from-top-[48%] sm:rounded-lg"
            >
                <div class="flex flex-col space-y-1.5 text-center sm:text-left">
                    <DialogTitle class="text-lg font-semibold leading-none tracking-tight">
                        <slot name="title" />
                    </DialogTitle>
                    <DialogDescription class="text-sm text-muted-foreground">
                        <slot name="description" />
                    </DialogDescription>
                </div>
                <slot />
                <button
                    class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:pointer-events-none data-[state=open]:bg-accent data-[state=open]:text-muted-foreground"
                    aria-label="Close"
                    @click="emit('update:open', false)"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 6 6 18"/><path d="m6 6 12 12"/>
                    </svg>
                </button>
            </DialogContent>
        </DialogPortal>
    </DialogRoot>
</template>
