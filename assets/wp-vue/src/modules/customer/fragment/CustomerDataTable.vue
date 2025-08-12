<script setup lang="ts">
    import {
        DataTable,
        DataTableBody,
        DataTableToolbar,
        DataTableSearch,
    } from '@/components/dataTable'
    import { TableCell, TableRow } from '@/components/ui/table'
    import { columns } from '../customerConfig'
    import { Customer } from '@/types'
    defineProps<{
        customers: Customer[]
        activeCustomer: Customer
    }>()
</script>

<template>
    <DataTable :data="customers" :columns="columns">
        <template #toolbar="{ table }">
            <DataTableToolbar :table="table">
                <template #search>
                    <DataTableSearch
                        placeholder="Enter customer name"
                        :table="table"
                        search-key="full_name"
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
                            row.original.id == activeCustomer?.id && 'selected'
                        "
                        class="cursor-pointer"
                    >
                        <TableCell class="!w-44">
                            <img
                                alt="Customer profile image"
                                class="aspect-square rounded-full object-cover h-10 w-10"
                                :src="row.original?.profile_image"
                            />
                        </TableCell>

                        <TableCell class="font-medium align-middle">
                            <div>
                                <div class="font-medium">
                                    {{ row.original?.full_name || '' }}
                                </div>
                                <div
                                    class="hidden text-sm text-muted-foreground md:inline"
                                >
                                    {{ row.original?.email || '' }}
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>
                            {{ row.original.phone }}
                        </TableCell>
                        <TableCell class="font-medium align-middle">
                            <div v-if="row.original.shipping">
                                <div class="font-medium">
                                    {{
                                        row.original?.shipping?.address_1 || ''
                                    }}
                                </div>
                                <div
                                    class="text-sm text-muted-foreground md:inline"
                                >
                                    {{ row.original?.shipping?.postcode || '' }}
                                    {{ row.original?.shipping?.city || '' }}
                                    {{ row.original?.shipping?.state || '' }}
                                </div>
                            </div>
                        </TableCell>
                        <TableCell>
                            {{ row.original.total_orders || '' }}
                        </TableCell>
                    </TableRow>
                </template>
            </DataTableBody>
        </template>
    </DataTable>
</template>
