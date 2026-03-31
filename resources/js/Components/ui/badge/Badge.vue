<script setup>
import { computed } from 'vue';
import { cva } from 'class-variance-authority';
import { cn } from '@/lib/utils';

const props = defineProps({
    variant: {
        type: String,
        default: 'default',
        validator: (v) => ['default', 'secondary', 'destructive', 'outline', 'success', 'warning'].includes(v),
    },
    class: String,
});

const variants = cva(
    'inline-flex items-center rounded-full border px-2.5 py-0.5 text-xs font-semibold transition-colors focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    {
        variants: {
            variant: {
                default: 'border-transparent bg-primary text-primary-foreground',
                secondary: 'border-transparent bg-secondary text-secondary-foreground',
                destructive: 'border-transparent bg-danger text-white',
                outline: 'text-foreground',
                success: 'border-transparent bg-success text-white',
                warning: 'border-transparent bg-warning text-white',
            },
        },
        defaultVariants: { variant: 'default' },
    },
);
</script>

<template>
    <span :class="cn(variants({ variant }), props.class)">
        <slot />
    </span>
</template>
