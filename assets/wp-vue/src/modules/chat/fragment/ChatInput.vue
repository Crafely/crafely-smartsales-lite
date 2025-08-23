<script setup lang="ts">
    import { Button } from '@/components/ui'
    import { Textarea } from '@/components/ui'
    import { SendHorizontal } from 'lucide-vue-next'

    const props = defineProps<{
        prompt: string
        isLoading?: boolean
    }>()

    const emit = defineEmits<{
        (e: 'update:prompt', value: string): void
        (e: 'send'): void
    }>()

    const handleInput = (e: Event) => {
        const target = e.target as HTMLTextAreaElement
        emit('update:prompt', target.value)
    }

    const handleSubmit = () => {
        if (!props.prompt.trim()) return
        emit('send')
    }
</script>

<template>
    <div class="bg-white dark:bg-gray-800 mt-4 relative">
        <div class="max-w-3xl mx-auto">
            <form @submit.prevent="handleSubmit" class="flex space-x-2">
                <Textarea
                    :value="prompt"
                    @input="handleInput"
                    placeholder="Type your message..."
                    class="flex-grow min-h-[50px] max-h-[200px] resize-y pr-12"
                    :disabled="isLoading"
                />
                <Button
                    type="submit"
                    :disabled="!prompt.trim() || isLoading"
                    class="h-[50px] w-[50px] p-0 flex items-center justify-center"
                    size="icon"
                >
                    <SendHorizontal
                        :class="[isLoading ? 'animate-pulse' : '']"
                        class="h-5 w-5"
                    />
                </Button>
            </form>
        </div>
    </div>
</template>
