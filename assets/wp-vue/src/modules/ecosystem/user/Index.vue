<script lang="ts" setup>
    import { useUser } from './useUser'
    import { ConfirmDialog, useConfirmDialog } from '@/components/confirmDialog'
    import UserDataTable from './fragment/UserDataTable.vue'
    import UserDetails from './fragment/UserDetails.vue'
    import UserForm from './fragment/UserForm.vue'
    import AppLayout from '@/layout/AppLayoutWithResize.vue'
    import { subMenuTabs } from './userConfig'

    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs'
    import StickyFooterLayout from '@/layout/StickyFooterLayout.vue'
    import {
        ResizableHandle,
        ResizablePanel,
        ResizablePanelGroup,
    } from '@/components/ui/resizable'
    import { ref } from 'vue'

    const {
        users,
        activeUser,
        updateUser,
        createUser,
        deleteUser,
        switchTab,
        getSingleUser,
    } = useUser()

    const { deleting, showConfirmDialog, handleDelete, confirmDelete } =
        useConfirmDialog()
    const activeTab = ref('details')

    // const switchTab = (value: string) => {
    //     activeTab.value = value
    // }
</script>

<template>
    <AppLayout activeRouteName="app.ecosystem">
        <div class="p-8 overflow-y-auto">
            <UserDataTable
                :users="users"
                :activeUser="activeUser"
                :deleteUser="handleDelete"
                @rowClick="(_user) => getSingleUser(_user.id)"
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
                        <UserDetails
                            :user="activeUser"
                            :handleDelete="(user) => handleDelete(user.id)"
                        />
                    </TabsContent>
                    <TabsContent value="create" class="mt-0">
                        <UserForm
                            :submitForm="createUser"
                            name="createForm"
                            ref="userCreateFormRef"
                        />
                    </TabsContent>
                    <TabsContent value="edit" class="mt-0">
                        <UserForm
                            :submitForm="updateUser"
                            name="editForm"
                            ref="userEditFormRef"
                        />
                    </TabsContent>
                </StickyFooterLayout>
            </Tabs>
        </template>
        <ConfirmDialog
            title="Are you sure you want to delete this user?"
            description="This action will move the user to the trash and can be restored within 30 days."
            v-model="showConfirmDialog"
            :deleting="deleting"
            @confirm="confirmDelete(deleteUser)"
        />
    </AppLayout>
</template>
