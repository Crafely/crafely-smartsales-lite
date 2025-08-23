import { ref, onMounted, h, computed, useTemplateRef } from 'vue'
import { useAppStore } from '@/stores/appStore'
import { useInvoiceStore } from '@/stores/invoiceStore'
import { useCustomerStore } from '@/stores/customerStore'
import { useOutletStore } from '@/stores/outletStore'
import { useProductStore } from '@/stores/productStore'
import { useRoute } from '@/composable/useVueRouter'
import { storeToRefs } from 'pinia'

import type { Customer, Product, Tabs } from '@/types'
import { subMenuTabs } from './invoiceConfig'

export const useInvoice = () => {
    const app = useAppStore()
    const outlet = useOutletStore()
    const productStore = useProductStore()
    const customerStore = useCustomerStore()
    const invoiceStore = useInvoiceStore()

    const { appSettings } = storeToRefs(app)
    const { outlets } = storeToRefs(outlet)
    const { products, searchQuery } = storeToRefs(productStore)
    const { customers } = storeToRefs(customerStore)

    const { invoiceId, routerReplace } = useRoute()
    const { loading, invoices, pagination, activeInvoice, invoiceForm } =
        storeToRefs(invoiceStore)

    const invoiceEditFormRef =
        useTemplateRef<HTMLInputElement>('invoiceEditFormRef')
    const invoiceCreateFormRef = useTemplateRef<HTMLInputElement>(
        'invoiceCreateFormRef'
    )

    // const loading = ref(false)

    const activeTab = ref<Tabs>('details')

    const hasOutlet = computed(() => appSettings.value?.has_outlet === true)

    const invoiceOutlets = computed(() => {
        return appSettings.value?.has_outlet === true ? outlets : ''
    })

    const selectedOutlet = computed(() => {
        return outlets.value.find(
            (outlet) => outlet.id === invoiceForm.value.outlet_id
        )
    })
    const selectedCustomer = computed(() => {
        return customers.value.find(
            (customer) => customer.id === invoiceForm.value.customer_id
        )
    })

    const itemTotalPrice = computed(() =>
        invoiceForm.value.line_items.map(
            (item) =>
                Number(item.quantity || 0) * Number(item.custom_price || 0)
        )
    )

    const subTotalPrice = computed(() => {
        return invoiceForm.value.line_items.reduce(
            (total, item) => total + item.quantity * item.custom_price,
            0
        )
    })

    const vatCalculate = computed(() => {
        return (subTotalPrice.value * invoiceForm.value.vat) / 100
    })

    const finalTotal = computed(() => {
        return subTotalPrice.value + vatCalculate.value
    })

    const createCustomer = async (payload: Customer) => {
        await customerStore.createCustomer(payload)
    }

    const filteredProducts = async () => {
        await productStore.getProductsByQuery()
    }

    const handleCleare = () => {
        searchQuery.value = ''
    }

    const handleAddProduct = (product: Product) => {
        const productObj = {
            product_id: product.id,
            quantity: 1,
            custom_name: product.name,
            custom_price: product.price,
            custom_description: product.short_description,
        }

        // const existingItem = invoiceForm.value.line_items.find(
        //     (item) => item.product_id === product.id
        // )
        // if (existingItem) {
        //     existingItem.quantity++
        // } else {
        //     invoiceForm.value.line_items.push(productObj)
        // }
        invoiceForm.value.line_items.push(productObj)
    }

    const handleRemove = (product: Product) => {
        const index = invoiceForm.value.line_items.findIndex(
            (item) => item.product_id === product.product_id
        )
        if (index !== -1) {
            invoiceForm.value.line_items.splice(index, 1)
        }
    }

    const submitInvoice = () => {
        loading.value = true
        try {
            const response = invoiceStore.createInvoice(invoiceForm.value)
            if (response) {
                console.log('Invoice created successfully:', response)
            }
        } catch (error: any) {
            console.error(error)
        } finally {
            // invoiceForm.value.line_items = []
        }
        loading.value = false
    }

    const updateInvoice = async (payload: any) => {
        payload.id = activeInvoice.value.id
        const response = await invoiceStore.updateInvoice(payload)
        if (!response.success) {
            const editForm = invoiceEditFormRef.value?.form
            editForm && editForm.setErrors(response.error)
        }
    }

    const deleteInvoice = async (id: any) => {
        const response = await invoiceStore.deleteInvoice(id)
        if (response.success) {
            createNewInvoice()
        } else {
            console.error('Failed to delete invoice:', response.error)
        }
    }

    const getInvoices = async (query = {}) => {
        loading.value = true
        await invoiceStore.getInvoice(query)
        loading.value = false
    }

    const getSingleInvoice = async (invoiceId: number) => {
        await invoiceStore.getSingleInvoice(invoiceId)
        invoiceEditFormRef.value?.form &&
            invoiceEditFormRef.value?.form.setValues(activeInvoice.value)
        routerReplace({ invoiceId: invoiceId })
    }

    const createNewInvoice = () => {
        loading.value = true
        try {
            activeTab.value = 'details'
            invoiceForm.value = {
                customer_id: null,
                outlet_id: null,
                line_items: [],
                vat: 0,
                issue_date: new Date().toISOString().split('T')[0],
                due_date: '',
            }
            selectedCustomer.value = null
            selectedOutlet.value = null

            activeInvoice.value = null
            routerReplace({ invoiceId: null })
        } catch (error) {
            console.error('Error creating new invoice:', error)
        }

        loading.value = false
    }

    onMounted(async () => {
        await getInvoices()
        await outlet.getOutlets()
        await productStore.getProducts()
        await customerStore.getCustomers()
        await invoiceStore.getInvoice()
    })

    // Return
    return {
        loading,
        hasOutlet,
        appSettings,
        selectedOutlet,
        selectedCustomer,
        invoiceOutlets,
        activeTab,
        subMenuTabs,
        customers,
        createCustomer,
        products,
        searchQuery,
        filteredProducts,
        handleCleare,
        handleAddProduct,
        handleRemove,
        itemTotalPrice,
        subTotalPrice,
        vatCalculate,
        finalTotal,
        invoiceForm,
        submitInvoice,
        updateInvoice,
        deleteInvoice,
        invoices,
        pagination,
        getInvoices,
        getSingleInvoice,
        activeInvoice,
        createNewInvoice,
    }
}
