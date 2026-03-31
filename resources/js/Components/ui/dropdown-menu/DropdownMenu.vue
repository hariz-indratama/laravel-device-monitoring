<script setup>
import {
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuRoot,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from 'reka-ui';
import { cn } from '@/lib/utils';

defineProps({
    align: { type: String, default: 'end', validator: (v) => ['start', 'center', 'end'].includes(v) },
    side: { type: String, default: 'bottom', validator: (v) => ['top', 'right', 'bottom', 'left'].includes(v) },
});
</script>

<script>
export default { name: 'DropdownMenu' };
</script>

<template>
    <DropdownMenuRoot>
        <DropdownMenuTrigger as-child>
            <slot name="trigger" />
        </DropdownMenuTrigger>
        <DropdownMenuPortal>
            <DropdownMenuContent
                :align="align"
                :side="side"
                :class="cn(
                    'z-50 min-w-[8rem] overflow-hidden rounded-md border bg-popover p-1 text-popover-foreground shadow-md data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2',
                    props.class,
                )"
            >
                <slot />
            </DropdownMenuContent>
        </DropdownMenuPortal>
    </DropdownMenuRoot>
</template>
