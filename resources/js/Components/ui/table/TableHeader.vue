<script setup>
import { cn } from '@/lib/utils';

const props = defineProps({ class: String });
</script>

<template>
    <thead :class="cn('border-b bg-muted/50', props.class)">
        <slot />
    </thead>
</template>
