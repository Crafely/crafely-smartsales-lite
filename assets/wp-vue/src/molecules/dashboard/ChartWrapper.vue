<script setup lang="ts">
import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
} from '@/components/ui/card'

withDefaults(defineProps<{
    title?: string
}>(), {
    title: 'Sales Analytics'
})
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>{{ title }}</CardTitle>
        </CardHeader>
        <CardContent>
            <div class="flex items-center justify-between space-y-2">
                <slot />
            </div>
        </CardContent>
    </Card>
</template>