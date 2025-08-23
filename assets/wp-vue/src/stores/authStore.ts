import { defineStore } from 'pinia'
import { useCrafelyAi } from '@/composable/useCrafelyAi'
import { toast } from 'vue-sonner'
export const useAuthStore = defineStore('auth', () => {
    const { post } = useCrafelyAi()

    const login = async (payload: any) => {
        const { data, error, success, message } = await post(
            `user/login`,
            payload
        )
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const register = async (payload: any) => {
        const { data, error, success, message } = await post(
            `user/registration`,
            payload
        )
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    return {
        login,
        register,
    }
})
