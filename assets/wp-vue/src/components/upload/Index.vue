<script setup lang="ts">
    import { ref, computed, watch } from 'vue'
    import { ReloadIcon, UploadIcon } from '@radix-icons/vue'
    import { useFileUpload } from './useFileUpload'
    import UploadDropzone from './UploadDropzone.vue'
    import UploadFileList from './UploadFileList.vue'
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs'
    import {
        Dialog,
        DialogDescription,
        DialogHeader,
        DialogScrollContent,
        DialogTitle,
    } from '@/components/ui/dialog'
    import PhotoGallery from './PhotoGallery.vue'
    import StickyFooterLayout from '@/layout/StickyFooterLayout.vue'

    const { files, uploadedFiles, handleFiles, uploadFile, removeFile } =
        useFileUpload()
    const isLoading = ref(false)
    const emit = defineEmits<{
        (e: 'select', item?: any): void
        (e: 'update:url', url: string): void
    }>()
    const props = defineProps<{
        url?: string
    }>()
    const hasMultipleTemporaryFiles = computed(() => {
        const temporaryFiles = files.value.filter(
            (file) => file.state === 'temporary'
        )
        return temporaryFiles.length > 1
    })
    const uploadAllFiles = async () => {
        isLoading.value = true
        try {
            await Promise.all(
                files.value.map((file) => {
                    if (file.state === 'temporary') {
                        return uploadFile(file)
                    }
                })
            )
        } finally {
            isLoading.value = false
        }
    }
    const localModel = defineModel<boolean>()
    const previewUrl = ref<string>('')

    const closePreview = () => {
        emit('select', null)
        previewUrl.value = ''
    }

    watch(
        () => props.url,
        (url) => {
            previewUrl.value = url
        },
        {
            immediate: true,
        }
    )
</script>

<template>
    <slot :previewUrl="previewUrl" :closePreview="closePreview" />
    <Dialog v-model:open="localModel">
        <DialogScrollContent
            @interact-outside="
                (event) => {
                    event.preventDefault()
                }
            "
        >
            <DialogHeader>
                <DialogTitle>Photo Gallery</DialogTitle>
                <DialogDescription>
                    Your uploaded photos will be displayed here.
                </DialogDescription>
            </DialogHeader>

            <Tabs default-value="gallery" class="space-y-4">
                <TabsList>
                    <TabsTrigger value="upload"> Upload </TabsTrigger>
                    <TabsTrigger value="gallery"> Gallery </TabsTrigger>
                </TabsList>
                <TabsContent value="upload" class="space-y-4">
                    <UploadDropzone @filesSelected="handleFiles" />
                    <div
                        class="mt-4 mb-2 flex justify-end"
                        v-if="hasMultipleTemporaryFiles"
                    >
                        <button
                            @click="uploadAllFiles"
                            class="px-2 py-1 border border-blue-500 text-blue-500 rounded hover:bg-blue-500 hover:text-white flex items-center justify-center"
                            :disabled="isLoading"
                        >
                            <ReloadIcon
                                v-if="isLoading"
                                class="w-4 h-4 mr-2 animate-spin"
                            />
                            <UploadIcon v-else class="w-4 h-4 mr-2" />
                            <span v-if="!isLoading">Upload all</span>
                        </button>
                    </div>
                    <StickyFooterLayout :headerHeight="400">
                        <UploadFileList
                            :files="files"
                            @uploadFile="uploadFile"
                            @removeFile="removeFile"
                        />
                    </StickyFooterLayout>
                </TabsContent>

                <TabsContent value="gallery" class="space-y-4">
                    <StickyFooterLayout :headerHeight="250">
                        <PhotoGallery
                            :images="uploadedFiles"
                            file-path="url"
                            @select="
                                (item) => {
                                    previewUrl = item.url
                                    $emit('select', item.id)
                                    $emit('update:url', item.url)
                                }
                            "
                        />
                    </StickyFooterLayout>
                </TabsContent>
            </Tabs>
        </DialogScrollContent>
    </Dialog>
</template>
