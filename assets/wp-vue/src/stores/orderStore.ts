import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Order, Pagination, ProductQueryParams } from '@/types'
import { useAxios } from '@/composable/useAxios'
import { toast } from 'vue-sonner'
import { modifyQuery } from '@/utils'
export const useOrderStore = defineStore('order', () => {
    const orders = ref<Order[]>([])
    const activeOrder = ref<Order>()
    const error = ref(null)
    const loading = ref(false)
    const pagination = ref<Pagination>({
        pageIndex: 0,
        pageSize: 15,
        pageCount: 0,
    })
    const { get, post, put, remove } = useAxios()
    // const getOrders = async () => {
    //     const { data } = await get('orders')
    //     orders.value = data
    // }

    const getOrders = async (query: ProductQueryParams = {}) => {
        const { data, pagination: _pagination } = await get('orders', {
            params: modifyQuery(query),
        })

        pagination.value = {
            pageIndex: _pagination.current_page - 1,
            pageSize: _pagination.per_page,
            pageCount: _pagination.total_pages,
        }

        orders.value = data
    }

    const createOrder = async (payload: Order) => {
        loading.value = true
        try {
            const { data, message } = await post('orders', payload)
            orders.value = Array.from([...orders.value, data])
            toast.success(message)
            return data
        } catch (err) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const updateOrder = async (payload: Order) => {
        const { data, error, success, message } = await put(
            `orders/${payload.id}`,
            payload
        )
        if (success) {
            orders.value = orders.value.map((order) =>
                order.id === payload.id ? data : order
            )
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const getSingleOrder = async (orderId: number) => {
        const { data } = await get(`orders/${orderId}`)
        activeOrder.value = data
    }

    const deleteOrder = async (id: number) => {
        const { message, success } = await remove(`orders/${id}`)
        if (success) {
            orders.value = orders.value.filter((order) => order.id !== id)
        }
        toast.success(message)
    }

    return {
        orders,
        pagination,
        getOrders,

        activeOrder,
        getSingleOrder,

        createOrder,
        updateOrder,
        deleteOrder,
    }
})
