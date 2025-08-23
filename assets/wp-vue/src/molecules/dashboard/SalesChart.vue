<script setup lang="ts">
import { computed } from 'vue'
import { salesDataToChartConfig } from './utils'
import type { SalesAnalytics } from '@/types'
import Chart from '@/components/Chart.vue'
import Wrapper from './ChartWrapper.vue'

const props = defineProps<{
    salesData: SalesAnalytics
}>()

const chartConfig = computed(() => {
    if (!props.salesData) return null
    return salesDataToChartConfig(props.salesData)
})
</script>

<template>
    <Wrapper title="Sales Analytics">
        <Chart
            v-if="chartConfig"
            :key="JSON.stringify(chartConfig)"
            :chartConfig="chartConfig"
        />
    </Wrapper>
</template>