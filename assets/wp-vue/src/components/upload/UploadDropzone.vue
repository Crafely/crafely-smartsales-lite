<template>
    <div
        class="relative"
        @dragover.prevent="handleDragOver"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop"
    >
        <div
            :class="[
                'rounded-lg border-2 border-dashed p-8 text-center transition-all',
                isDragging ? 'border-primary-500 bg-primary-50' : 'border-gray-300 hover:border-primary-400',
            ]"
        >
            <input
                type="file"
                @change="handleFileUpload"
                accept="image/*, .pdf, .doc, .docx"
                multiple
                ref="fileInput"
                class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10"
            />
            <div class="space-y-4">
                <UploadSimple
                    :size="40"
                    class="mx-auto text-gray-400"
                    weight="thin"
                />
                <p class="text-gray-600">Drag files here or click to upload</p>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { ref } from 'vue'

    const emit = defineEmits<{
        (e: 'filesSelected', files: File[]): void
    }>()

    const isDragging = ref(false)
    const fileInput = ref<HTMLInputElement | null>(null)

    const handleDragOver = () => (isDragging.value = true)

    const handleDrop = (event: DragEvent) => {
        isDragging.value = false
        const droppedFiles = event.dataTransfer?.files
        if (droppedFiles) emit('filesSelected', Array.from(droppedFiles))
    }

    const handleFileUpload = (event: Event) => {
        const target = event.target as HTMLInputElement
        if (target.files?.length) {
            emit('filesSelected', Array.from(target.files))
        }
    }
</script>
