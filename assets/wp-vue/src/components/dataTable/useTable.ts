import {
    getCoreRowModel,
    getFacetedRowModel,
    getFacetedUniqueValues,
    getFilteredRowModel,
    getPaginationRowModel,
    getSortedRowModel,
    useVueTable,
} from '@tanstack/vue-table'
import { valueUpdater } from '@/lib/utils'
import { ref } from 'vue'
import { isEqual } from 'lodash'

export const useTable = (props, emits) => {
    const sorting = ref([])
    const columnFilters = ref([])
    const columnVisibility = ref({})
    const rowSelection = ref({})
    let table: any = null
    if (props.reFetch) {
        table = useVueTable({
            manualPagination: true,
            get pageCount() {
                return props.pagination.pageCount
            },
            get data() {
                return props.data
            },
            get columns() {
                return props.columns
            },
            state: {
                get sorting() {
                    return sorting.value
                },
                get columnFilters() {
                    return columnFilters.value
                },
                get columnVisibility() {
                    return columnVisibility.value
                },
                get rowSelection() {
                    return rowSelection.value
                },
                get pagination() {
                    return props.pagination
                },
            },
            enableRowSelection: true,
            onSortingChange: (updaterOrValue) =>
                valueUpdater(updaterOrValue, sorting),
            onColumnFiltersChange: (updaterOrValue) =>
                valueUpdater(updaterOrValue, columnFilters),
            onColumnVisibilityChange: (updaterOrValue) =>
                valueUpdater(updaterOrValue, columnVisibility),
            onRowSelectionChange: (updaterOrValue) =>
                valueUpdater(updaterOrValue, rowSelection),
            onPaginationChange: (updaterOrValue) => {
                const updatedPagination =
                    typeof updaterOrValue === 'function'
                        ? updaterOrValue(props.pagination)
                        : updaterOrValue
                if (!isEqual(props.pagination, updatedPagination)) {
                    Object.assign(props.pagination, updatedPagination)
                    props.reFetch(props.pagination)
                }
            },
            getCoreRowModel: getCoreRowModel(),
            getFilteredRowModel: getFilteredRowModel(),
            getPaginationRowModel: getPaginationRowModel(),
            getSortedRowModel: getSortedRowModel(),
            getFacetedRowModel: getFacetedRowModel(),
            getFacetedUniqueValues: getFacetedUniqueValues(),
        })
    } else {
        table = useVueTable({
            get data() {
                return props.data
            },
            get columns() {
                return props.columns
            },
            state: {
                get sorting() {
                    return sorting.value
                },
                get columnFilters() {
                    return columnFilters.value
                },
                get columnVisibility() {
                    return columnVisibility.value
                },
                get rowSelection() {
                    return rowSelection.value
                },
            },
            enableRowSelection: true,
            onSortingChange: (updaterOrValue) =>
                valueUpdater(updaterOrValue, sorting),
            onColumnFiltersChange: (updaterOrValue) =>
                valueUpdater(updaterOrValue, columnFilters),
            onColumnVisibilityChange: (updaterOrValue) =>
                valueUpdater(updaterOrValue, columnVisibility),
            onRowSelectionChange: (updaterOrValue) =>
                valueUpdater(updaterOrValue, rowSelection),
            getCoreRowModel: getCoreRowModel(),
            getFilteredRowModel: getFilteredRowModel(),
            getPaginationRowModel: getPaginationRowModel(),
            getSortedRowModel: getSortedRowModel(),
            getFacetedRowModel: getFacetedRowModel(),
            getFacetedUniqueValues: getFacetedUniqueValues(),
        })
    }

    return {
        table,
    }
}
