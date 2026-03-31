<script setup>
import { SheetContent, SheetDescription, SheetOverlay, SheetPortal, SheetRoot, SheetTitle, SheetTrigger, SheetClose, SheetFooter, SheetHeader } from 'vaul-vue';
import { cn } from '@/lib/utils';

defineProps({
    open: { type: Boolean, default: false },
    side: { type: String, default: 'right', validator: (v) => ['top', 'bottom', 'left', 'right'].includes(v) },
    class: String,
});
const emit = defineEmits(['update:open']);
</script>

<script>
export default { name: 'Sheet' };
</script>

<template>
    <SheetRoot :open="open" @update:open="(v) => emit('update:open', v)">
        <SheetTrigger as-child>
            <slot name="trigger" />
        </SheetTrigger>
        <SheetPortal>
            <SheetOverlay class="fixed inset-0 z-50 bg-black/50 backdrop-blur-sm data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0" />
            <SheetContent
                :side="side"
                :class="cn(
                    'fixed z-50 gap-4 bg-background p-6 shadow-lg transition ease-in-out data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:duration-300 data-[state=open]:duration-500',
                    side === 'right' && 'inset-y-0 right-0 h-full w-3/4 border-l sm:max-w-sm',
                    side === 'left' && 'inset-y-0 left-0 h-full w-3/4 border-r sm:max-w-sm',
                    side === 'bottom' && 'inset-x-0 bottom-0 h-auto border-t rounded-t-xl',
                    side === 'top' && 'inset-x-0 top-0 h-auto border-b rounded-b-xl',
                    props.class,
                )"
            >
                <SheetHeader v-if="$slots.header || $slots.title" class="text-left">
                    <SheetTitle>
                        <slot name="title" />
                    </SheetTitle>
                    <SheetDescription v-if="$slots.description">
                        <slot name="description" />
                    </SheetDescription>
                </SheetHeader>

                <slot />

                <SheetFooter v-if="$slots.footer" class="mt-auto">
                    <slot name="footer" />
                </SheetFooter>
                <SheetClose class="absolute right-4 top-4 rounded-sm opacity-70 ring-offset-background transition-opacity hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2">
                    <slot name="close" />
                </SheetClose>
            </SheetContent>
        </SheetPortal>
    </SheetRoot>
</template>
