<template>
    <div class="min-w-52 space-y-1 relative">
        <slot></slot>
        <p class="text-sm text-gray-500">{{ label }}:</p>
        <h3 class="text-xl font-semibold">
            {{ title || 'Name' }}
        </h3>
        <p class="text-sm">Email: {{ email || 'demo@email.com' }}</p>
        <p class="text-sm">Address: {{ address || 'Route, State, City' }}</p>
    </div>
</template>

<script lang="ts" setup>
    defineProps<{
        label: string
        title: string
        email: string
        address: string
    }>()
</script>
