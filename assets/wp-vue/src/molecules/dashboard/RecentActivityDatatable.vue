<script setup lang="ts">
    import {
        DataTable,
        DataTableBody,
        DataTableToolbar,
        DataTableSearch,
    } from '@/components/dataTable'
    import { TableCell, TableRow } from '@/components/ui/table'
    import { columns } from './recentActivityTableColumn'
    import { RecentActivity, Pagination } from '@/types'

    defineProps<{
        activities: RecentActivity[]
    }>()
</script>

<template>
    <DataTable :data="activities" :columns="columns">
        <template #toolbar="{ table }">
            <DataTableToolbar :table="table">
                <template #search>
                    <DataTableSearch
                        placeholder="Search activities..."
                        :table="table"
                        search-key="title"
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
                        class="cursor-pointer"
                    >
                        <TableCell>#{{ row.original.id }}</TableCell>
                        <TableCell>{{ row.original.type }}</TableCell>
                        <TableCell>{{ row.original.title }}</TableCell>
                        <TableCell>{{ row.original.amount || '-' }}</TableCell>
                        <TableCell>{{ row.original.status }}</TableCell>
                        <TableCell>{{ row.original.date }}</TableCell>
                        <TableCell>{{ row.original.email || '-' }}</TableCell>
                    </TableRow>
                </template>
            </DataTableBody>
        </template>
    </DataTable>
</template>
