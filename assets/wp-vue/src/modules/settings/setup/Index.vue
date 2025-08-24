<script setup lang="ts">
    import { useSetup } from './useSetup'
    import BusinessSetupForm from '../fragment/BusinessSetupForm.vue'
    import Loader from '@/components/Loader.vue'

    const { wizards, updateWizards, loading } = useSetup()
</script>

<template>
    <Loader :loading="loading"></Loader>
    <BusinessSetupForm
        :form="wizards"
        :submitForm="updateWizards"
        ref="setupEditFormRef"
    />
</template>
