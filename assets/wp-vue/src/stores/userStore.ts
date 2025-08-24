import { ref, computed, onMounted } from 'vue'
import { defineStore } from 'pinia'
import { useAxios } from '@/composable/useAxios'
import { useRouteVisibility } from '@/composable/useRouteVisibility'
import { usePermission } from '@/composable/usePermission'
import { toast } from 'vue-sonner'

import type { User } from '@/types'

export const useUserStore = defineStore('user', () => {
    const users = ref<User[]>([])
    const token = ref()
    const authUser = ref<User | null>(null)
    const userRoles = computed(() => authUser.value?.roles || [])
    const userPermissions = computed(() => authUser.value?.permissions || [])
    const { can } = usePermission({ userPermissions })
    const { routesByRoles } = useRouteVisibility({ userRoles })
    const userCredentials = {
        //@ts-ignore
        username: import.meta.env.VITE_APP_USER_NAME,
        //@ts-ignore
        password: import.meta.env.VITE_APP_PASSWORD,
    }
    const activeUser = ref<User>()
    const error = ref<any>(null)
    const loading = ref(false)
    const { get, post, put, remove } = useAxios()

    const getUsers = async () => {
        const { data } = await get('users')
        users.value = data
    }

    const getCurrentUser = async () => {
        const { data } = await get('/users/current')
        authUser.value = data
    }

    const createUser = async (payload: User) => {
        loading.value = true
        try {
            const { data, error, success, message } = await post(
                'users',
                payload,
                {
                    auth: userCredentials,
                }
            )
            if (success) {
                users.value = Array.from([...users.value, data])
            }
            toast[success ? 'success' : 'error'](message)
            return { data, error, success, message }
        } catch (err: any) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const deleteUser = async (id: number) => {
        const { message, success } = await remove(`users/${id}`)
        if (success) {
            users.value = users.value.filter((outlet) => outlet.id !== id)
        }
        toast.success(message)
    }

    const logout = async () => {
        loading.value = true
        try {
            await post('users/logout')
            toast.success('Logged out successfully')
            window.location.reload()
        } catch (err: any) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const updateUser = async (payload: User) => {
        const { data, error, success, message } = await put(
            `users/${payload.id}`,
            payload
        )
        if (success) {
            users.value = users.value.map((user) =>
                user.id === payload.id ? data : user
            )
            if (payload.id === authUser.value?.id) {
                authUser.value = data
            }
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const setActiveUser = (user: User) => {
        activeUser.value = user
    }

    onMounted(async () => {
        await getCurrentUser()
    })

    return {
        users,
        token,
        getUsers,
        getCurrentUser,
        authUser,
        routesByRoles,
        can,

        activeUser,
        setActiveUser,
        updateUser,
        createUser,
        deleteUser,
        logout,
    }
})
