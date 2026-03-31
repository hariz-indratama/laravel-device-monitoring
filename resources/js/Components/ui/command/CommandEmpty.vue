<script setup>
import { CommandEmpty } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({ class: String });
</script>

<template>
    <CommandEmpty :class="cn('py-6 text-center text-sm', props.class)">
        <slot />
    </CommandEmpty>
</template>
