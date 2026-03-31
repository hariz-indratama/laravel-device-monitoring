<script setup>
import { TabsContent, TabsIndicator, TabsList, TabsRoot, TabsTrigger } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    defaultValue: String,
    modelValue: String,
    class: String,
});
const emit = defineEmits(['update:modelValue']);
</script>

<template>
    <TabsRoot
        :default-value="defaultValue"
        :model-value="modelValue"
        @update:model-value="(v) => emit('update:modelValue', v)"
        :class="cn('w-full', props.class)"
    >
        <slot />
    </TabsRoot>
</template>
