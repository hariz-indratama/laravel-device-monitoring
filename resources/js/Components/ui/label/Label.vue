<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    for: String,
    class: String,
    required: Boolean,
});

const labelId = `label-${Math.random().toString(36).slice(2, 9)}`;
</script>

<template>
    <label
        :for="for"
        :class="cn('text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70', props.class)"
    >
        <slot />
        <span v-if="required" class="text-danger ml-0.5">*</span>
    </label>
</template>
