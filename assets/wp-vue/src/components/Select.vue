<script setup lang="ts">
    import {
        Select,
        SelectContent,
        SelectGroup,
        SelectItem,
        SelectLabel,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select'

    interface Item {
        [key: string]: any
    }

    const model = defineModel<string>()

    withDefaults(
        defineProps<{
            items: Item[]
            placeholder?: string
            label?: string
            itemValue?: string
            itemKey?: string
        }>(),
        {
            itemValue: 'id',
            itemKey: 'name',
        }
    )
</script>

<template>
    <Select v-model="model">
        <SelectTrigger class="w-full">
            <SelectValue :placeholder="placeholder || 'Select an option'" />
        </SelectTrigger>
        <SelectContent>
            <SelectGroup>
                <SelectLabel v-if="label">{{ label }}</SelectLabel>
                <SelectItem
                    v-for="item in items"
                    :key="item[itemValue]"
                    :value="item[itemValue]"
                >
                    {{ item[itemKey] }}
                </SelectItem>
            </SelectGroup>
        </SelectContent>
    </Select>
</template>
