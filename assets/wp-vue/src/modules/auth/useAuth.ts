import { useTemplateRef, ref } from 'vue'
import { useAuthStore } from '@/stores/authStore'
import { useRouter } from 'vue-router'
export const useAuth = () => {
    const authStore = useAuthStore()
    const router = useRouter()
    const loginFormRef = useTemplateRef<HTMLInputElement>('loginFormRef')
    const registerFormRef = useTemplateRef<HTMLInputElement>('registerFormRef')
    const loading = ref(false)

    const login = async (payload: any, actions: any) => {
        loading.value = true
        const response = await authStore.login(payload)

        if (response.success) {
            localStorage.setItem('_token', response.data.token)
            router.push({ name: 'app.home' })
            actions.resetForm()
        } else {
            const loginForm = loginFormRef.value?.form

            if (loginForm) {
                const errorArray = response.error

                if (Array.isArray(errorArray) && errorArray.length > 0) {
                    const fieldErrors: Record<string, string> = {}

                    errorArray.forEach((e: any) => {
                        if (e.type === 'field' && e.path && e.msg) {
                            fieldErrors[e.path] = e.msg
                        }
                    })

                    loginForm.setErrors(fieldErrors)
                } else {
                    loginForm.setFieldError(
                        'email',
                        response.message || 'Login failed'
                    )
                }
            }
        }
        loading.value = false
    }

    const register = async (payload: any, actions: any) => {
        loading.value = true
        const response = await authStore.register(payload)

        if (response.success) {
            router.push({ name: 'app.login' })
            actions.resetForm()
        } else {
            const registerForm = registerFormRef.value?.form

            if (registerForm) {
                const errorArray = response.error

                if (Array.isArray(errorArray) && errorArray.length > 0) {
                    const fieldErrors: Record<string, string> = {}

                    errorArray.forEach((e: any) => {
                        if (e.type === 'field' && e.path && e.msg) {
                            fieldErrors[e.path] = e.msg
                        }
                    })

                    registerForm.setErrors(fieldErrors)
                } else {
                    registerForm.setFieldError(
                        'name',
                        response.message || 'Login failed'
                    )
                }
            }
        }
        loading.value = false
    }

    return {
        login,
        register,
        loading,
    }
}
