import { defineStore } from 'pinia'
import { ref } from 'vue'
import type { Customer } from '@/types'
import { useAxios } from '@/composable/useAxios'
import { toast } from 'vue-sonner'
export const useCustomerStore = defineStore('customer', () => {
    const customers = ref<Customer[]>([])
    const activeCustomer = ref<Customer>()
    const error = ref(null)
    const loading = ref(false)
    const { get, post, put, remove } = useAxios()
    const getCustomers = async () => {
        const { data } = await get('customers')
        customers.value = data
    }

    const createCustomer = async (payload: Customer) => {
        const { data, error, message, success } = await post(
            'customers',
            payload
        )
        customers.value = success
            ? Array.from([data, ...customers.value])
            : customers.value
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const updateCustomer = async (payload: Customer) => {
        const { data, error, success, message } = await put(
            `customers/${payload.id}`,
            payload
        )
        if (success) {
            customers.value = customers.value.map((customer) =>
                customer.id === payload.id ? data : customer
            )
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const getSingleCustomer = async (customerId: number) => {
        const { data } = await get(`customers/${customerId}`)
        activeCustomer.value = data
    }

    const deleteCustomer = async (id: number) => {
        const { message, success } = await remove(`customers/${id}`)
        if (success) {
            customers.value = customers.value.filter(
                (customer) => Number(customer.id) !== Number(id)
            )
            if (customers.value.length > 0) {
                await getSingleCustomer(customers.value[0].id)
            }
        }
        toast.success(message)
    }

    const setActiveCustomer = (customer: Customer) => {
        activeCustomer.value = customer
    }

    return {
        customers,
        getCustomers,

        activeCustomer,
        getSingleCustomer,
        setActiveCustomer,

        createCustomer,
        updateCustomer,
        deleteCustomer,
    }
})
