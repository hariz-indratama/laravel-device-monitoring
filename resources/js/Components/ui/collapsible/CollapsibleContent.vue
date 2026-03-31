<script setup>
import { CollapsibleContent as CollapsibleContentPrimitive } from 'radix-vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    class: String,
    forceMount: Boolean,
});
</script>

<template>
    <CollapsibleContentPrimitive
        :class="cn('overflow-hidden transition-all data-[state=closed]:animate-collapsible-up data-[state=open]:animate-collapsible-down', props.class)"
        :force-mount="forceMount"
    >
        <slot />
    </CollapsibleContentPrimitive>
</template>
