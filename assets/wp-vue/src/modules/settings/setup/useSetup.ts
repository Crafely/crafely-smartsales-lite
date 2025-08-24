import { ref, onMounted, useTemplateRef } from 'vue'
import { storeToRefs } from 'pinia'
import { useWizardStore } from '@/stores/wizardStore'

export const useSetup = () => {
    const wizardStore = useWizardStore()
    const setupEditFormRef =
        useTemplateRef<HTMLInputElement>('setupEditFormRef')
    const loading = ref(false)

    const { wizards } = storeToRefs(wizardStore)

    const getWizards = async () => {
        loading.value = true
        try {
            await wizardStore.getWizards()
            setupEditFormRef.value?.form &&
                setupEditFormRef.value?.form.setValues(wizards.value)
        } finally {
            loading.value = false
        }
    }

    const updateWizards = async (payload: any) => {
        loading.value = true
        try {
            const response = await wizardStore.updateWizards(payload)
            if (!response.success) {
                const editForm = setupEditFormRef.value?.form
                editForm && editForm.setErrors(response.error)
            }
        } finally {
            loading.value = false
        }
    }

    onMounted(async () => {
        await getWizards()
    })

    return { wizards, updateWizards, loading }
}
