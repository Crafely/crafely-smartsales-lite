import { ref, h, useTemplateRef, onMounted, VNode } from 'vue'
import { useOrderStore } from '@/stores/orderStore'
import { storeToRefs } from 'pinia'
import { ReloadIcon } from '@radix-icons/vue'
import { useRoute } from '@/composable/useVueRouter'
import { Info, Edit } from 'lucide-vue-next'

import type { Tabs } from '@/types'
export const useOrder = () => {
    const orderEditFormRef =
        useTemplateRef<HTMLInputElement>('orderEditFormRef')
    const orderCreateFormRef =
        useTemplateRef<HTMLInputElement>('orderCreateFormRef')
    const activeTab = ref<Tabs>('details')
    const tabs = ref([
        {
            label: 'Details',
            value: 'details',
            icon: h(Info),
        },
        {
            value: 'edit',
            label: 'Edit',
            icon: h(Edit),
        },
    ])
    const { orderId, routerReplace } = useRoute()
    const orderStore = useOrderStore()
    const { orders, activeOrder, pagination } = storeToRefs(orderStore)
    const loading = ref(false)
    const error = ref(null)

    const showDeleteDialog = ref(false)

    const createOrder = async (payload: any, actions: any) => {
        const response = await orderStore.createOrder(payload)
        if (!response.success) {
            const createForm = orderCreateFormRef.value?.form
            if (createForm) {
                createForm.setErrors(response.error)
            }
        } else {
            actions.resetForm()
        }
    }

    const updateOrder = async (payload: any) => {
        payload.id = activeOrder.value!.id as number
        const response = await orderStore.updateOrder(payload)
        if (!response.success) {
            const editForm = orderEditFormRef.value?.form
            if (!editForm) return
            editForm.setErrors(response.error)
        }
    }

    const _deleteOrder = (id: number) => {
        showDeleteDialog.value = true
        orderId.value = id
    }

    const switchTab = (tab: string) => {
        if (tab === 'edit') {
            setTimeout(() => {
                orderEditFormRef.value?.form &&
                    orderEditFormRef.value?.form.setValues(activeOrder.value)
            }, 50)
        }
    }

    const getSingleOrder = async (orderId: number) => {
        const tab = tabs.value.find((tab) => tab.value === activeTab.value)
        const _tab = { ...tab }
        if (tab && tab.value !== 'create') {
            tab.icon = h(ReloadIcon, { class: 'w-4 h-4 animate-spin' })
        }
        await orderStore.getSingleOrder(orderId)
        if (orderEditFormRef.value?.form) {
            orderEditFormRef.value.form.setValues(activeOrder.value)
        }
        if (tab && tab.value !== 'create') {
            tab.icon = _tab.icon as VNode
        }
        routerReplace({ orderId: orderId })
    }

    const deleteOrder = async (orderId: number) => {
        await orderStore.deleteOrder(orderId)
        routerReplace({ orderId: undefined })
    }

    const getOrders = async (query = {}) => {
        await orderStore.getOrders(query)
    }

    onMounted(async () => {
        await getOrders()
        if (orderId.value || orders.value.length > 0) {
            await getSingleOrder(orderId.value || orders.value[0].id)
        }
    })

    return {
        tabs,
        orders,
        activeOrder,
        loading,
        error,
        pagination,
        activeTab,
        showDeleteDialog,
        getOrders,
        switchTab,
        createOrder,
        _deleteOrder,
        deleteOrder,
        getSingleOrder,
        updateOrder,
    }
}
