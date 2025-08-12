import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Thread } from '@/types'
import { useAxiosNode } from '@/composable/useAxios.node'
import { toast } from 'vue-sonner'
import { useUserStore } from '@/stores/userStore'
export const useThreadStore = defineStore('thread', () => {
    const userStore = useUserStore()
    const threads = ref<Thread[]>([])
    const activeThread = ref<Thread>()
    const loading = ref(false)
    const _token = localStorage.getItem('_token')
    const { get, post, put, remove } = useAxiosNode(_token)

    const getThreads = async () => {
        const { data, success } = await get('thread')
        threads.value = data
        return {
            data,
            success,
        }
    }

    const createThread = async (payload: any) => {
        loading.value = true
        try {
            const { data, error, success, message } = await post(
                'thread',
                payload,
                {
                    auth: userStore.userCredentials,
                }
            )
            if (success) {
                threads.value = Array.from([...threads.value, data])
            }
            toast[success ? 'success' : 'error'](message)
            return { data, error, success, message }
        } catch (err) {
            throw err
        } finally {
            loading.value = false
        }
    }

    const updateThread = async (payload: any) => {
        loading.value = true
        try {
            const { data, error, success, message } = await put(
                `thread/${payload.id}`,
                payload
            )
            if (success) {
                threads.value = threads.value.map((thread) =>
                    thread.id === payload.id ? data : thread
                )
            }
            toast[success ? 'success' : 'error'](message)
            return { data, error, success, message }
        } catch (err) {
            throw err
        } finally {
            loading.value = false
        }
    }

    const deleteThread = async (id: string) => {
        const { message, success } = await remove(`thread/${id}`)
        if (success) {
            threads.value = threads.value.filter((thread) => thread.id !== id)
        }
        toast.success(message)
    }

    const setActiveThread = (id: string | null) => {
        activeThread.value = threads.value.find((thread) => thread.id === id)
    }

    return {
        threads,
        getThreads,

        activeThread,
        setActiveThread,

        updateThread,
        createThread,
        deleteThread,
    }
})
