import { ref, h } from 'vue'
import { NotebookText, PlusCircle } from 'lucide-vue-next'
import DataTableColumnHeader from '@/components/dataTable/DataTableColumnHeader.vue'

// Tabs
export const subMenuTabs = ref([
    {
        label: 'Invoice',
        value: 'details',
        icon: h(NotebookText),
    },
    {
        label: 'Add Item',
        value: 'addItem',
        icon: h(PlusCircle),
    },
    {
        label: 'Create Customer',
        value: 'create',
        icon: h(PlusCircle),
    },
])

export const columns = [
    {
        accessorKey: 'id',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'ID' }),
    },
    {
        accessorKey: 'customer_id',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Name' }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'issue_date',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Issue Date' }),
    },
    {
        accessorKey: 'subtotal',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Amount' }),
    },
]
