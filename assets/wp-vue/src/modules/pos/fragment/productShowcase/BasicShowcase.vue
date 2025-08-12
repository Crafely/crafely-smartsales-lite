<script setup lang="ts">
import GridView from '../GridView.vue'
import { priorities, statuses, columns } from '../../tableConfig'
import { Input } from '@/components/ui/input'
import { useSalesStore } from '@/stores/salesStore'
import {
    DataTable,
    DataTableToolbar,
    DataTableFacetedFilter,
} from '@/components/dataTable'

defineOptions({
    name: 'BasicShowcase',
})

const store = useSalesStore()
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
        <template #tbody="{ table }">
            <div
                class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-x-4 gap-y-10"
            >
                <template v-for="row in table.getRowModel().rows" :key="row.id">
                    <GridView
                        :product="row.original"
                        class="w-full"
                        aspect-ratio="square"
                        :width="null"
                        :height="null"
                        :increment="(product) => store.addToCart(product)"
                        :decrement="
                            (product) => store.decreaseQuantity(product)
                        "
                    />
                </template>
            </div>
        </template>
    </DataTable>
</template>
