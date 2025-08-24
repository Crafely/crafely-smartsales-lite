<script setup lang="ts">
    import {
        DataTable,
        DataTableBody,
        DataTableToolbar,
        DataTableSearch,
    } from '@/components/dataTable'
    import { TableCell, TableRow } from '@/components/ui/table'
    import { columns } from './orderTableColumn'
    import { Order, Pagination } from '@/types'
    import { useAppStore } from '@/stores/appStore'
    import { storeToRefs } from 'pinia'
    import { formatAmount, formatDateTime } from '@/utils'
    const { currency } = storeToRefs(useAppStore())
    defineProps<{
        orders: Order[]
        pagination: Pagination
        getOrders: (query: any) => void
        activeOrder: Order
    }>()
</script>

<template>
    <DataTable
        :data="orders"
        :columns="columns"
        :pagination="pagination"
        :reFetch="getOrders"
    >
        <template #toolbar="{ table }">
            <DataTableToolbar :table="table">
                <template #search>
                    <DataTableSearch
                        placeholder="Enter order id"
                        :table="table"
                        search-key="id"
                    />
                </template>
            </DataTableToolbar>
        </template>
        <template #tbody="{ table }">
            <DataTableBody :table="table" :columns="columns">
                <template #tableBody>
                    <TableRow
                        v-for="row in table.getRowModel().rows"
                        :key="row.id"
                        @click="$emit('rowClick', row.original)"
                        :data-state="
                            row.original.id == activeOrder?.id && 'selected'
                        "
                        class="cursor-pointer"
                    >
                        <TableCell class="!w-auto">
                            #{{ row.original.id }}
                        </TableCell>
                        <TableCell class="font-medium align-middle">
                            <div>
                                <div class="font-medium">
                                    {{
                                        row.original?.customer?.full_name || ''
                                    }}
                                </div>
                                <div
                                    class="hidden text-sm text-muted-foreground md:inline"
                                >
                                    {{ row.original?.customer?.email || '' }}
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>
                            {{ row.original.status }}
                        </TableCell>
                        <TableCell>
                            {{
                                formatAmount(
                                    row.original.discount_total,
                                    currency
                                )
                            }}
                        </TableCell>
                        <TableCell>
                            {{ formatAmount(row.original.total, currency) }}
                        </TableCell>

                        <TableCell class="font-medium align-middle">
                            <div>
                                <div class="font-medium">
                                    {{ row.original?.created_by?.name || '' }}
                                </div>
                                <div
                                    class="hidden text-sm text-muted-foreground md:inline"
                                >
                                    {{ row.original?.created_by?.outlet || '' }}
                                </div>
                            </div>
                        </TableCell>
                    </TableRow>
                </template>
            </DataTableBody>
        </template>
    </DataTable>
</template>
