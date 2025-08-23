import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Outlet } from '@/types'
import { useAxios } from '@/composable/useAxios'
import { toast } from 'vue-sonner'
import { useUserStore } from '@/stores/userStore'

export const useOutletStore = defineStore('outlet', () => {
    const outlets = ref<Outlet[]>([])
    const activeOutlet = ref<Outlet>()
    const { get, post, put, remove } = useAxios()
    const userStore = useUserStore()

    const getOutlets = async () => {
        const { data } = await get('outlets')
        outlets.value = data
    }

    const createOutlet = async (payload: Outlet) => {
        const { data, error, success, message } = await post('outlets', payload)
        if (success) {
            outlets.value = Array.from([...outlets.value, data])
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const updateOutlet = async (payload: Outlet) => {
        const { data, error, success, message } = await put(
            `outlets/${payload.id}`,
            payload
        )
        if (success) {
            outlets.value = outlets.value.map((outlet) =>
                outlet.id === payload.id ? data : outlet
            )
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const getSingleOutlet = async (outletId: number) => {
        const { data } = await get(`outlets/${outletId}`)
        activeOutlet.value = data
    }

    const deleteOutlet = async (id: number) => {
        const { message, success } = await remove(`outlets/${id}`)
        if (success) {
            outlets.value = outlets.value.filter((outlet) => outlet.id !== id)
        }
        toast.success(message)
    }

    const setActiveOutlet = (outlet: Outlet) => {
        activeOutlet.value = outlet
    }

    return {
        outlets,
        getOutlets,

        activeOutlet,
        getSingleOutlet,
        setActiveOutlet,

        createOutlet,
        updateOutlet,
        deleteOutlet,
    }
})
