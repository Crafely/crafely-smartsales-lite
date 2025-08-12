<script setup lang="ts">
import { priorities, statuses, columns } from '../../tableConfig'
import { Input } from '@/components/ui/input'
import { useSalesStore } from '@/stores/salesStore'
import {
    DataTable,
    DataTableToolbar,
    DataTableFacetedFilter,
} from '@/components/dataTable'

defineProps({
    products: Array,
})
</script>

<template>
    <DataTable :data="products" :columns="columns">
        <template #toolbar="{ table }">
            <DataTableToolbar :table="table" :priorities="priorities">
                <template #search>
                    <Input class="max-w-sm" placeholder="Filter name..."
                        :model-value="table.getColumn('name')?.getFilterValue() || ''"
                        @update:model-value=" table.getColumn('name')?.setFilterValue($event)" />
                </template>
                <template #filter>
                    <DataTableFacetedFilter
                        v-if="table.getColumn('categories')"
                        :column="table.getColumn('categories')"
                        title="Categories"
                        :options="statuses"
                    />
                </template>
            </DataTableToolbar>
        </template>
    </DataTable>
</template>
