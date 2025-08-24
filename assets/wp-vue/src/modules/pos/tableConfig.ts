import {
    ArrowDownIcon,
    ArrowRightIcon,
    ArrowUpIcon,
    CheckCircledIcon,
    CircleIcon,
    QuestionMarkCircledIcon,
    StopwatchIcon,
} from '@radix-icons/vue'
import { h, ref } from 'vue'
import { intersection } from 'lodash'
import DataTableColumnHeader from '@/components/dataTable/DataTableColumnHeader.vue'
import DataTableRowActions from './fragment/DataTableRowActions.vue'
export const labels = [
    {
        value: 'bug',
        label: 'Bug',
    },
    {
        value: 'feature',
        label: 'Feature',
    },
    {
        value: 'documentation',
        label: 'Documentation',
    },
]

export const statuses = [
    {
        value: 'Decor',
        label: 'Decor',
    },
    {
        value: 'Clothing',
        label: 'Clothing',
    },
    {
        value: 'Accessories',
        label: 'Accessories',
    },
    {
        value: 'Tshirts',
        label: 'Tshirts',
    },
]

export const priorities = [
    {
        value: 'low',
        label: 'Low',
        icon: h(ArrowDownIcon),
    },
    {
        value: 'medium',
        label: 'Medium',
        icon: h(ArrowRightIcon),
    },
    {
        value: 'high',
        label: 'High',
        icon: h(ArrowUpIcon),
    },
]

export const columns = [
    {
        accessorKey: 'src',
    },
    {
        accessorKey: 'sku',
    },
    {
        accessorKey: 'name',
    },
    {
        accessorKey: 'price',
    },
    {
        accessorKey: 'stock',
    },
    {
        accessorKey: 'categories',
        filterFn: (row, id, value) =>
            intersection(value, row.getValue(id)).length > 0,
    },
    {
        id: 'actions',
        cell: ({ row }) =>
            h(DataTableRowActions, {
                row,
                labels,
            }),
    },
]
