import type { Product, Pagination, ProductQueryParams } from '@/types'
import { defineStore } from 'pinia'
import { ref } from 'vue'
import { debounce } from 'lodash'
import { useAxios } from '@/composable/useAxios'
import { toast } from 'vue-sonner'
import { modifyQuery } from '@/utils'
export const useProductStore = defineStore('product', () => {
    const products = ref<Product[]>([])
    const searchQuery = ref('')
    const searchFilters = ref<string[]>([])

    const pagination = ref<Pagination>({
        pageIndex: 0,
        pageSize: 15,
        pageCount: 0,
    })

    const activeProduct = ref<Product>()
    const { get, post, put, remove } = useAxios()

    // Mutations
    const setActiveProduct = (product: Product | undefined) => {
        activeProduct.value = product
    }

    // Actions
    const createProduct = async (payload: Product) => {
        const { data, error, success, message } = await post(
            'products',
            payload
        )
        if (success) {
            products.value = Array.from([...products.value, data])
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const updateProduct = async (payload: Product) => {
        const { data, error, success, message } = await put(
            `products/${payload.id}`,
            payload
        )
        if (success) {
            products.value = products.value.map((product) =>
                product.id === payload.id ? data : product
            )
        }
        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const getProducts = async (query: ProductQueryParams = {}) => {
        const { data, pagination: _pagination } = await get('products', {
            params: modifyQuery(query),
        })

        pagination.value = {
            pageIndex: _pagination.current_page - 1,
            pageSize: _pagination.per_page,
            pageCount: _pagination.total_pages,
        }

        products.value = data
    }

    const getProductsByQuery = debounce(async () => {
        return await getProducts({ q: searchQuery.value.trim() })
    }, 1000)

    const getSingleProduct = async (id: number) => {
        const response = await get(`products/${id}`).then(({ data }) => {
            return {
                ...data,
                price: Number(data.price),
            }
        })
        setActiveProduct(response)
    }

    const deleteProduct = async (productId: number) => {
        const { message, success } = await remove(`products/${productId}`)
        if (success) {
            products.value = products.value.filter(
                (product) => product.id !== productId
            )
            setActiveProduct(undefined)
        }
        toast.success(message)
    }

    const deleteBulkProduct = async (productIds: number[]) => {
        const { message, success } = await remove(`products/bulk-delete`, {
            ids: productIds,
        })
        if (success) {
            products.value = products.value.filter(
                (product) => !productIds.includes(product.id)
            )
            setActiveProduct(undefined)
        }
        toast.success(message)
    }

    return {
        products,
        pagination,
        getProducts,

        createProduct,

        activeProduct,
        setActiveProduct,
        getSingleProduct,
        updateProduct,
        deleteProduct,
        deleteBulkProduct,
        searchQuery,
        searchFilters,
        getProductsByQuery,
    }
})
