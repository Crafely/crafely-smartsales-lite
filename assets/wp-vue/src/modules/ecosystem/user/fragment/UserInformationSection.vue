<template>
    <div class="grid gap-3">
        <div class="font-semibold">{{ title }}</div>
        <slot />
    </div>
</template>

<script lang="ts" setup>
    defineProps<{
        title: string
    }>()
</script>
