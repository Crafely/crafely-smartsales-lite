import { h, ref } from 'vue'
import { ColumnDef } from '@tanstack/vue-table'
import { Outlet } from '@/types'
import DataTableColumnHeader from '@/components/dataTable/DataTableColumnHeader.vue'
import { Info, TicketPercent, Edit, PlusCircle } from 'lucide-vue-next'

export const subMenuTabs = ref([
    {
        label: 'Details',
        value: 'details',
        icon: h(Info),
    },
    {
        label: 'Edit',
        value: 'edit',
        icon: h(Edit),
    },
    {
        label: 'Create',
        value: 'create',
        icon: h(PlusCircle),
    },
    {
        value: 'counter',
        label: 'Add Counter',
        icon: h(TicketPercent),
    },
])

export const columns: ColumnDef<Outlet>[] = [
    {
        accessorKey: 'name',
        header: 'Name',
    },
    {
        accessorKey: 'Email',
        header: 'Email',
    },
    {
        accessorKey: 'phone',
        header: 'Phone',
    },
    {
        accessorKey: 'manager_name',
        header: 'Manager',
    },
    {
        accessorKey: 'operating_hours',
        header: 'Operating Hour',
    },
    {
        accessorKey: 'status',
        header: 'Status',
    },
]

export const counterColumns: ColumnDef<Outlet>[] = [
    {
        accessorKey: 'name',
        header: 'Name',
    },
    {
        id: 'actions',
        header: ({ column }) =>
            h(DataTableColumnHeader, {
                column,
                title: 'Action',
                class: 'text-right',
            }),
    },
]
