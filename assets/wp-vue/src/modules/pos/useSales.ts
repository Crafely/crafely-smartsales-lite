import { ref, onMounted, computed } from 'vue'
import { storeToRefs } from 'pinia'
import { isEmpty } from 'lodash'
import { useProductStore } from '@/stores/productStore'
import { useCustomerStore } from '@/stores/customerStore'
import { useSalesStore } from '@/stores/salesStore'
import type { Customer, Product, OrderForm } from '@/types'
import { formatAmount } from '@/utils'
import { toast } from 'vue-sonner'
import { number } from 'zod'

export const useSales = () => {
    const showCustomerForm = ref(false)
    const salesProducts = ref<Product[]>([])
    const orderForm = ref<OrderForm>({
        customer_id: null,
        payment_method: 'cash',
        line_items: [],
    })
    const productStore = useProductStore()
    const customerStore = useCustomerStore()
    const { products } = storeToRefs(productStore)
    const { customers } = storeToRefs(customerStore)
    const salesStore = useSalesStore()
    const { getActiveCart } = salesStore
    const { carts, activeCartId, syncPending, totalPending } =
        storeToRefs(salesStore)

    function getCurrency() {
        return getActiveCart().items[0]?.currency || 'USD'
    }

    const getVariantProducts = (data: Product[]) => {
        return data.flatMap((product) =>
            product.variations?.length ? product.variations : [product]
        )
    }
    const activeCustomer = ref<Customer>()
    const setActiveCustomer = (customer: Customer) => {
        activeCustomer.value = customer
        orderForm.value.customer_id = customer.id
    }

    const createCustomer = async (payload: Customer) => {
        const { data, success } = await customerStore.createCustomer(payload)
        setActiveCustomer(data)
        showCustomerForm.value = false
    }

    const submitOrder = async () => {
        orderForm.value.line_items = getActiveCart().items.map((item) => ({
            product_id: item.id,
            quantity: item.quantity,
        }))
        const { success } = await salesStore.createOrder(orderForm.value)
        if (success) {
            clearCartHandler(activeCartId.value)
        }
    }

    const handleAddPaymentMethod = () => {
        try {
            if (orderForm.value.split_payments) {
                orderForm.value.split_payments.push({
                    method: 'cash',
                    amount: '',
                })
            } else {
                const currentMethod = orderForm.value.payment_method || 'cash'
                orderForm.value.split_payments = [
                    { method: currentMethod, amount: '' },
                ]
                delete orderForm.value.payment_method
            }
        } catch (error) {
            toast.error('Failed to add payment method')
        }
    }

    const handleRemovePaymentMethod = (index: number) => {
        try {
            if (!orderForm.value.split_payments) return
            if (orderForm.value.split_payments.length < 2) {
                const preservedMethod =
                    orderForm.value.split_payments[0]?.method || 'cash'
                delete orderForm.value.split_payments
                orderForm.value.payment_method = preservedMethod
            } else {
                orderForm.value.split_payments.splice(index, 1)
            }
        } catch (error) {
            toast.error('Failed to remove payment method')
        }
    }

    const activeCart = computed(() => getActiveCart())
    const cartItemsCount = computed(() => {
        const total = activeCart.value.items.reduce(
            (count: number, item: Product) => count + item.quantity,
            0
        )
        if (total) {
            return total
        }

        return 'No'
    })

    const cartTotal = computed(() => {
        if (isEmpty(activeCart.value?.items)) {
            return
        }
        const total = activeCart.value.items.reduce(
            (total: number, item: Product) =>
                total + (item.sale_price || item.price) * item.quantity,
            0
        )
        return formatAmount(total, getCurrency())
    })

    const clearCartHandler = (cartId) => {
        orderForm.value = {
            customer_id: null,
            payment_method: 'cash',
            line_items: [],
        }
        activeCustomer.value = undefined
        salesStore.clearCart(cartId)
    }

    const handlePending = () => {
        salesStore.syncOrder()
    }

    onMounted(async () => {
        await productStore.getProducts()
        salesProducts.value = getVariantProducts(products.value) as Product[]
        await customerStore.getCustomers()
        await salesStore.checkPendingOrders()
    })
    return {
        createCustomer,
        customers,
        showCustomerForm,
        activeCustomer,
        setActiveCustomer,
        salesProducts,
        syncPending,
        totalPending,
        handlePending,
        carts,
        getActiveCart,
        orderForm,
        submitOrder,
        handleAddPaymentMethod,
        handleRemovePaymentMethod,
        cartTotal,
        cartItemsCount,
        clearCartHandler,
    }
}
