<script setup>
import { CommandSeparator } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({ class: String });
</script>

<template>
    <CommandSeparator :class="cn('-mx-1 h-px bg-border', props.class)" />
</template>
