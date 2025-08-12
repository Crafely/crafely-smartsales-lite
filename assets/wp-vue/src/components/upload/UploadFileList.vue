<template>
    <div v-if="files.length > 0" class="mt-6 space-y-3">
        <div
            v-for="(file, index) in files"
            :key="index"
            class="relative bg-white rounded-lg border border-gray-200 shadow-sm py-2"
        >
            <div class="flex items-center space-x-4">
                <div
                    class="w-12 h-12 rounded flex-shrink-0 bg-gray-50 flex items-center justify-center ml-3"
                >
                    <img
                        v-if="isImageFile(file.file)"
                        :src="file.preview"
                        :alt="file.name"
                        class="w-full h-full object-cover rounded"
                    />
                    <component
                        v-else
                        :is="getFileTypeIcon(file.file)"
                        :size="24"
                        :class="getFileTypeColor(file.file)"
                    />
                </div>

                <div class="min-w-0 flex-grow">
                    <p class="text-sm font-medium text-gray-900 truncate">
                        {{ file.name }}
                    </p>
                    <p class="text-xs text-gray-500">
                        {{ formatFileSize(file.size) }}
                    </p>
                </div>

                <div class="flex items-center space-x-2">
                    <button
                        v-if="stateIconMap[file.state].action"
                        @click="stateIconMap[file.state].action?.(file)"
                        class="p-2 rounded-full hover:bg-gray-50 transition-colors"
                    >
                        <UploadCloud class="w-5 h-5" />
                    </button>
                    <div v-else class="p-2">
                        <component
                            :is="stateIconMap[file.state].icon"
                            :class="stateIconMap[file.state].class"
                            :size="20"
                        />
                    </div>

                    <button
                        @click="$emit('removeFile', file)"
                        class="p-2 text-gray-400 hover:text-red-500 rounded-full hover:bg-gray-50 transition-colors"
                    >
                        <CrossCircledIcon :size="25" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import {
        UploadCloud,
        CircleCheck,
        CloudOffIcon,
        FileText,
        FileSpreadsheet,
        File,
        FileArchive,
    } from 'lucide-vue-next'
    import { h } from 'vue'
    import { CrossCircledIcon, ReloadIcon } from '@radix-icons/vue'
    import type { FileWithPreview } from './useFileUpload'

    const props = defineProps<{
        files: FileWithPreview[]
    }>()

    const emit = defineEmits<{
        (e: 'uploadFile', file: FileWithPreview): void
        (e: 'removeFile', file: FileWithPreview): void
    }>()

    const stateIconMap = {
        temporary: {
            icon: h(UploadCloud),
            class: 'text-gray-400 hover:text-primary-500',
            action: (file: FileWithPreview) => emit('uploadFile', file),
        },
        uploading: {
            icon: h(ReloadIcon),
            class: 'text-primary-500 animate-spin',
            action: null,
        },
        uploaded: {
            icon: h(CircleCheck),
            class: 'text-green-500',
            action: null,
        },
        failed: {
            icon: h(CloudOffIcon),
            class: 'text-red-500',
            action: (file: FileWithPreview) => emit('uploadFile', file),
        },
    } as const

    const formatFileSize = (bytes: number): string => {
        if (bytes === 0) return '0 Bytes'
        const k = 1024
        const sizes = ['Bytes', 'KB', 'MB', 'GB']
        const i = Math.floor(Math.log(bytes) / Math.log(k))
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
    }

    const fileTypeMap = {
        pdf: {
            icon: FileText,
            color: 'text-red-500',
        },
        doc: {
            icon: FileText,
            color: 'text-blue-500',
        },
        docx: {
            icon: FileText,
            color: 'text-blue-500',
        },
        xls: {
            icon: FileSpreadsheet,
            color: 'text-green-500',
        },
        xlsx: {
            icon: FileSpreadsheet,
            color: 'text-green-500',
        },
        txt: {
            icon: File,
            color: 'text-gray-500',
        },
        zip: {
            icon: FileArchive,
            color: 'text-yellow-500',
        },
    } as const

    const isImageFile = (file: File): boolean => {
        return file.type.startsWith('image/')
    }

    const getFileTypeIcon = (file: File): string => {
        const extension = file.name.split('.').pop()?.toLowerCase() || ''
        return (
            fileTypeMap[extension as keyof typeof fileTypeMap]?.icon || 'PhFile'
        )
    }

    const getFileTypeColor = (file: File): string => {
        const extension = file.name.split('.').pop()?.toLowerCase() || ''
        return (
            fileTypeMap[extension as keyof typeof fileTypeMap]?.color ||
            'text-gray-400'
        )
    }
</script>
