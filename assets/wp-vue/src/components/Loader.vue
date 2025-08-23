<script setup>
    const props = defineProps({
        loading: {
            type: Boolean,
            required: true,
        },
    })
</script>
<template>
    <div
        v-if="loading"
        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-70 z-10"
    >
        <slot name="loader">
            <div class="text-xl font-mono animate-pulse">loading..</div>
        </slot>
    </div>
    <slot v-if="!loading" />
</template>
