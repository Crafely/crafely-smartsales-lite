import { ref, h, useTemplateRef, onMounted, VNode } from 'vue'
import { useCustomerStore } from '@/stores/customerStore'
import { storeToRefs } from 'pinia'
import { ReloadIcon } from '@radix-icons/vue'
import { useRoute } from '@/composable/useVueRouter'
import { subMenuTabs } from './customerConfig'
import type { Tabs, Customer } from '@/types'
export const useCustomer = () => {
    const customerEditFormRef = useTemplateRef<HTMLInputElement>(
        'customerEditFormRef'
    )
    const customerCreateFormRef = useTemplateRef<HTMLInputElement>(
        'customerCreateFormRef'
    )
    const activeTab = ref<Tabs>('details')
    const { routerReplace, customerId } = useRoute()
    const customerStore = useCustomerStore()
    const { customers, activeCustomer } = storeToRefs(customerStore)
    const createCustomer = async (payload: any, actions: any) => {
        const { success, error } = await customerStore.createCustomer(payload)
        if (!success) {
            const createForm = customerCreateFormRef.value?.form
            createForm?.setErrors(error)
        } else {
            actions.resetForm()
        }
    }

    const updateCustomer = async (payload: any) => {
        payload.id = activeCustomer.value?.id
        const { success, error } = await customerStore.updateCustomer(payload)
        if (!success) {
            const editForm = customerEditFormRef.value?.form
            editForm?.setErrors(error)
        }
    }

    const switchTab = (tab: string) => {
        if (tab === 'edit') {
            setTimeout(() => {
                const editForm = customerEditFormRef.value?.form
                editForm?.setValues(activeCustomer.value)
            }, 50)
        }
    }

    const getSingleCustomer = async (customerId: number) => {
        const tab = subMenuTabs.value.find(
            (tab) => tab.value === activeTab.value
        )
        const _tab = { ...tab }
        if (tab && tab.value !== 'create') {
            tab.icon = h(ReloadIcon, { class: 'w-4 h-4 animate-spin' })
        }
        await customerStore.getSingleCustomer(customerId)
        customerEditFormRef.value?.form?.setValues(activeCustomer.value)
        if (tab && tab.value !== 'create') {
            tab.icon = _tab.icon as VNode
        }
        routerReplace({ customerId: customerId })
    }

    const deleteCustomer = async (customer: Customer) => {
        await customerStore.deleteCustomer(customer.id)
        routerReplace({ customerId: undefined })
    }

    onMounted(async () => {
        await customerStore.getCustomers()
        if (customerId.value || customers.value.length > 0) {
            await getSingleCustomer(customerId.value || customers.value[0].id)
        }
    })

    return {
        subMenuTabs,
        activeTab,
        switchTab,

        customers,
        activeCustomer,
        createCustomer,
        deleteCustomer,
        getSingleCustomer,
        updateCustomer,
    }
}
