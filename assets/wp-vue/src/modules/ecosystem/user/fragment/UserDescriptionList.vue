<template>
    <div class="flex items-center justify-between">
        <dt class="flex items-center gap-1 text-muted-foreground">
            {{ label }}
        </dt>
        <dd>
            <slot>{{ value }}</slot>
        </dd>
    </div>
</template>

<script lang="ts" setup>
    defineProps<{
        label: string
        value?: string
    }>()
</script>
