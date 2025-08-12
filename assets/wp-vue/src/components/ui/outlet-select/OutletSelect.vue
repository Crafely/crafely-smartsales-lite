<script setup lang="ts">
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select'

    interface Outlet {
        id: number
        name: string
    }

    defineProps<{
        modelValue: number | null
        outlets: Outlet[]
        label?: string
    }>()

    const emit = defineEmits<{
        'update:modelValue': [value: number]
    }>()
</script>

<template>
    <Select
        :model-value="modelValue"
        @update:model-value="emit('update:modelValue', $event)"
    >
        <SelectTrigger class="w-full">
            <SelectValue :placeholder="label || 'Select outlet'" />
        </SelectTrigger>
        <SelectContent>
            <SelectItem
                v-for="outlet in outlets"
                :key="outlet.id"
                :value="outlet.id"
            >
                {{ outlet.name }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
