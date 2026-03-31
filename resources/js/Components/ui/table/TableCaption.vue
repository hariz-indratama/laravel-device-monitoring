<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({ class: String });
</script>

<template>
    <caption :class="cn('mt-4 text-sm text-muted-foreground', props.class)">
        <slot />
    </caption>
</template>
