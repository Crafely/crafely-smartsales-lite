import { storeToRefs } from 'pinia'
import { debounce } from 'lodash'
import { useRoute, useRouter } from 'vue-router'
import { useThreadStore } from '@/stores/threadStore'
export const useThread = () => {
    const threadStore = useThreadStore()
    const { threads, activeThread } = storeToRefs(threadStore)
    const router = useRouter()
    const route = useRoute()

    const getThreads = async () => {
        await threadStore.getThreads()
    }

    const createThread = async () => {
        const payload = {
            name: 'Default thread',
        }
        const { data } = await threadStore.createThread(payload)
        threadStore.setActiveThread(data.id)
        switchThread(data.id)
    }

    const updateThread = debounce(async () => {
        threadStore.updateThread({
            id: activeThread.value?.id,
            name: activeThread.value?.name,
        })
    }, 2000)

    const deleteThread = async (threadId: string) => {
        await threadStore.deleteThread(threadId)
        if (threads.value.length > 0) {
            switchThread(threads.value[0].id as string)
        } else {
            threadStore.setActiveThread(null)
            router.replace({
                name: route.name,
                query: {
                    ...route.query,
                    thread_id: null,
                },
            })
        }
    }

    const switchThread = async (id: string) => {
        threadStore.setActiveThread(id)
        await router.replace({
            name: route.name,
            query: {
                ...route.query,
                thread_id: id,
            },
        })
    }

    const initializeThread = async () => {
        await getThreads()
        if (route.query.thread_id) {
            switchThread(route.query.thread_id as string)
            return
        }
        if (threads.value.length > 0) {
            switchThread(threads.value[0].id)
        }
    }

    return {
        threads,
        activeThread,
        updateThread,

        createThread,
        deleteThread,
        switchThread,
        initializeThread,
    }
}
