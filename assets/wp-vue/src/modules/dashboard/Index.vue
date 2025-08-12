<script setup lang="ts">
    import { onMounted, computed } from 'vue'
    import { storeToRefs } from 'pinia'
    import { useDashboardStore } from '@/stores/dashboardStore'
    import { useAppStore } from '@/stores/appStore'
    import { useDashboard } from './useDashboard'
    import { tabs } from './dashboardConfig'
    import Loader from '@/components/Loader.vue'
    import { ReloadIcon } from '@radix-icons/vue'

    import AppLayout from '@/layout/AppLayoutWithResize.vue'
    import TabbedContent from '@/components/TabbedContent.vue'
    import QueryFilter from '@/components/QueryFilter.vue'
    import ProgressCard from '@/components/ProgressCard.vue'
    import CustomerChart from '@/molecules/dashboard/CustomerChart.vue'
    import SalesChart from '@/molecules/dashboard/SalesChart.vue'
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs'
    import StickyFooterLayout from '@/layout/StickyFooterLayout.vue'

    import ChatModule from '@/modules/chat/Index.vue'
    // import CustomerChart from '@/molecules/dashboard/CustomerChart.vue'
    import RecentActivityDatatable from '@/molecules/dashboard/RecentActivityDatatable.vue'

    const {
        defaultTab,
        ranges,
        outlets,
        activeTab,
        subMenuTabs,
        handleChangeQuery,
        getOutlets,
        getDashboardData,
        loading,
    } = useDashboard()
    const dashboardStore = useDashboardStore()
    const { summary, salesAnalytics, recentActivities, customerAnalytics } =
        storeToRefs(dashboardStore)

    const appStore = useAppStore()
    const { isAiConfig } = storeToRefs(appStore)

    onMounted(() => {
        getOutlets()
        getDashboardData()
        isAiConfig
    })
</script>

<template>
    <AppLayout activeRouteName="app.dashboard" :hideSidebar="!isAiConfig">
        <Loader :loading="loading">
            <template #loader>
                <ReloadIcon class="size-8 animate-spin"></ReloadIcon>
            </template>
        </Loader>
        <div class="p-8">
            <TabbedContent :tabs="tabs" v-model="defaultTab">
                <template #default="{ currentTab }">
                    <template v-if="currentTab == 'overview'">
                        <div class="space-y-5">
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <ProgressCard
                                    v-if="summary?.sales"
                                    title="Sales"
                                    :value="summary.sales.total"
                                    :note="`${summary.sales.total_orders} Orders`"
                                />

                                <ProgressCard
                                    v-if="summary?.customers"
                                    title="Customers"
                                    :value="summary.customers.total"
                                    note="Active Customers"
                                />
                                <ProgressCard
                                    v-if="summary?.inventory"
                                    title="Inventory"
                                    :value="summary.inventory.total_value"
                                    :note="`${summary.inventory.total_products} Products`"
                                />
                                <ProgressCard
                                    v-if="summary?.inventory.low_stock"
                                    title="Low Stock"
                                    :value="summary.inventory.low_stock"
                                    :note="`${summary.inventory.out_of_stock} Out of stock`"
                                />
                            </div>
                            <div
                                class="block space-y-4 md:space-y-0 md:flex gap-4"
                            >
                                <div class="w-full md:w-1/2">
                                    <SalesChart
                                        v-if="salesAnalytics"
                                        :key="
                                            'sales-' +
                                            JSON.stringify(salesAnalytics)
                                        "
                                        :salesData="salesAnalytics"
                                    />
                                </div>
                                <div class="w-full md:w-1/2">
                                    <CustomerChart
                                        v-if="customerAnalytics"
                                        key="customer-"
                                        :customerData="customerAnalytics"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-if="currentTab == 'analytics'">
                        <div class="space-y-5">
                            <div
                                class="block space-y-4 md:space-y-0 md:flex gap-4"
                            >
                                <div class="w-full md:w-1/2">
                                    <SalesChart
                                        v-if="salesAnalytics"
                                        :salesData="salesAnalytics"
                                    />
                                </div>
                                <div class="w-full md:w-1/2">
                                    <CustomerChart
                                        v-if="customerAnalytics"
                                        :customerData="customerAnalytics"
                                    />
                                </div>
                            </div>
                        </div>
                    </template>
                    <template v-if="currentTab == 'recentActivities'">
                        <div class="space-y-5">
                            <RecentActivityDatatable
                                :activities="recentActivities"
                                :pagination="{
                                    page: 1,
                                    per_page: 10,
                                    total: recentActivities?.length || 0,
                                }"
                                :getActivities="getDashboardData"
                            />
                        </div>
                    </template>
                    <!-- <component :is="dashboardComponents[currentTab]" /> -->
                </template>
                <template #afterTab>
                    <QueryFilter
                        @changeQuery="handleChangeQuery"
                        :outlets="outlets"
                        :ranges="ranges"
                    />
                </template>
            </TabbedContent>
        </div>
        <template #sidebar v-if="isAiConfig">
            <Tabs class="w-full" v-model="activeTab">
                <StickyFooterLayout>
                    <template #header>
                        <TabsList class="w-full rounded-none">
                            <TabsTrigger
                                v-for="tab in subMenuTabs"
                                :key="tab.value"
                                class="data-[state=active]:shadow-none data-[state=active]:bg-transparent"
                                :value="tab.value"
                            >
                                <div
                                    class="flex items-center gap-2 transition-opacity duration-200 hover:opacity-80"
                                >
                                    <component :is="tab.icon" class="w-4 h-4" />
                                    {{ tab.label }}
                                </div>
                            </TabsTrigger>
                        </TabsList>
                    </template>

                    <TabsContent value="assistant" class="mt-0">
                        <ChatModule />
                    </TabsContent>
                </StickyFooterLayout>
            </Tabs>
        </template>
    </AppLayout>
</template>
