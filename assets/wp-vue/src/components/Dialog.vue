<script setup lang="ts">
    import { Button } from '@/components/ui/button'
    import {
        Dialog,
        DialogDescription,
        DialogFooter,
        DialogHeader,
        DialogScrollContent,
        DialogTitle,
    } from '@/components/ui/dialog'
    const localModel = defineModel<boolean>()
</script>

<template>
    <Dialog v-model:open="localModel">
        <DialogScrollContent>
            <DialogHeader>
                <DialogTitle>Dialog Title</DialogTitle>
                <DialogDescription>
                    Lorem ipsum dolor sit amet consectetur adipisicing elit.
                    Nemo, molestias impedit.
                </DialogDescription>
            </DialogHeader>
            <slot />
            <DialogFooter>
                <Button type="submit"> Save changes </Button>
            </DialogFooter>
        </DialogScrollContent>
    </Dialog>
</template>
