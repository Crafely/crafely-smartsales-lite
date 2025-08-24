import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useAxios } from '@/composable/useAxios'
import type { Wizard } from '@/types'
import { toast } from 'vue-sonner'
export const useWizardStore = defineStore('wizard', () => {
    const wizards = ref<Wizard | null>(null)
    const error = ref(null)
    const { get, put } = useAxios()

    const getWizards = async () => {
        try {
            const { data } = await get('wizard')
            wizards.value = data
        } catch (err) {
            throw err
        }
    }

    const updateWizards = async (payload: Partial<Wizard>) => {
        try {
            const { data, error, success, message } = await put(
                'wizard',
                payload
            )
            if (success) {
                wizards.value = { ...wizards.value, ...data }
            }
            toast[success ? 'success' : 'error'](message)
            return { data, error, success, message }
        } catch (err) {
            error.value = err
            throw err
        }
    }

    return {
        wizards,
        getWizards,
        updateWizards,
    }
})
