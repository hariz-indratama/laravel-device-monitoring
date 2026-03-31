<script setup>
import { computed } from 'vue';
import { cva } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (v) =>
            ['default', 'destructive', 'outline', 'secondary', 'ghost', 'link', 'success', 'warning'].includes(v),
    },
    size: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'sm', 'lg', 'icon', 'xl'].includes(v),
    },
    as: {
        type: String,
        default: 'button',
    },
    class: String,
    disabled: Boolean,
    loading: Boolean,
});

const variants = cva(
    'inline-flex items-center justify-center gap-2 whitespace-nowrap rounded-md text-sm font-medium transition-colors focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:pointer-events-none disabled:opacity-50 [&_svg]:pointer-events-none [&_svg]:size-4 [&_svg]:shrink-0',
    {
        variants: {
            variant: {
                default: 'bg-primary text-primary-foreground hover:bg-secondary shadow-sm',
                destructive: 'bg-danger text-white hover:bg-danger/90 shadow-sm',
                outline: 'border border-input bg-background hover:bg-muted hover:text-foreground',
                secondary: 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
                ghost: 'hover:bg-muted hover:text-foreground',
                link: 'text-primary underline-offset-4 hover:underline',
                success: 'bg-success text-white hover:bg-success/90 shadow-sm',
                warning: 'bg-warning text-white hover:bg-warning/90 shadow-sm',
            },
            size: {
                default: 'h-10 px-4 py-2',
                sm: 'h-9 rounded-md px-3',
                lg: 'h-12 rounded-md px-8 text-base',
                xl: 'h-14 rounded-lg px-10 text-lg font-semibold',
                icon: 'h-10 w-10',
            },
        },
        defaultVariants: {
            variant: 'default',
            size: 'default',
        },
    },
);

const tag = computed(() => props.as);

defineExpose({ variant: props.variant, size: props.size });
</script>

<template>
    <component
        :is="tag"
        :class="cn(variants({ variant, size }), props.class)"
        :disabled="disabled || loading"
    >
        <svg
            v-if="loading"
            class="animate-spin"
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
        >
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
            <path
                class="opacity-75"
                fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"
            />
        </svg>
        <slot />
    </component>
</template>
