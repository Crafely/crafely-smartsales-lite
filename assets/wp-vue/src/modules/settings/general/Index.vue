<script setup lang="ts">
    import ProfileForm from '../fragment/ProfileForm.vue'
    import NotificationForm from '../fragment/NotificationForm.vue'
    import StoreForm from '../fragment/StoreForm.vue'
    import { useGeneral } from './useGeneral'
    import Loader from '@/components/Loader.vue'

    const { authUser, updateAuth, appSettings, updateAppSettings, loading } =
        useGeneral()
</script>
<template>
    <Loader :loading="loading"></Loader>
    <ProfileForm
        :form="authUser"
        :submitForm="updateAuth"
        ref="authUserEditFormRef"
    >
        <template #formHeader>
            <div class="mb-4 border-b">
                <h1 class="text-xl">Profile Settings</h1>
                <p class="mb-6 text-gray-600">
                    Update your profile information here.
                </p>
            </div>
        </template>
    </ProfileForm>
    <StoreForm
        :form="appSettings"
        :submitForm="updateAppSettings"
        ref="appSettingsEditFormRef"
    >
        <template #formHeader>
            <div class="mb-4 border-b">
                <h1 class="text-xl">Store Settings</h1>
                <p class="mb-6 text-gray-600">
                    Configure your store info here.
                </p>
            </div>
        </template>
    </StoreForm>
    <!-- <NotificationForm>
        <template #formHeader>
            <div class="mb-4 border-b">
                <h1 class="text-xl">Notification Settings</h1>
                <p class="mb-6 text-gray-600">
                    Configure your notification preferences here.
                </p>
            </div>
        </template>
        <template #formFooter>
            <Button type="submit">Update Notifications</Button>
        </template>
    </NotificationForm> -->
</template>
