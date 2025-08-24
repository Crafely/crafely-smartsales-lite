<script lang="ts" setup>
    import AppLayout from '@/layout/AppLayoutWithResize.vue'
    import { useOrder } from './useOrder'
    import { ConfirmDialog, useConfirmDialog } from '@/components/confirmDialog'
    import OrderDataTable from './fragment/OrderDataTable.vue'
    import OrderDetails from './fragment/OrderDetails.vue'
    import OrderForm from './fragment/OrderForm.vue'
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs'
    import StickyFooterLayout from '@/layout/StickyFooterLayout.vue'
    const {
        tabs,
        orders,
        activeTab,
        activeOrder,
        pagination,
        getOrders,
        switchTab,
        updateOrder,
        deleteOrder,
        getSingleOrder,
    } = useOrder()

    const { deleting, showConfirmDialog, handleDelete, confirmDelete } =
        useConfirmDialog()
</script>

<template>
    <AppLayout activeRouteName="app.order">
        <div class="p-8 overflow-y-auto">
            <OrderDataTable
                :orders="orders"
                :pagination="pagination"
                :getOrders="getOrders"
                :activeOrder="activeOrder"
                @rowClick="(_Order) => getSingleOrder(_Order.id)"
            />
        </div>
        <template #sidebar>
            <Tabs
                class="w-full"
                v-model="activeTab"
                @update:model-value="switchTab"
            >
                <StickyFooterLayout>
                    <template #header>
                        <TabsList class="w-full rounded-none">
                            <TabsTrigger
                                v-for="tab in tabs"
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
                    <TabsContent value="details" class="mt-0">
                        <OrderDetails
                            :activeOrder="activeOrder"
                            :handleDelete="(order) => handleDelete(order.id)"
                        />
                    </TabsContent>
                    <TabsContent value="edit" class="mt-0">
                        <OrderForm
                            :submitForm="updateOrder"
                            name="editForm"
                            ref="orderEditFormRef"
                        />
                    </TabsContent>
                </StickyFooterLayout>
            </Tabs>
        </template>

        <ConfirmDialog
            title="Are you sure you want to delete this Order?"
            description="This action will move the Order to the trash and can be restored within 30 days."
            v-model="showConfirmDialog"
            :deleting="deleting"
            @confirm="confirmDelete(deleteOrder)"
        />
    </AppLayout>
</template>
