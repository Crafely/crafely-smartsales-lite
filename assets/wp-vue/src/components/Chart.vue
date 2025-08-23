<script setup lang="ts">
import { onMounted, onBeforeUnmount, watch, ref, nextTick } from 'vue'
import Chart from 'chart.js/auto'

interface ChartConfig {
    data: any
    options?: any
}

const props = defineProps<{
    chartConfig: ChartConfig
}>()

const chartInstance = ref<Chart | null>(null)
const canvasRef = ref<HTMLCanvasElement | null>(null)
const resizeObserver = ref<ResizeObserver | null>(null)
const isInitializing = ref(false)

const cleanupChart = () => {
    if (chartInstance.value) {
        chartInstance.value.destroy()
        chartInstance.value = null
    }

    if (resizeObserver.value) {
        resizeObserver.value.disconnect()
        resizeObserver.value = null
    }

    // Clear the canvas context
    if (canvasRef.value) {
        const ctx = canvasRef.value.getContext('2d')
        if (ctx) {
            ctx.clearRect(0, 0, canvasRef.value.width, canvasRef.value.height)
        }
    }
}

const initChart = async () => {
    if (isInitializing.value) return
    isInitializing.value = true

    try {
        await nextTick()
        
        if (!canvasRef.value) {
            console.error('Canvas element not found')
            return
        }

        const ctx = canvasRef.value.getContext('2d')
        if (!ctx) {
            console.error('Canvas context not available')
            return
        }

        // Ensure cleanup before creating new chart
        cleanupChart()

        if (!props.chartConfig) {
            console.error('Chart configuration is missing')
            return
        }

        chartInstance.value = new Chart(ctx, {
            ...props.chartConfig
        })

        // Setup resize observer
        resizeObserver.value = new ResizeObserver(() => {
            if (chartInstance.value) {
                chartInstance.value.resize()
            }
        })
        resizeObserver.value.observe(canvasRef.value)
    } catch (error) {
        console.error('Failed to initialize chart:', error)
        cleanupChart()
    }
}

onMounted(async () => {
    await initChart()
})

onBeforeUnmount(() => {
    cleanupChart()
})

watch(() => props.chartConfig, async (newConfig, oldConfig) => {
    if (newConfig !== oldConfig) {
        await nextTick()
        await initChart()
    }
}, { deep: true, immediate: true })
</script>

<template>
    <div style="width: 100%;">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>