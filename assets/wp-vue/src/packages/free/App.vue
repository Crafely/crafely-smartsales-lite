<template>
    <RouterView />
    <Toaster position="top-right" :expand="true" richColors />
</template>

<script lang="ts" setup>
    import { useAppStore } from '@/stores/appStore'
    import { Toaster } from '@/components/ui/sonner'
    import { onMounted } from 'vue'

    const appStore = useAppStore()
    onMounted(() => {
        appStore.getAppSettings()
    })
</script>
