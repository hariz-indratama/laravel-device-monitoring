<script setup lang="ts">
import { ref, watch } from 'vue'
import AlertDialog from '@/Components/ui/alert-dialog/AlertDialog.vue'
import Button from '@/Components/ui/button/Button.vue'

const props = withDefaults(defineProps<{
    open: boolean
    title?: string
    description?: string
    confirmText?: string
    cancelText?: string
    variant?: 'destructive' | 'warning' | 'success' | 'default'
    loading?: boolean
    closeOnConfirm?: boolean
}>(), {
    title: 'Konfirmasi',
    description: 'Apakah Anda yakin ingin melanjutkan?',
    confirmText: 'Ya, Lanjutkan',
    cancelText: 'Batal',
    variant: 'default',
    loading: false,
    closeOnConfirm: false,
})

const emit = defineEmits<{
    confirm: []
    cancel: []
    'update:open': [value: boolean]
}>()

const isOpen = ref(props.open)

watch(() => props.open, (val) => {
    isOpen.value = val
})

function handleConfirm() {
    if (props.closeOnConfirm) {
        emit('update:open', false)
    }
    emit('confirm')
}

function handleCancel() {
    emit('update:open', false)
    emit('cancel')
}
</script>

<template>
    <AlertDialog v-model:open="isOpen" :modal="true">
        <template #trigger>
            <slot name="trigger" />
        </template>

        <template #title>
            {{ title }}
        </template>
        <template #description>
            {{ description }}
        </template>
        <template #default>
            <div class="flex justify-end gap-3 pt-2">
                <Button
                    variant="secondary"
                    :disabled="loading"
                    @click="handleCancel"
                >
                    {{ cancelText }}
                </Button>
                <Button
                    :variant="variant === 'destructive' ? 'destructive' : variant === 'warning' ? 'warning' : variant === 'success' ? 'success' : 'default'"
                    :loading="loading"
                    @click="handleConfirm"
                >
                    {{ confirmText }}
                </Button>
            </div>
        </template>
    </AlertDialog>
</template>
