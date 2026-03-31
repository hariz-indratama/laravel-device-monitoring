<script setup>
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps({
    modelValue: [String, Number],
    class: String,
    type: { type: String, default: 'text' },
    placeholder: String,
    disabled: Boolean,
    readonly: Boolean,
    required: Boolean,
    autofocus: Boolean,
    id: String,
    name: String,
    min: [String, Number],
    max: [String, Number],
    step: [String, Number],
});

const emit = defineEmits(['update:modelValue', 'blur', 'focus', 'keydown']);

const inputValue = computed({
    get: () => props.modelValue,
    set: (val) => emit('update:modelValue', val),
});
</script>

<template>
    <input
        v-model="inputValue"
        :type="type"
        :class="
            cn(
                'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium file:text-foreground placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
                props.class,
            )
        "
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :required="required"
        :autofocus="autofocus"
        :id="id"
        :name="name"
        :min="min"
        :max="max"
        :step="step"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
        @keydown="$emit('keydown', $event)"
    />
</template>
