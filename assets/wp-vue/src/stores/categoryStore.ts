import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import type { Category } from '@/types'
import { useAxios } from '@/composable/useAxios'
import { toast } from 'vue-sonner'
export const useCategoryStore = defineStore('category', () => {
    const categories = ref<Category[]>([])
    const activeCategory = ref<Category>()
    const loading = ref(false)
    const { get, post, remove } = useAxios()
    const getCategories = async () => {
        const { data } = await get('categories')
        categories.value = data
    }

    const categoryOptions = computed(() => {
        return categories.value.map((category) => ({
            value: category.id,
            label: category.name,
        }))
    })

    const createCategory = async (payload: Category) => {
        loading.value = true
        try {
            const { data, error, success, message } = await post(
                'categories',
                payload
            )
            if (success) {
                categories.value = Array.from([...categories.value, data])
            }
            toast[success ? 'success' : 'error'](message)
            return { data, error, success, message }
        } catch (err) {
            throw err
        } finally {
            loading.value = false
        }
    }

    const deleteCategory = async (id: number) => {
        const { message, success } = await remove(`categories/${id}`)
        if (success) {
            categories.value = categories.value.filter(
                (category) => category.id !== id
            )
        }
        toast.success(message)
    }

    const setActiveCategory = (category: Category) => {
        activeCategory.value = category
    }

    return {
        categories,
        categoryOptions,
        getCategories,

        activeCategory,
        setActiveCategory,

        createCategory,
        deleteCategory,
    }
})
