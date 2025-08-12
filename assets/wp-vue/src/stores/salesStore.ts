import { ref } from 'vue'
import { defineStore } from 'pinia'
import { find, findIndex } from 'lodash'
import { useAxios } from '@/composable/useAxios'
import { useUserStore } from '@/stores/userStore'

import { toast } from 'vue-sonner'
import { useOnline } from '@vueuse/core'
import { Product, OrderForm } from '@/types'
import { number } from 'zod'

export const useSalesStore = defineStore('sales', () => {
    const carts = ref<
        {
            id: number
            items: []
        }[]
    >([])
    const paymentMethods = [
        { id: 'cash', name: 'Cash' },
        { id: 'card', name: 'Card' },
        { id: 'bank_transfer', name: 'Bank Transfer' },
        { id: 'paypal', name: 'PayPal' },
        { id: 'upi', name: 'UPI' },
        { id: 'cryptocurrency', name: 'Cryptocurrency' },
        { id: 'cod', name: 'Cash on Delivery' },
    ]
    const userStore = useUserStore()
    const activeCartId = ref<number | null>(null)
    const online = useOnline()
    const syncPending = ref(false)
    const totalPending = ref(0)
    const { post } = useAxios()
    const loading = ref(false)

    const getActiveCart = () => {
        return find(carts.value, { id: activeCartId.value })
    }

    const setActiveCartId = (id: number) => {
        activeCartId.value = id
    }

    const addNewCart = () => {
        const newCartId = Date.now()
        carts.value.push({
            id: newCartId,
            items: [],
        })
        setActiveCartId(newCartId)
        return newCartId
    }

    const deleteCart = (cartId: number) => {
        const cartIndex = carts.value.findIndex(
            (cart: any) => cart.id === cartId
        )
        if (carts.value.length > 1) {
            carts.value.splice(cartIndex, 1)
            if (cartIndex !== 0) {
                setActiveCartId(carts.value[cartIndex - 1].id)
            } else {
                setActiveCartId(carts.value[cartIndex].id)
            }
        }
        return false
    }

    const clearCart = (cartId: number) => {
        console.log(cartId)
        const cartIndex = carts.value.findIndex(
            (cart: any) => cart.id === cartId
        )
        if (cartIndex !== -1) {
            carts.value[cartIndex].items = []
        }
    }

    function addToCart(product: Product) {
        const activeCart = getActiveCart()
        const existingItem =
            activeCart && Array.isArray(activeCart.items)
                ? find(activeCart.items, (item: any) => item.id === product.id)
                : undefined

        if (existingItem) {
            existingItem.quantity = (existingItem.quantity || 0) + 1
        } else {
            product.quantity = 1
            activeCart.items.push({ ...product })
        }
    }

    function decreaseQuantity(product: Product) {
        console.log('decrease')
        const activeCart = getActiveCart()
        const existingItem =
            activeCart && Array.isArray(activeCart.items)
                ? find(activeCart.items, (item: any) => item.id === product.id)
                : undefined

        if (existingItem) {
            existingItem.quantity = existingItem.quantity - 1
            if (existingItem.quantity === 0) {
                removeFromCart(product.id)
            }
        }
    }

    function removeFromCart(productId: number) {
        const activeCart = getActiveCart()
        if (activeCart) {
            const index = findIndex(
                activeCart.items,
                (item: any) => item.id === productId
            )
            if (index > -1) {
                activeCart.items.splice(index, 1)
            }
        }
    }

    const checkPendingOrders = () => {
        const pendingOrders = JSON.parse(
            localStorage.getItem('pendingOrders') || '[]'
        ).length

        if (pendingOrders > 0) {
            syncPending.value = true
            totalPending.value = pendingOrders
        } else {
            syncPending.value = false
            totalPending.value = 0
        }
    }

    const syncOrder = async () => {
        if (online.value) {
            const pendingOrders = JSON.parse(
                localStorage.getItem('pendingOrders') || '[]'
            )
            let successPendingOrders: number[] = []

            for (let i = 0; i < pendingOrders.length; i++) {
                const order = pendingOrders[i]
                try {
                    const response = await post('orders', order, {
                        auth: userStore.userCredentials,
                    })
                    const { success, message } = response
                    toast[success ? 'success' : 'error'](message)

                    if (success) {
                        successPendingOrders.push(i)
                    }
                } catch (error: any) {
                    const errorMessage =
                        error?.response?.data?.message ||
                        (error instanceof Error
                            ? error.message
                            : 'Failed to sync order')
                    toast.error(errorMessage)
                }
            }

            successPendingOrders
                .sort((a, b) => b - a)
                .forEach((i) => pendingOrders.splice(i, 1))

            localStorage.setItem('pendingOrders', JSON.stringify(pendingOrders))
            checkPendingOrders()
        } else {
            toast.warning('Connect to the internet to sync.')
        }
    }
    //
    const createOrder = async (payload: OrderForm) => {
        try {
            if (online.value === false) {
                try {
                    const pendingOrders = JSON.parse(
                        localStorage.getItem('pendingOrders') || '[]'
                    )
                    pendingOrders.push(payload)
                    localStorage.setItem(
                        'pendingOrders',
                        JSON.stringify(pendingOrders)
                    )
                    const success = true
                    const message = 'Product are saved.'
                    toast[success ? 'success' : 'error'](message)

                    checkPendingOrders()
                    return { success, message, offline: true } // ✅ return a response
                } catch (error) {
                    return {
                        success: false,
                        message: 'Failed to save offline order',
                        error,
                    }
                }
            } else {
                const response = await post('orders', payload, {
                    auth: userStore.userCredentials,
                })
                const { success, message } = response
                toast[success ? 'success' : 'error'](message)

                return { success, message, offline: false, data: response } // ✅ return a response
            }
        } catch (error: any) {
            const errorResponse = error?.response?.data
            const errorMessage =
                errorResponse?.message ||
                (error instanceof Error
                    ? error.message
                    : 'Failed to submit order')
            const errorDetails =
                errorResponse?.details ||
                'Please try again or contact support if the issue persists'

            toast.error(errorMessage, { description: errorDetails })

            return {
                success: false,
                message: errorMessage,
                details: errorDetails,
            } // ✅ return error
        }
    }

    return {
        carts,
        loading,
        addToCart,
        removeFromCart,
        decreaseQuantity,
        clearCart,

        paymentMethods,
        syncOrder,
        activeCartId,
        addNewCart,
        deleteCart,
        getActiveCart,
        createOrder,
        syncPending,
        totalPending,
        checkPendingOrders,
    }
})
