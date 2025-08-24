<script setup lang="ts">
import { computed, useId } from 'vue'
import { customerDataToChartConfig } from './utils'
import type { CustomerAnalytics} from '@/types'
import Chart from '@/components/Chart.vue'
import Wrapper from './ChartWrapper.vue'

const props = defineProps<{
    customerData: CustomerAnalytics
}>()
const id = useId()
const chartConfig = computed(() => {
    if (!props.customerData) return null
    return customerDataToChartConfig(props.customerData)
})
</script>

<template>
    <Wrapper title="Customer Analytics">
        <Chart
            v-if="chartConfig"
            :key="id"
            :chartConfig="chartConfig"
        />
    </Wrapper>
</template>