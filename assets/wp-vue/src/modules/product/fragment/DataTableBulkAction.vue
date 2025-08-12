<template>
    <Button
        v-if="totalSelectedRows"
        variant="outline"
        size="sm"
        class="h-8 border-dashed"
        @click="handleDelete"
        :icon="TrashIcon"
        :loading="loading"
    >
        Delete ({{ totalSelectedRows }})
    </Button>
    <ConfirmDialog
        title="Are you sure you want to delete those products?"
        description="This action will move the product to the trash and can be restored within 30 days."
        v-model="showConfirmDialog"
        :deleting="deleting"
        @confirm="confirmDelete(handleBulkDelete)"
    />
</template>

<script lang="ts" setup>
    import { Button } from '@/components/ui/button'
    import { TrashIcon } from '@radix-icons/vue'
    import { computed, ref } from 'vue'
    import { ConfirmDialog, useConfirmDialog } from '@/components/confirmDialog'

    const { deleting, showConfirmDialog, handleDelete, confirmDelete } =
        useConfirmDialog()
    const props = defineProps<{
        table: any
        deleteBulkProduct: (ids: number[]) => Promise<void>
    }>()
    const loading = ref(false)
    const totalSelectedRows = computed(
        () => props.table.getFilteredSelectedRowModel().rows.length
    )

    const handleBulkDelete = async () => {
        loading.value = true
        const ids = props.table
            .getFilteredSelectedRowModel()
            .rows.map((row) => row.original.id)
        await props.deleteBulkProduct(ids)
        loading.value = false
        props.table.resetRowSelection()
    }
</script>
