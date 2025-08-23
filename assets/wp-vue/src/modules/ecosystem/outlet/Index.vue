<script lang="ts" setup>
    import AppLayout from '@/layout/AppLayoutWithResize.vue'
    import { useOutlet } from './useOutlet'
    import { ConfirmDialog, useConfirmDialog } from '@/components/confirmDialog'
    import OutletDataTable from './fragment/OutletDataTable.vue'
    import OutletForm from './fragment/OutletForm.vue'
    import OutletDetails from './fragment/OutletDetails.vue'
    import Counter from './counter/Index.vue'
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs'
    import StickyFooterLayout from '@/layout/StickyFooterLayout.vue'
    const {
        subMenuTabs,
        outlets,
        activeTab,
        activeOutlet,
        switchTab,
        createOutlet,
        updateOutlet,
        deleteOutlet,
        getSingleOutlet,
    } = useOutlet()

    const { deleting, showConfirmDialog, handleDelete, confirmDelete } =
        useConfirmDialog()
</script>

<template>
    <AppLayout activeRouteName="app.ecosystem">
        <div class="p-8 overflow-y-auto">
            <OutletDataTable
                :outlets="outlets"
                :activeOutlet="activeOutlet"
                @rowClick="(_outlet) => getSingleOutlet(_outlet.id)"
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

                    <TabsContent value="details" class="mt-0">
                        <OutletDetails
                            :activeOutlet="activeOutlet"
                            :handleDelete="(outlet) => handleDelete(outlet.id)"
                        />
                    </TabsContent>
                    <TabsContent value="create" class="mt-0">
                        <OutletForm
                            :submitForm="createOutlet"
                            name="createForm"
                            ref="outletCreateFormRef"
                        />
                    </TabsContent>
                    <TabsContent value="edit" class="mt-0">
                        <OutletForm
                            :submitForm="updateOutlet"
                            name="editForm"
                            ref="outletEditFormRef"
                        />
                    </TabsContent>
                    <TabsContent value="counter" class="mt-0">
                        <Counter />
                    </TabsContent>
                </StickyFooterLayout>
            </Tabs>
        </template>
        <ConfirmDialog
            title="Are you sure you want to delete this outlet?"
            description="This action will move the outlet to the trash and can be restored within 30 days."
            v-model="showConfirmDialog"
            :deleting="deleting"
            @confirm="confirmDelete(deleteOutlet)"
        />
    </AppLayout>
</template>
