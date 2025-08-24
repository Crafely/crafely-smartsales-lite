<script setup>
    import { Button } from '@/components/ui/button'
    import { Cross2Icon } from '@radix-icons/vue'
    import { computed } from 'vue'
    const props = defineProps({
        table: {
            type: Object,
            required: true,
        },
    })

    const isFiltered = computed(
        () => props.table.getState().columnFilters.length > 0
    )
</script>

<template>
    <div class="flex justify-between">
        <div class="flex flex-1 flex-wrap items-center gap-2">
            <slot name="search" />
            <slot name="filter" />
            <Button
                v-if="isFiltered"
                variant="ghost"
                class="h-8 px-2 lg:px-3"
                @click="table.resetColumnFilters()"
            >
                Reset
                <Cross2Icon class="ml-2 h-4 w-4" />
            </Button>
        </div>
        <slot name="viewOption" />
    </div>
</template>
