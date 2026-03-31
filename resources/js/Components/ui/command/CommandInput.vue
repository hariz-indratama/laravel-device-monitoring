<script setup>
import { CommandInput } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({ class: String });
</script>

<template>
    <CommandInput :class="cn('flex h-11 w-full rounded-md bg-transparent py-3 text-sm outline-none placeholder:text-muted-foreground disabled:cursor-not-allowed disabled:opacity-50', props.class)">
        <slot />
    </CommandInput>
</template>
