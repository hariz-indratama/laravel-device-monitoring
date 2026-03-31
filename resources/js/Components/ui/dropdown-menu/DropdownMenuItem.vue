<script setup>
import { DropdownMenuItem } from 'reka-ui';
import { cn } from '@/lib/utils';

const props = defineProps({
    class: String,
    disabled: Boolean,
    inset: Boolean,
    destructive: Boolean,
});
</script>

<template>
    <DropdownMenuItem
        :class="cn(
            'relative flex cursor-default select-none items-center gap-2 rounded-sm px-2 py-1.5 text-sm outline-none transition-colors focus:bg-muted focus:text-foreground data-[disabled]:pointer-events-none data-[disabled]:opacity-50 [&>svg]:size-4 [&>svg]:shrink-0',
            inset && 'pl-8',
            destructive && 'text-danger focus:bg-danger/10',
            props.class,
        )"
        :disabled="disabled"
    >
        <slot />
    </DropdownMenuItem>
</template>
