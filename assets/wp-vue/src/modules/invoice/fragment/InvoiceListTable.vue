<script lang="ts" setup>
    import { useInvoice } from '../useInvoice'
    import {
        DataTable,
        DataTableBody,
        DataTableToolbar,
        DataTableSearch,
    } from '@/components/dataTable'
    import { columns } from '../invoiceConfig'

    import { formatAmount, formatDate } from '@/utils'
    import { TableCell, TableRow } from '@/components/ui/table'

    const {
        invoices,
        pagination,
        getInvoices,
        activeInvoice,
        getSingleInvoice,
    } = useInvoice()
</script>

<template>
    <div class="p-4">
        <DataTable
            :data="invoices"
            :columns="columns"
            :pagination="pagination"
            :simplePagination="true"
            :reFetch="getInvoices"
        >
            <template #toolbar="{ table }">
                <DataTableToolbar :table="table">
                    <template #search>
                        <DataTableSearch
                            placeholder="Search invoice by id"
                            :table="table"
                            search-key="id"
                            :inputClass="`!w-full h-10`"
                        />
                    </template>
                </DataTableToolbar>
            </template>
            <template #tbody="{ table }">
                <DataTableBody :table="table" :columns="columns">
                    <template #tableBody>
                        <TableRow
                            v-for="(row, index) in table.getRowModel().rows"
                            :key="row.id"
                            @click="getSingleInvoice(row.original.id)"
                            :data-state="
                                row.original.id == activeInvoice?.id &&
                                'selected'
                            "
                            class="cursor-pointer"
                        >
                            <TableCell> #{{ row.original.id }} </TableCell>
                            <TableCell class="font-medium align-middle w-full">
                                <div>
                                    {{ row.original.customer?.full_name }}
                                </div>
                            </TableCell>
                            <TableCell class="w-full">
                                {{
                                    formatDate(
                                        row.original.issue_date,
                                        'dd/MM/yyyy'
                                    )
                                }}
                            </TableCell>
                            <TableCell class="w-full">
                                {{
                                    formatAmount(
                                        row.original.subtotal,
                                        currency
                                    )
                                }}
                            </TableCell>
                        </TableRow>
                    </template>
                </DataTableBody>
            </template>
        </DataTable>
    </div>
</template>
