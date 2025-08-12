<script setup lang="ts">
    import {
        AlertDialog,
        AlertDialogCancel,
        AlertDialogContent,
        AlertDialogDescription,
        AlertDialogFooter,
        AlertDialogHeader,
        AlertDialogTitle,
    } from '@/components/ui/alert-dialog'
    import { Button } from '@/components/ui/button'
    import { ReloadIcon } from '@radix-icons/vue'
    const localModel = defineModel<boolean>()

    defineProps<{
        title: string
        description?: string
        deleting: boolean
    }>()
    defineEmits<{
        confirm: () => void
    }>()
</script>

<template>
    <AlertDialog v-model:open="localModel">
        <AlertDialogContent>
            <AlertDialogHeader>
                <AlertDialogTitle>{{ title }}</AlertDialogTitle>
                <AlertDialogDescription v-if="description">
                    {{ description }}</AlertDialogDescription
                >
            </AlertDialogHeader>
            <AlertDialogFooter>
                <AlertDialogCancel>Cancel</AlertDialogCancel>
                <Button :disabled="deleting" @click="$emit('confirm')">
                    <ReloadIcon
                        v-if="deleting"
                        class="w-4 h-4 mr-2 animate-spin"
                    />
                    Confirm
                </Button>
            </AlertDialogFooter>
        </AlertDialogContent>
    </AlertDialog>
</template>
