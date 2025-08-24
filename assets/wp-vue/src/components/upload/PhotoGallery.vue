<template>
    <div class="columns-3 gap-4 my-8 space-y-4">
        <div v-for="image in images" :key="image.id" class="break-inside-avoid">
            <img
                :src="get(image, filePath, '')"
                :alt="image.alt_description"
                class="w-full rounded-lg hover:opacity-75 transition-opacity cursor-pointer"
                :class="{ 'border-2 border-primary': selectedImage === image }"
                @click="selectedImage = image"
            />
        </div>
    </div>
    <div
        class="sticky bottom-0 -right-2 pt-2 pr-2 w-full bg-white flex justify-end"
    >
        <Button variant="outline" @click="selectImage(selectedImage)"
            >Set Product Image</Button
        >
    </div>
</template>

<script setup lang="ts">
    import { get } from 'lodash'
    import { Button } from '@/components/ui'
    import { ref } from 'vue'
    defineProps<{
        images: any[]
        filePath: string
    }>()
    const selectedImage = ref(null)
    const emit = defineEmits<{
        (e: 'select', image: any): void
    }>()

    const selectImage = (image: any) => {
        emit('select', image)
    }
</script>
