<script setup lang="ts">
import { SelectValue } from 'reka-ui'
import { useForwardProps } from 'reka-ui'

defineProps<{
    placeholder?: string
}>()
</script>

<template>
    <SelectValue :placeholder="placeholder">
        <slot />
    </SelectValue>
</template>
