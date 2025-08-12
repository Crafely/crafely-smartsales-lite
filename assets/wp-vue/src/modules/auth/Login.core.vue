<script setup lang="ts">
    import LoginForm from './fragment/LoginForm.vue'
    import { useAuth } from './useAuth'
    import Loader from '@/components/Loader.vue'
    const { login, loading } = useAuth()
</script>

<template>
    <Loader :loading="loading"></Loader>
    <div
        class="flex min-h-svh flex-col items-center justify-center bg-muted p-6 md:p-10"
    >
        <div class="w-full max-w-sm md:max-w-3xl">
            <LoginForm ref="loginFormRef" :submitForm="login" />
        </div>
    </div>
</template>
