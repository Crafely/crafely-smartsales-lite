import { ref, h, useTemplateRef, onMounted, VNode } from 'vue'
import { useProductStore } from '@/stores/productStore'
import { useCategoryStore } from '@/stores/categoryStore'
import { useCrafelyAi } from '@/composable/useCrafelyAi'
import { storeToRefs } from 'pinia'
import type { Product, Tabs } from '@/types'
import { useRoute } from '@/composable/useVueRouter'
import { ReloadIcon } from '@radix-icons/vue'
import { subMenuTabs } from './productConfig'
import { toast } from 'vue-sonner'
export const useProduct = () => {
    const productStore = useProductStore()
    const categoryStore = useCategoryStore()
    const { categories } = storeToRefs(categoryStore)

    const { sendMessageWithStructure } = useCrafelyAi()
    const productEditFormRef =
        useTemplateRef<HTMLInputElement>('productEditFormRef')
    const productCreateFormRef = useTemplateRef<HTMLInputElement>(
        'productCreateFormRef'
    )
    const generatePromptRef =
        useTemplateRef<HTMLInputElement>('generatePromptRef')
    const loading = ref(false)
    const activeTab = ref<Tabs>('create')

    const suggestPrompts = ref([
        {
            id: 1,
            title: 'iPhone 11',
            content:
                'Generate an e-commerce product about iPhone 11 Pro Max with price $100, SKU iphone-11',
        },
        {
            id: 2,
            title: 'Leather Wallet',
            content:
                'Generate an e-commerce product about a leather wallet with price $50, SKU leather-wallet',
        },
        {
            id: 3,
            title: 'Smartwatch',
            content:
                'Generate an e-commerce product about a smartwatch with price $200, SKU smartwatch-2023',
        },
        {
            id: 4,
            title: 'Denim Jacket',
            content:
                'Generate an e-commerce product about a denim jacket with price $80, SKU denim-jacket',
        },
    ])

    const { routerReplace, productId } = useRoute()
    const { products, pagination, activeProduct } = storeToRefs(productStore)

    const createProduct = async (payload: any, actions: any) => {
        const response = await productStore.createProduct(payload)
        if (!response.success) {
            const createForm = productCreateFormRef.value?.form
            createForm && createForm.setErrors(response.error)
        } else {
            actions.resetForm()
            actions.setFieldValue('categories', [])
            actions.setFieldValue('tags', [])
        }
    }

    const tags = [
        { id: 2, title: 'recent' },
        { id: 3, title: 'popular' },
        { id: 4, title: 'featured' },
        { id: 5, title: 'sale' },
        { id: 6, title: 'new' },
        { id: 7, title: 'exclusive' },
    ]

    const contexGenerator = (payload: any) => {
        return payload
            .map((items) => {
                const formattedItems = items.data
                    .map(
                        (item) =>
                            `{id: ${item.id}, title: '${
                                item?.title || item?.name
                            }'}`
                    )
                    .join(', ')
                return `${items.title}: [${formattedItems}]`
            })
            .join(' ')
    }

    const generateProduct = async (payload: any) => {
        payload.context = contexGenerator([
            { data: categories.value, title: 'Categories' },
            { data: tags, title: 'Tags' },
        ])

        const { data, error, success, message } =
            await sendMessageWithStructure('product/generate', payload)
        if (success) {
            productCreateFormRef.value?.form &&
                productCreateFormRef.value?.form.setValues(data)
        }

        toast[success ? 'success' : 'error'](message)
        return { data, error, success, message }
    }

    const updateProduct = async (payload: any) => {
        payload.id = activeProduct.value.id
        const response = await productStore.updateProduct(payload)
        if (!response.success) {
            const editForm = productEditFormRef.value?.form
            editForm && editForm.setErrors(response.error)
        }
    }

    const getSingleProduct = async (productId: number) => {
        let tab = subMenuTabs.value.find((tab) => tab.value === activeTab.value)
        const _tab = { ...tab }
        if (tab && tab.value !== 'create') {
            tab.icon = h(ReloadIcon, { class: 'w-4 h-4 animate-spin' })
        }
        await productStore.getSingleProduct(productId)
        productEditFormRef.value?.form &&
            productEditFormRef.value?.form.setValues(activeProduct.value)

        if (tab && tab.value !== 'create') {
            tab.icon = _tab.icon as VNode
        }
        routerReplace({ productId: productId })
    }

    const switchTab = (tab: string) => {
        if (tab === 'edit') {
            setTimeout(() => {
                productEditFormRef.value?.form &&
                    productEditFormRef.value?.form.setValues(
                        activeProduct.value
                    )
            }, 50)
        }
    }

    const deleteProduct = async (product: Product) => {
        await productStore.deleteProduct(product.id)
        routerReplace({ productId: undefined })
    }

    const deleteBulkProduct = async (ids) => {
        return await productStore.deleteBulkProduct(ids)
    }

    const getProducts = async (query = {}) => {
        loading.value = true
        await productStore.getProducts(query)
        loading.value = false
    }

    onMounted(async () => {
        await getProducts()
        if (productId.value || products.value.length > 0) {
            activeTab.value = 'details'
            await getSingleProduct(productId.value || products.value[0].id)
        }
        await categoryStore.getCategories()
    })
    return {
        subMenuTabs,
        products,
        activeTab,
        loading,
        pagination,
        activeProduct,
        getSingleProduct,
        deleteProduct,
        createProduct,
        generateProduct,
        suggestPrompts,
        updateProduct,
        switchTab,
        getProducts,
        deleteBulkProduct,
    }
}
