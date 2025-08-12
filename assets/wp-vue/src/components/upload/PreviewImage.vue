<template>
    <div class="relative w-full h-40 rounded-md overflow-hidden flex-shrink-0">
        <CircleX
            class="absolute right-1 top-1 text-red-300 cursor-pointer"
            @click.stop="$emit('close')"
        />
        <img
            :src="src"
            class="w-full h-full object-contain cursor-pointer"
            alt="Product image"
        />
    </div>
</template>

<script lang="ts" setup>
    import { CircleX } from 'lucide-vue-next'

    defineProps<{
        src: string
    }>()
</script>
