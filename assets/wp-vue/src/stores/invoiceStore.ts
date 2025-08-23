import { defineStore } from 'pinia'
import { ref } from 'vue'
import type {
    Invoice,
    Pagination,
    ProductQueryParams,
    InvoiceForm,
} from '@/types'
import { debounce } from 'lodash'
import { useAxios } from '@/composable/useAxios'
import { toast } from 'vue-sonner'
import { modifyQuery } from '@/utils'

export const useInvoiceStore = defineStore('invoice', () => {
    const invoices = ref<Invoice[]>([])
    const invoiceForm = ref<InvoiceForm>({
        customer_id: null,
        outlet_id: null,
        line_items: [],
        vat: 6,
        issue_date: new Date().toISOString().split('T')[0],
        due_date: '',
    })
    const searchQuery = ref('')
    const searchFilters = ref<string[]>([])
    const pagination = ref<Pagination>({
        pageIndex: 0,
        pageSize: 15,
        pageCount: 0,
    })

    const error = ref(null)
    const loading = ref(false)
    const activeInvoice = ref<Invoice>()
    const { get, post, put, remove } = useAxios()

    const setActiveInvoice = (invoice: Invoice) => {
        activeInvoice.value = invoice
        invoiceForm.value = {
            customer_id: invoice.customer_id,
            outlet_id: invoice.outlet_id,
            line_items: invoice.line_items,
            vat: invoice.vat,
            issue_date: invoice.issue_date,
            due_date: invoice.due_date,
        }
    }

    const removeActiveInvoice = () => {
        activeInvoice.value = undefined

        invoiceForm.value = {
            customer_id: null,
            outlet_id: null,
            line_items: [],
            vat: 6,
            issue_date: new Date().toISOString().split('T')[0],
            due_date: '',
        }
    }

    const createInvoice = async (payload: Invoice) => {
        loading.value = true
        const { data, error, success, message } = await post(
            'invoices',
            payload
        )
        if (success) {
            invoices.value = Array.from([...invoices.value, data])
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
        loading.value = false
    }

    const updateInvoice = async (payload: Invoice) => {
        loading.value = true
        const { data, error, success, message } = await put(
            `invoices/${payload.id}`,
            payload
        )
        if (success) {
            invoices.value = invoices.value.map((invoice) =>
                invoice.id === payload.id ? data : invoice
            )
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
        loading.value = false
    }

    const getInvoice = async (query: ProductQueryParams = {}) => {
        const { data, pagination: _pagination } = await get('invoices', {
            params: modifyQuery(query),
        })

        pagination.value = {
            pageIndex: _pagination.current_page - 1,
            pageSize: _pagination.per_page,
            pageCount: _pagination.total_pages,
        }

        invoices.value = data
    }

    const getInvoicesByQuery = debounce(async () => {
        return await getInvoice({ q: searchQuery.value.trim() })
    }, 1000)

    const getSingleInvoice = async (id: number) => {
        const response = await get(`invoices/${id}`).then(({ data }) => {
            return {
                ...data,
            }
        })
        setActiveInvoice(response)
    }

    const deleteInvoice = async (id: number) => {
        loading.value = true
        const { message, success } = await remove(`invoices/${id}`)
        if (success) {
            invoices.value = invoices.value.filter(
                (invoice) => invoice.id !== id
            )

            removeActiveInvoice()
        }
        toast.success(message)
        loading.value = false
    }

    return {
        loading,
        invoices,
        pagination,
        getInvoice,
        invoiceForm,

        createInvoice,

        activeInvoice,
        setActiveInvoice,
        getSingleInvoice,
        updateInvoice,
        deleteInvoice,
        searchQuery,
        searchFilters,
        getInvoicesByQuery,
    }
})
