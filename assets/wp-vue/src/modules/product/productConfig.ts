import {
    CheckCircledIcon,
    CircleIcon,
    StopwatchIcon,
    LockClosedIcon,
} from '@radix-icons/vue'
import { Info, Edit, PlusCircle, Bot, Star, StarOff } from 'lucide-vue-next'
import intersection from 'lodash/intersection'
import { h, ref } from 'vue'
import { Checkbox } from '@/components/ui/checkbox'
import DataTableColumnHeader from '@/components/dataTable/DataTableColumnHeader.vue'
import DataTablePriceColumn from './fragment/DataTablePriceColumn.vue'
import CategoryLabel from './fragment/CategoryLabel.vue'

export const statuses = [
    {
        value: 'publish',
        label: 'Publish',
        icon: h(CheckCircledIcon),
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
        icon: h(LockClosedIcon),
    },
]
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

export const columns = [
    {
        id: 'select',
        header: ({ table }) =>
            h(Checkbox, {
                checked:
                    table.getIsAllPageRowsSelected() ||
                    (table.getIsSomePageRowsSelected() && 'indeterminate'),
                'onUpdate:checked': (value) =>
                    table.toggleAllPageRowsSelected(!!value),
                ariaLabel: 'Select all',
                class: 'translate-y-0.5',
            }),
        cell: ({ row }) =>
            h(Checkbox, {
                checked: row.getIsSelected(),
                'onUpdate:checked': (value) => row.toggleSelected(!!value),
                ariaLabel: 'Select row',
                class: 'translate-y-0.5',
            }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'sku',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'SKU' }),
        cell: ({ row }) => h('div', row.getValue('sku')),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'name',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Name' }),
        cell: ({ row }) => {
            return h('div', { class: '' }, [
                h(
                    'p',
                    { class: 'max-w-[500px] line-clamp-2 font-medium' },
                    row.getValue('name')
                ),
            ])
        },
    },
    {
        accessorKey: 'regular_price',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Price' }),
        cell: ({ row }) => {
            return h(DataTablePriceColumn, { row })
        },
    },
    {
        accessorKey: 'stock',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Stock' }),
        cell: ({ row }) => {
            return h(
                'span',
                { class: 'max-w-[500px] truncate font-medium' },
                row.getValue('stock')
            )
        },
    },
    {
        accessorKey: 'featured',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Featured' }),
        cell: ({ row }) =>
            h(Star, {
                fill: row.getValue('featured') ? 'currentColor' : 'none',
                class: 'h-4 w-4 text-yellow-500',
            }),
        enableSorting: false,
        enableHiding: true,
    },
    {
        accessorKey: 'categories',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Categories' }),
        cell: ({ row }) =>
            h(CategoryLabel, { categories: row.getValue('categories') }),
        filterFn: (row, id, value) =>
            intersection(value, row.getValue(id)).length > 0,
    },
    {
        accessorKey: 'status',
        header: ({ column }) =>
            h(DataTableColumnHeader, { column, title: 'Status' }),

        cell: ({ row }) => {
            const status = statuses.find(
                (status) => status.value === row.getValue('status')
            )

            if (!status) return null

            return h('div', { class: 'flex w-[100px] items-center' }, [
                status.icon &&
                    h(status.icon, {
                        class: 'mr-2 h-4 w-4 text-muted-foreground',
                    }),
                h('span', status.label),
            ])
        },
        filterFn: (row, id, value) => {
            return value.includes(row.getValue(id))
        },
    },
]
