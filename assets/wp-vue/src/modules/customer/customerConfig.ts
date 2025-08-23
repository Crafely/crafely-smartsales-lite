import {
    CheckCircledIcon,
    CircleIcon,
    QuestionMarkCircledIcon,
    StopwatchIcon,
} from '@radix-icons/vue'
import { h, ref } from 'vue'
import DataTableColumnHeader from '@/components/dataTable/DataTableColumnHeader.vue'
import { Info, Edit, PlusCircle } from 'lucide-vue-next'

export const subMenuTabs = ref([
    {
        label: 'Details',
        value: 'details',
        icon: h(Info),
    },
    {
        value: 'edit',
        label: 'Edit',
        icon: h(Edit),
    },
    {
        value: 'create',
        label: 'Create',
        icon: h(PlusCircle),
    },
])

export const statuses = [
    {
        value: 'publish',
        label: 'Publish',
        icon: h(QuestionMarkCircledIcon),
    },
    {
        value: 'draft',
        label: 'Draft',
        icon: h(CircleIcon),
    },
    {
        value: 'pending',
        label: 'Pending',
        icon: h(StopwatchIcon),
    },
    {
        value: 'private',
        label: 'Private',
        icon: h(CheckCircledIcon),
    },
]

export const columns = [
    {
        accessorKey: 'src',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Image' }),
        enableSorting: false,
        enableHiding: false,
    },

    {
        accessorKey: 'full_name',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Name' }),
    },
    {
        accessorKey: 'phone',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Phone' }),
    },
    {
        accessorKey: 'shipping',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Address' }),
        enableSorting: false,
    },
    {
        accessorKey: 'total_orders',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Total Order' }),
    },
]
