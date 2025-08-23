import { ref } from 'vue'

export const useConfirmDialog = () => {
    const activeItem = ref(null)
    const deleting = ref(false)
    const showConfirmDialog = ref(false)
    const handleDelete = (item) => {
        showConfirmDialog.value = true
        activeItem.value = item
    }

    const confirmDelete = async (cb: (activeItem: any) => Promise<void>) => {
        deleting.value = true
        await cb(activeItem.value)
        activeItem.value = null
        deleting.value = false
        showConfirmDialog.value = false
    }

    return {
        deleting,
        showConfirmDialog,
        handleDelete,
        confirmDelete,
    }
}
