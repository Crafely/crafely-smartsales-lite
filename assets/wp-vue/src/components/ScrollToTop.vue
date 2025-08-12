<script setup lang="ts">
    import { computed } from 'vue'
    import { useScroll } from '@vueuse/core'
    import { ArrowUpToLine } from 'lucide-vue-next'

    const { y } = useScroll(window)

    const scrollToTop = () => {
        window.scrollTo({ top: 0, behavior: 'smooth' })
    }
    const isVisible = computed(() => y.value > 250)
</script>
<template>
    <button
        v-show="isVisible"
        class="flex justify-center items-center fixed bottom-14 right-4 z-50 w-14 md:w-10 h-14 md:h-10 rounded-full border border-input bg-background shadow-sm hover:bg-accent hover:text-accent-foreground"
        @click="scrollToTop"
    >
        <ArrowUpToLine class="w-6 md:w-4 h-6 md:h-4" />
    </button>
</template>
