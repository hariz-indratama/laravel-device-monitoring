<script setup>
import { CommandList } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({ class: String });
</script>

<template>
    <CommandList :class="cn('max-h-[300px] overflow-y-auto overflow-x-hidden', props.class)">
        <slot />
    </CommandList>
</template>
