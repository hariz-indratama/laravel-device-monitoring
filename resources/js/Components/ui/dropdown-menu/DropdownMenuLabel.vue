<script setup>
import { DropdownMenuLabel } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    class: String,
    inset: Boolean,
});
</script>

<template>
    <DropdownMenuLabel
        :class="cn('px-2 py-1.5 text-sm font-semibold', inset && 'pl-8', props.class)"
    >
        <slot />
    </DropdownMenuLabel>
</template>
