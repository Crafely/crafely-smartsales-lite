<template>
    <div
        class="flex flex-col"
        :style="`height: calc(100vh - ${headerHeight}px)`"
    >
        <div :style="{ zoom: isSmallScreen ? 0.9 : 1 }">
            <slot name="header" />
        </div>
        <div
            class="flex-1 overflow-y-auto"
            :style="{ zoom: isSmallScreen ? 0.9 : 1 }"
        >
            <slot />
        </div>

        <footer :style="{ zoom: isSmallScreen ? 0.9 : 1 }">
            <slot name="footer" />
        </footer>
    </div>
</template>

<script setup lang="ts">
    import { computed } from 'vue'
    const props = defineProps({
        headerHeight: {
            type: Number,
            default: 64,
        },
    })

    const isSmallScreen = computed(
        () => window.matchMedia('(max-width: 767px)').matches
    )
</script>
