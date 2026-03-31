<script setup>
import { SelectSeparator } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({ class: String });
</script>

<template>
    <SelectSeparator :class="cn('-mx-1 my-1 h-px bg-muted', props.class)" />
</template>
