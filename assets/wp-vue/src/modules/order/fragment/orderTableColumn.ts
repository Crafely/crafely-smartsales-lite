import DataTableColumnHeader from '@/components/dataTable/DataTableColumnHeader.vue'
import { h } from 'vue'
export const columns = [
    {
        accessorKey: 'id',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'ID' }),
    },
    {
        accessorKey: 'customer.full_name',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Name' }),
        enableSorting: false,
        enableHiding: false,
    },

    {
        accessorKey: 'status',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Status' }),
        enableSorting: false,
    },
    {
        accessorKey: 'discount_total',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Discount' }),
    },
    {
        accessorKey: 'total',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Amount' }),
    },
    {
        accessorKey: 'created_by',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Order placed By' }),
    },
]
