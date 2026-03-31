<script setup>
import { CommandItem } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    value: String,
    disabled: Boolean,
    class: String,
});
</script>

<template>
    <CommandItem
        :value="value"
        :disabled="disabled"
        :class="cn('relative flex cursor-default select-none items-center rounded-sm px-2 py-1.5 text-sm outline-none data-[disabled=true]:pointer-events-none data-[selected=true]:bg-accent data-[selected=true]:text-accent-foreground data-[disabled=true]:opacity-50', props.class)"
    >
        <slot />
    </CommandItem>
</template>
