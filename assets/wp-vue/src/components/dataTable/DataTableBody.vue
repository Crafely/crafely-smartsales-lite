<script lang="ts" setup>
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
        TableEmpty,
    } from '@/components/ui/table'
    import { FlexRender } from '@tanstack/vue-table'
    defineProps({
        table: {
            type: Object,
            required: true,
        },
        columns: {
            type: Array,
            required: true,
        },
        activeProduct: {
            type: Object,
            default: null,
        },
    })
</script>

<template>
    <div class="rounded-md border">
        <Table>
            <TableHeader class="shadow-sm">
                <TableRow
                    v-for="headerGroup in table.getHeaderGroups()"
                    :key="headerGroup.id"
                >
                    <TableHead
                        v-for="header in headerGroup.headers"
                        :key="header.id"
                    >
                        <FlexRender
                            v-if="!header.isPlaceholder"
                            :render="header.column.columnDef.header"
                            :props="header.getContext()"
                        />
                    </TableHead>
                </TableRow>
            </TableHeader>
            <TableBody>
                <template v-if="table.getRowModel().rows?.length">
                    <slot name="tableBody" :table="table">
                        <TableRow
                            v-for="row in table.getRowModel().rows"
                            :key="row.id"
                            :data-state="
                                row.original.id == activeProduct?.id &&
                                'selected'
                            "
                            @click="$emit('rowClick', row.original)"
                            class="cursor-pointer"
                        >
                            <TableCell
                                v-for="cell in row.getVisibleCells()"
                                :key="cell.id"
                            >
                                <FlexRender
                                    :render="cell.column.columnDef.cell"
                                    :props="cell.getContext()"
                                />
                            </TableCell>
                        </TableRow>
                    </slot>
                </template>

                <TableEmpty
                    :colspan="columns.length"
                    class="text-center"
                    v-else
                >
                    No results.
                </TableEmpty>
            </TableBody>
        </Table>
    </div>
</template>
