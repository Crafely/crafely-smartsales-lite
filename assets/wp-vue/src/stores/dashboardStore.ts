import { defineStore } from 'pinia'
import { ref } from 'vue'
import { useAxios } from '@/composable/useAxios'
import type {
    DashboardSummary,
    SalesAnalytics,
    CustomerAnalytics,
    ProductAnalytics,
    OutletAnalytics,
    RecentActivity,
} from '@/types/DashboardType'

export const useDashboardStore = defineStore('dashboard', () => {
    const summary = ref<DashboardSummary | null>(null)
    const salesAnalytics = ref<SalesAnalytics | null>(null)
    const customerAnalytics = ref<CustomerAnalytics | null>(null)
    const productAnalytics = ref<ProductAnalytics | null>(null)
    const outletAnalytics = ref<OutletAnalytics | null>(null)
    const recentActivities = ref<RecentActivity[] | null>(null)
    const error = ref(null)
    const loading = ref(false)
    const { get } = useAxios()

    const getDashboardSummary = async (query: Record<string, any> = {}) => {
        loading.value = true
        try {
            const { data, success, message, error } = await get(
                'dashboard/summary',
                { params: query }
            )
            summary.value = data
            return {
                data,
                success,
                message,
            }
        } catch (err) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const getSalesAnalytics = async (query: Record<string, any> = {}) => {
        loading.value = true
        try {
            const { data } = await get('dashboard/sales', { params: query })
            salesAnalytics.value = data
        } catch (err) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const getCustomerAnalytics = async (query: Record<string, any> = {}) => {
        loading.value = true
        try {
            const { data } = await get('dashboard/customers', { params: query })
            customerAnalytics.value = data
        } catch (err) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const getProductAnalytics = async (query: Record<string, any> = {}) => {
        loading.value = true
        try {
            const { data } = await get('dashboard/products', { params: query })
            productAnalytics.value = data
        } catch (err) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const getRecentActivities = async (query: Record<string, any> = {}) => {
        loading.value = true
        try {
            const { data } = await get('dashboard/recent-activities', {
                params: query,
            })
            recentActivities.value = data
        } catch (err) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    const getOutletAnalytics = async () => {
        loading.value = true
        try {
            const { data } = await get('dashboard/outlets')
            outletAnalytics.value = data
        } catch (err) {
            error.value = err
            throw err
        } finally {
            loading.value = false
        }
    }

    return {
        summary,
        salesAnalytics,
        customerAnalytics,
        productAnalytics,
        outletAnalytics,
        recentActivities,
        loading,
        error,
        getDashboardSummary,
        getSalesAnalytics,
        getCustomerAnalytics,
        getProductAnalytics,
        getOutletAnalytics,
        getRecentActivities,
    }
})
