<script lang="ts" setup>
    import type { Pagination } from '@/types'
    import DataTablePagination from './DataTablePagination.vue'
    import { DataTableBody } from '@/components/dataTable'
    import { useTable } from './useTable'

    const props = defineProps<{
        columns: any[]
        data: any[]
        pagination?: Pagination
        reFetch?: (pagination: Pagination) => void
        simplePagination: any[]
        activeProduct: any
    }>()
    const emits = defineEmits<{
        (e: 'change:pagination', pagination: Pagination): void
        (e: 'rowClick', item: any): void
    }>()

    const { table } = useTable(props, emits)
</script>

<template>
    <div class="space-y-4">
        <slot name="toolbar" :table="table" />
        <slot name="tbody" :table="table">
            <DataTableBody
                @rowClick="(value) => $emit('rowClick', value)"
                :table="table"
                :columns="columns"
                :activeProduct="activeProduct"
            />
        </slot>
        <DataTablePagination
            :table="table"
            :simplePagination="simplePagination"
        />
    </div>
</template>
