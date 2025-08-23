import { ref, onMounted, useTemplateRef } from 'vue'
import { storeToRefs } from 'pinia'
import { useUserStore } from '@/stores/userStore'
import { useAppStore } from '@/stores/appStore'

export const useGeneral = () => {
    const userStore = useUserStore()
    const appStore = useAppStore()
    const loading = ref(false)
    const authUserEditFormRef = useTemplateRef<HTMLInputElement>(
        'authUserEditFormRef'
    )
    const appSettingsEditFormRef = useTemplateRef<HTMLInputElement>(
        'appSettingsEditFormRef'
    )

    const { authUser } = storeToRefs(userStore)
    const { appSettings } = storeToRefs(appStore)

    const getCurrentUser = async () => {
        loading.value = true
        try {
            await userStore.getCurrentUser()
            authUserEditFormRef.value?.form &&
                authUserEditFormRef.value?.form.setValues(authUser.value)
        } finally {
            loading.value = false
        }
    }

    const getAppSettings = async () => {
        loading.value = true
        try {
            await appStore.getAppSettings()
            appSettingsEditFormRef.value?.form &&
                appSettingsEditFormRef.value?.form.setValues(appSettings.value)
        } finally {
            loading.value = false
        }
    }

    const updateAuth = async (payload: any) => {
        loading.value = true
        try {
            payload.id = authUser.value?.id
            const response = await userStore.updateUser(payload)
            if (!response.success) {
                const editForm = authUserEditFormRef.value?.form
                editForm && editForm.setErrors(response.error)
            }
        } finally {
            loading.value = false
        }
    }

    const updateAppSettings = async (payload: any) => {
        loading.value = true
        try {
            const response = await appStore.updateAppSettings(payload)
            if (!response.success) {
                const editForm = appSettingsEditFormRef.value?.form
                editForm && editForm.setErrors(response.error)
            }
        } finally {
            loading.value = false
        }
    }

    onMounted(async () => {
        await getCurrentUser()
        await getAppSettings()
    })

    return { authUser, updateAuth, appSettings, updateAppSettings, loading }
}
