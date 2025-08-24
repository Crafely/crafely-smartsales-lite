import { ref, h, useTemplateRef, onMounted } from 'vue'
import { useOutletStore } from '@/stores/outletStore'
import { useCounterStore } from '@/stores/counterStore'
import { storeToRefs } from 'pinia'
import { useRoute } from '@/composable/useVueRouter'
import { ReloadIcon } from '@radix-icons/vue'
import { subMenuTabs } from './outletConfig'
import type { Outlet, Tabs } from '@/types'

export const useOutlet = () => {
    const outletEditFormRef =
        useTemplateRef<HTMLInputElement>('outletEditFormRef')
    const outletCreateFormRef = useTemplateRef<HTMLInputElement>(
        'outletCreateFormRef'
    )
    const activeTab = ref<Tabs>('details')

    const { routerReplace, outletId } = useRoute()
    const outletStore = useOutletStore()
    const counterStore = useCounterStore()
    const { outlets, activeOutlet } = storeToRefs(outletStore)
    const createOutlet = async (payload: any, actions: any) => {
        const response = await outletStore.createOutlet(payload)
        if (!response.success) {
            const createForm = outletCreateFormRef.value?.form
            createForm?.setErrors(response.error)
        } else {
            actions.resetForm()
            activeOutlet.value = response.data
        }
    }

    const updateOutlet = async (payload: any) => {
        payload.id = activeOutlet.value?.id
        const response = await outletStore.updateOutlet(payload)
        if (!response.success) {
            const editForm = outletEditFormRef.value?.form
            editForm?.setErrors(response.error)
        }
    }

    const switchTab = (tab: string) => {
        if (tab === 'edit') {
            setTimeout(() => {
                if (activeOutlet.value) {
                    outletEditFormRef.value?.form?.setValues(activeOutlet.value)
                }
            }, 50)
        }
    }

    const getSingleOutlet = async (outletId: number) => {
        const tab = subMenuTabs.value.find(
            (tab) => tab.value === activeTab.value
        )
        const _tab = { ...tab }
        if (tab && tab.value !== 'create') {
            tab.icon = h(ReloadIcon, { class: 'w-4 h-4 animate-spin' })
        }
        activeOutlet.value = outlets.value.find(
            (outlet: Outlet) => outlet.id === outletId
        )
        outletEditFormRef.value?.form?.setValues(activeOutlet.value)
        if (tab && tab.value !== 'create') {
            tab.icon = _tab.icon as any
        }
        await counterStore.getCounters(outletId)
        routerReplace({ outletId: outletId })
    }

    const deleteOutlet = async (outletId: number) => {
        await outletStore.deleteOutlet(outletId)
    }

    onMounted(async () => {
        await outletStore.getOutlets()
        if (outletId.value || outlets.value.length > 0) {
            const _outletId = Number(outletId.value || outlets.value[0].id)
            await getSingleOutlet(_outletId)
            counterStore.getCounters(_outletId)
        }
    })

    return {
        subMenuTabs,
        outlets,
        activeOutlet,
        activeTab,
        switchTab,
        createOutlet,
        deleteOutlet,
        getSingleOutlet,
        updateOutlet,
    }
}
