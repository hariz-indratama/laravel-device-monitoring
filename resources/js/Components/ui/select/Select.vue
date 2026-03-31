<script setup lang="ts">
import { SelectRoot } from 'reka-ui'
import { useForwardPropsEmits } from 'reka-ui'

const props = defineProps<{
    modelValue?: string | number
    placeholder?: string
}>()

const emit = defineEmits<{
    (e: 'update:modelValue', value: string | number): void
}>()

const forwarded = useForwardPropsEmits(props, emit)
</script>

<template>
    <SelectRoot v-bind="forwarded">
        <slot />
    </SelectRoot>
</template>
