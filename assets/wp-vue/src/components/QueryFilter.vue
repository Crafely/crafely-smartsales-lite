<script setup lang="ts">
import Select from '@/components/Select.vue'
import DaterangePicker from '@/components/DaterangePicker.vue'
import { Button } from '@/components/ui/button'
import { ref, watch, computed } from 'vue'
import type { Outlet } from '@/types'

defineProps<{
    ranges: any[]
    outlets: Outlet[]
}>()

const emit = defineEmits<{
    (e: 'changeQuery', query: { range?: any; outlet_id?: string; start_date?: string; end_date?: string }): void
}>()

const selectedRange = ref()
const selectedOutlet = ref()
const selectedDate = ref()

// Compute if any filter is selected
const hasSelection = computed(() => {
    return selectedRange.value || selectedOutlet.value || (selectedDate.value?.start && selectedDate.value?.end)
})

// Reset function to clear all selections
const resetFilters = () => {
    selectedRange.value = undefined
    selectedOutlet.value = undefined
    selectedDate.value = { start: null, end: null }
    emit('changeQuery', null)
}

watch([selectedRange, selectedOutlet, selectedDate], () => {
    const query: { range?: any; outlet_id?: string; start_date?: string; end_date?: string } = {}
    
    if (selectedRange.value) {
        query.range = selectedRange.value
    }

    if (selectedOutlet.value) {
        query.outlet_id = selectedOutlet.value
    }

    if (selectedDate.value?.start && selectedDate.value?.end) {
        const startDate = new Date(selectedDate.value.start)
        const endDate = new Date(selectedDate.value.end)
        query.start_date = `${String(startDate.getDate()).padStart(2, '0')}-${String(startDate.getMonth() + 1).padStart(2, '0')}-${startDate.getFullYear()}`
        query.end_date = `${String(endDate.getDate()).padStart(2, '0')}-${String(endDate.getMonth() + 1).padStart(2, '0')}-${endDate.getFullYear()}`
    }
    
    // Only emit if query has at least one key-value pair
    const hasQueryValues = Object.keys(query).length > 0
    // If range is custom, both dates must be selected
    const isValidCustomRange = selectedRange.value !== 'custom' || (selectedDate.value?.start && selectedDate.value?.end)
    
    if (hasQueryValues && isValidCustomRange) {
        // console.log(query, 'from watch')
        emit('changeQuery', query)
    }
})
</script>

<template>
    <div class="flex items-center gap-4">
        <Select
            v-model="selectedRange"
            :items="ranges"
            itemValue="value"
            itemKey="label"
            placeholder="Select range"
        />

        <Select
            v-if="outlets?.length > 0"
            v-model="selectedOutlet"
            :items="outlets"
            placeholder="Select outlet"
        />

        <DaterangePicker
            v-if="selectedRange === 'custom'"
            v-model:dateRange="selectedDate"
        />

        <Button
            v-if="hasSelection"
            variant="outline"
            size="sm"
            @click="resetFilters"
        >
            Reset
        </Button>
    </div>
</template>