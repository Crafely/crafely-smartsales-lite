<script setup lang="ts">
    import RegistrationForm from './fragment/RegistrationForm.vue'
    import { useAuth } from './useAuth'
    import Loader from '@/components/Loader.vue'
    const { register, loading } = useAuth()
</script>

<template>
    <Loader :loading="loading"></Loader>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-muted p-6 md:p-10"
    >
        <div class="w-full max-w-sm md:max-w-3xl">
            <RegistrationForm ref="registerFormRef" :submitForm="register" />
        </div>
    </div>
</template>
