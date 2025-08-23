import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Counter } from '@/types'
import { useAxios } from '@/composable/useAxios'
import { toast } from 'vue-sonner'
export const useCounterStore = defineStore('counter', () => {
    const counters = ref<Counter[]>([])
    const activeCounter = ref<Counter>()
    const { get, post, put, remove } = useAxios()

    const getCounters = async (outletId: number) => {
        if (!outletId) {
            toast.error('Outlet id is required')
            return
        }
        const { data } = await get(`outlets/${outletId}/counters`)
        counters.value = data
    }

    const createCounter = async (outletId: number, payload: Counter) => {
        const { data, error, success, message } = await post(
            `outlets/${outletId}/counters`,
            payload
        )
        if (success) {
            counters.value = Array.from([...counters.value, data])
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const updateCounter = async (payload: Counter) => {
        const { data, error, success, message } = await put(
            `counters/${payload.id}`,
            payload
        )
        if (success) {
            counters.value = counters.value.map((counter) =>
                counter.id === payload.id ? data : counter
            )
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const getSingleCounter = async (counterId: number) => {
        const { data } = await get(`counters/${counterId}`)
        activeCounter.value = data
    }

    const deleteCounter = async (outletId: number, counterId: number) => {
        const { message, success } = await remove(
            `outlets/${outletId}/counters/${counterId}`
        )
        if (success) {
            counters.value = counters.value.filter(
                (counter) => counter.id !== counterId
            )
        }
        toast.success(message)
    }

    const setActiveCounter = (counter: Counter) => {
        activeCounter.value = counter
    }

    return {
        counters,
        getCounters,

        activeCounter,
        getSingleCounter,
        setActiveCounter,

        createCounter,
        updateCounter,
        deleteCounter,
    }
})
