import { ref, toRefs } from 'vue'
import { useOutletStore } from '@/stores/outletStore'
import { useDashboardStore } from '@/stores/dashboardStore'
import { useRouter } from '@/composable/useVueRouter'
import { useRoute } from 'vue-router'
import { Tabs } from '@/types'
import { subMenuTabs } from './dashboardConfig'
export const useDashboard = () => {
    const defaultTab = ref('overview')
    const activeTab = ref<Tabs>('assistant')
    const outletStore = useOutletStore()
    const { getOutlets } = outletStore
    const { outlets } = toRefs(outletStore)
    const router = useRouter()
    const route = useRoute()
    const loading = ref(false)
    const dashboardStore = useDashboardStore()
    const {
        getDashboardSummary,
        getRecentActivities,
        getSalesAnalytics,
        getCustomerAnalytics,
    } = dashboardStore
    const ranges = [
        { label: 'Today', value: 'today' },
        { label: 'Yesterday', value: 'yesterday' },
        { label: 'This Week', value: 'this_week' },
        { label: 'Last Week', value: 'last_week' },
        { label: 'This Month', value: 'this_month' },
        { label: 'Last Month', value: 'last_month' },
        { label: 'This Year', value: 'this_year' },
        { label: 'Last Year', value: 'last_year' },
        { label: 'Custom', value: 'custom' },
    ]

    const handleChangeQuery = async (newQuery) => {
        let query = {}
        if (newQuery) {
            query = {
                ...route.query,
                ...newQuery,
            }
        }
        await router.replace({
            name: 'app.dashboard',
            query,
        })

        getDashboardData()
    }

    const getDashboardData = async () => {
        loading.value = true
        const query = route.query
        await getDashboardSummary(query)
        await getSalesAnalytics(query)
        await getCustomerAnalytics(query)
        await getRecentActivities(query)
        loading.value = false
    }

    return {
        defaultTab,
        outlets,
        getOutlets,
        ranges,
        activeTab,
        subMenuTabs,
        handleChangeQuery,
        loading,
        getDashboardData,
    }
}
