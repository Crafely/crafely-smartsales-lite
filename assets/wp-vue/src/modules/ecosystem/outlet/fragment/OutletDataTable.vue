<script setup lang="ts">
    import {
        DataTable,
        DataTableBody,
        DataTableToolbar,
        DataTableSearch,
        DataTableViewOptions,
    } from '@/components/dataTable'
    import { TableCell, TableRow } from '@/components/ui/table'
    import { columns } from '../outletConfig'
    import { Outlet } from '@/types'
    defineProps<{
        outlets: Outlet[]
        activeOutlet: Outlet
    }>()
</script>

<template>
    <DataTable :data="outlets" :columns="columns">
        <template #toolbar="{ table }">
            <DataTableToolbar :table="table">
                <template #search>
                    <DataTableSearch
                        placeholder="Enter outlet name"
                        :table="table"
                        search-key="name"
                    />
                </template>
                <template #viewOption>
                    <DataTableViewOptions :table="table" />
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
                            row.original.id == activeOutlet?.id && 'selected'
                        "
                        class="cursor-pointer"
                    >
                        <TableCell class="font-medium align-middle">
                            <div class="font-medium">
                                {{ row.original?.name || '' }}
                            </div>
                            <div
                                class="hidden text-sm text-muted-foreground md:inline"
                            >
                                {{ row.original?.address || '' }}
                            </div>
                        </TableCell>
                        <TableCell>
                            {{ row.original.email }}
                        </TableCell>
                        <TableCell>
                            {{ row.original.phone }}
                        </TableCell>
                        <TableCell>
                            {{ row.original.manager_name }}
                        </TableCell>
                        <TableCell>
                            {{ row.original.operating_hours }}
                        </TableCell>
                        <TableCell>
                            {{ row.original.status }}
                        </TableCell>
                    </TableRow>
                </template>
            </DataTableBody>
        </template>
    </DataTable>
</template>
