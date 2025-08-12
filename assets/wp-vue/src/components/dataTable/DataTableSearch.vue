<script lang="ts" setup>
    import { Input } from '@/components/ui/input'
    import { cn } from '@/lib/utils'
    defineProps({
        table: {
            type: Object,
            required: true,
        },
        placeholder: {
            type: String,
            default: 'Search...',
        },
        searchKey: {
            type: String,
            default: 'name',
        },
        inputClass: {
            type: String,
            default: '',
        },
    })
</script>
<template>
    <Input
        :placeholder="placeholder"
        :model-value="table.getColumn(searchKey)?.getFilterValue() ?? ''"
        :class="cn('h-8 w-[150px] lg:w-[250px]', inputClass)"
        @input="table.getColumn(searchKey)?.setFilterValue($event.target.value)"
    />
</template>
