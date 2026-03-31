<script setup>
import { CollapsibleRoot } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    open: Boolean,
    defaultOpen: Boolean,
    disabled: Boolean,
    class: String,
});

const model = defineModel({ type: Boolean, default: undefined });
</script>

<template>
    <CollapsibleRoot
        v-model:open="model"
        :open="props.open"
        :default-open="defaultOpen"
        :disabled="disabled"
        :class="cn('', props.class)"
    >
        <slot />
    </CollapsibleRoot>
</template>
