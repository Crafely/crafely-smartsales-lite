<script setup lang="ts">
    import AppLayout from '@/layout/AppLayout.vue'
    import { useSetting } from './useSetting'
    import SidebarButtons from './fragment/SidebarButtons.vue'
    const { settingItems } = useSetting()
</script>

<template>
    <AppLayout activeRouteName="app.settings">
        <div class="flex flex-col md:grid grid-cols-12">
            <sidebar class="side-bar col-span-2 border-r md:min-h-screen">
                <div class="p-4">
                    <SidebarButtons :settingItems="settingItems" />
                </div>
            </sidebar>

            <main class="main-section col-span-10">
                <div class="p-4 space-y-8 mb-8">
                    <router-view />
                </div>
            </main>
        </div>
    </AppLayout>
</template>
