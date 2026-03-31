<script setup>
import { CommandDialog, CommandEmpty, CommandGroup, CommandInput, CommandItem, CommandList, CommandSeparator, CommandRoot } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    open: Boolean,
    class: String,
});
const emit = defineEmits(['update:open']);
</script>

<template>
    <CommandRoot :class="cn('flex h-full w-full flex-col overflow-hidden rounded-md bg-popover text-popover-foreground', props.class)">
        <slot />
    </CommandRoot>
</template>
