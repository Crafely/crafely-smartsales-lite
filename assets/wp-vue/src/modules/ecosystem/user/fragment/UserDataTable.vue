<script setup lang="ts">
    import {
        DataTable,
        DataTableBody,
        DataTableToolbar,
        DataTableSearch,
    } from '@/components/dataTable'
    import { TableCell, TableRow } from '@/components/ui/table'
    import { columns } from '../userConfig'
    import { User } from '@/types'
    defineProps<{
        users: User[]
        activeUser?: User
    }>()
</script>

<template>
    <DataTable :data="users" :columns="columns">
        <template #toolbar="{ table }">
            <DataTableToolbar :table="table">
                <template #search>
                    <DataTableSearch
                        placeholder="Enter user name"
                        :table="table"
                        search-key="name"
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
                        :data-state="
                            row.original.id == activeUser?.id && 'selected'
                        "
                        @click="$emit('rowClick', row.original)"
                    >
                        <TableCell>
                            <img
                                alt="Customer profile image"
                                class="aspect-square rounded-full object-cover h-10 w-10"
                                :src="row.original?.avatar"
                            />
                        </TableCell>
                        <TableCell>
                            {{ row.original.name }}
                        </TableCell>
                        <TableCell>
                            {{ row.original.email }}
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
