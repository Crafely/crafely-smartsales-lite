<script setup lang="ts">
import { columns } from '../../tableConfig'
import { Input } from '@/components/ui/input'
import { useProductStore } from '@/stores/productStore'
import { storeToRefs } from 'pinia'

import Pagination from '@/components/Pagination.vue'
import {
    DataTable,
    DataTableToolbar,
    DataTableFacetedFilter,
} from '@/components/dataTable'

defineProps({
    products: Array,
})

const productStore = useProductStore()
const { getProductsByQuery }= productStore
let { searchKey } = storeToRefs(productStore)
</script>

<template>
    <div class="space-y-8">
        <Input v-model="searchKey" @input="getProductsByQuery"  />
        <DataTable :data="products" :columns="columns" hidePaginate>
            <!-- <template #toolbar="{ table }">
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
            </template> -->
        </DataTable>
        <div>
            <Pagination />
        </div>
    </div>
</template>
