import { inject, onMounted, ref } from 'vue'
import { useAxios } from '@/composable/useAxios'
import { toast } from 'vue-sonner'
export interface FileWithPreview {
    preview?: string
    id?: string
    name: string
    size: number
    state: 'temporary' | 'uploading' | 'uploaded' | 'failed'
    file: File
}

export const useFileUpload = () => {
    const files = ref<FileWithPreview[]>([])
    const uploadedFiles = ref<{ id: number; type: string; url: string }[]>([])
    const { get, post, remove } = useAxios()

    const handleFiles = async (newFiles: File[]) => {
        for (const file of newFiles) {
            files.value.push({
                preview: URL.createObjectURL(file),
                name: file.name,
                size: file.size,
                state: 'temporary',
                file: file,
            })
        }
    }

    const uploadFile = async (file: FileWithPreview) => {
        file.state = 'uploading'
        const formData = new FormData()
        formData.append('file', file.file)
        try {
            const { success, data, message } = await post('media', formData)
            file.state = 'uploaded'
            file.id = data.id
            toast[success ? 'success' : 'error'](message)
        } catch (error) {
            console.error('Upload failed:', error)
            file.state = 'failed'
        }
    }

    const getFiles = async () => {
        try {
            const { data } = await get('media')
            uploadedFiles.value = data
        } catch (error) {
            console.error('Failed to fetch files:', error)
        }
    }

    const removeFile = async (file: FileWithPreview) => {
        try {
            if (file.id) {
                await remove(`/api/files/${file.id}`)
            }
            const index = files.value.indexOf(file)
            if (index > -1) {
                if (file.preview) URL.revokeObjectURL(file.preview)
                files.value.splice(index, 1)
            }
        } catch (error) {
            console.error('File deletion failed:', error)
        }
    }

    onMounted(() => {
        getFiles()
    })

    return {
        files,
        uploadedFiles,
        handleFiles,
        uploadFile,
        removeFile,
    }
}
