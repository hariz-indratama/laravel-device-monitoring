<script setup>
import { CollapsibleTrigger as CollapsibleTriggerPrimitive } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    class: String,
    as: {
        type: String,
        default: 'button',
    },
});

defineOptions({ inheritAttrs: false });
</script>

<template>
    <CollapsibleTriggerPrimitive
        :as="as"
        :class="cn('flex items-center', props.class)"
        v-bind="$attrs"
    >
        <slot />
    </CollapsibleTriggerPrimitive>
</template>
