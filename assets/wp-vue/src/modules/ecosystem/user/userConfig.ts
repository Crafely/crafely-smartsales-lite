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
        label: 'Edit',
        value: 'edit',
        icon: h(Edit),
    },
    {
        label: 'Create',
        value: 'create',
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
        accessorKey: 'avatar',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Image' }),
    },
    {
        accessorKey: 'name',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Name' }),
    },
    {
        accessorKey: 'email',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Email' }),
    },
    {
        accessorKey: 'status',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Status' }),
    },
]
