import { ref, h, useTemplateRef, onMounted } from 'vue'
import { useUserStore } from '@/stores/userStore'
import { useOutletStore } from '@/stores/outletStore'
import { storeToRefs } from 'pinia'
import { useRoute } from '@/composable/useVueRouter'
import { ReloadIcon } from '@radix-icons/vue'
import { subMenuTabs } from './userConfig'
import type { User, Tabs } from '@/types'

export const useUser = () => {
    const userEditFormRef = useTemplateRef<HTMLInputElement>('userEditFormRef')
    const userCreateFormRef =
        useTemplateRef<HTMLInputElement>('userCreateFormRef')
    const activeTab = ref<Tabs>('details')
    const outletStore = useOutletStore()
    const { routerReplace, userId } = useRoute()
    const userStore = useUserStore()
    const { users, activeUser } = storeToRefs(userStore)
    const loading = ref(false)
    const error = ref(null)

    const showDeleteDialog = ref(false)

    const createUser = async (payload: any, actions: any) => {
        loading.value = true
        const response = await userStore.createUser(payload)
        if (!response.success) {
            const createForm = userCreateFormRef.value?.form
            createForm?.setErrors(response.error)
        } else {
            actions.resetForm()
            activeUser.value = response.data
        }
        loading.value = false
    }

    const updateUser = async (payload: any) => {
        payload.id = activeUser.value?.id
        const response = await userStore.updateUser(payload)
        if (!response.success) {
            const editForm = userEditFormRef.value?.form
            editForm?.setErrors(response.error)
        }
    }

    const mapActiveUserDataToEditForm = () => {
        if (activeUser.value) {
            console.log(activeUser.value)
            const role = Array.isArray(activeUser.value.roles)
                ? activeUser.value.roles[0]
                : ''
            userEditFormRef.value?.form?.setValues(activeUser.value)
            userEditFormRef.value?.form?.setFieldValue('role', role)
            const outletId = Number(activeUser.value.outlet?.id || '') || null
            console.log(outletId)
            userEditFormRef.value?.form?.setFieldValue('outlet_id', outletId)
            if (activeUser.value.outlet) {
                outletStore.setActiveOutlet(activeUser.value.outlet)
            }
        }
    }

    const switchTab = (tab: string) => {
        if (tab === 'edit') {
            setTimeout(mapActiveUserDataToEditForm, 50)
        }
    }

    const getSingleUser = async (userId: number) => {
        const tab = subMenuTabs.value.find(
            (tab) => tab.value === activeTab.value
        )
        const _tab = { ...tab }
        if (tab && tab.value !== 'create') {
            tab.icon = h(ReloadIcon, { class: 'w-4 h-4 animate-spin' })
        }
        activeUser.value = users.value.find((user: User) => user.id === userId)
        // userEditFormRef.value?.form?.setValues(activeUser.value)
        mapActiveUserDataToEditForm()
        if (tab && tab.value !== 'create') {
            tab.icon = _tab.icon as any
        }
        routerReplace({ userId: userId })
    }

    const deleteUser = async (userId: number) => {
        loading.value = true
        await userStore.deleteUser(userId)
        loading.value = false
        showDeleteDialog.value = false
        routerReplace({ userId: undefined })
    }

    onMounted(async () => {
        await userStore.getUsers()
        if (userId.value || users.value.length > 0) {
            const _userId = Number(userId.value || users.value[0].id)
            await getSingleUser(_userId)
        }
        outletStore.getOutlets()
    })

    return {
        subMenuTabs,
        users,
        activeUser,
        activeTab,
        loading,
        error,
        showDeleteDialog,
        switchTab,
        createUser,
        deleteUser,
        getSingleUser,
        updateUser,
    }
}
