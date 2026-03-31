<script setup>
import { Separator } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    orientation: { type: String, default: 'horizontal', validator: (v) => ['horizontal', 'vertical'].includes(v) },
    class: String,
});
</script>

<template>
    <Separator
        :orientation="orientation"
        :class="cn(
            'shrink-0 bg-border',
            orientation === 'horizontal' ? 'h-[1px] w-full' : 'h-full w-[1px]',
            props.class,
        )"
    />
</template>
