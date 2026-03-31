<script setup>
import { computed } from 'vue';
import { CommandDialog as RekaCommandDialog } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({ open: Boolean, class: String });
const emit = defineEmits(['update:open']);
</script>

<template>
    <RekaCommandDialog :open="open" @update:open="(v) => emit('update:open', v)">
        <slot />
    </RekaCommandDialog>
</template>
