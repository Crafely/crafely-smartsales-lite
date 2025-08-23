<script setup lang="ts">
    import { watch } from 'vue'
    import { useTextareaAutosize } from '@vueuse/core'
    import { Button } from '@/components/ui/button'
    import {
        SquareSlash,
        Loader,
        Link,
        ChevronDown,
        ArrowUp,
    } from 'lucide-vue-next'

    const { textarea, input } = useTextareaAutosize({ styleProp: 'minHeight' })

    const props = defineProps<{
        modelValue: string
        loading?: boolean
        rows?: number | 2
    }>()

    const emit = defineEmits(['update:modelValue', 'submit'])

    const handleSendPrompt = () => {
        emit('update:modelValue', input.value)
        emit('submit', input.value)
    }

    watch(
        () => props.modelValue,
        (newValue) => {
            input.value = newValue
        }
    )
    watch(
        () => input.value,
        (newValue) => {
            emit('update:modelValue', newValue)
        }
    )
</script>
<template>
    <div class="w-full h-auto border rounded-md shadow-sm bg-white">
        <div class="p-2 pb-0 relative">
            <textarea
                ref="textarea"
                v-model="input"
                class="text-sm resize-none w-full pr-9 border-nonne focus-visible:ring-0 focus-visible:outline-none shadow-none overflow-hidden"
                placeholder="What's on your mind?"
                :rows="props.rows"
                @keydown.enter.exact.prevent="handleSendPrompt"
                @keydown.shift.enter.exact.stop
            />

            <div class="absolute right-2 bottom-1">
                <Button
                    variant="secondary"
                    class="rounded-full w-7 h-7 px-2 py-2 hover:opacity-80"
                    type="submit"
                    v-if="modelValue.length > 0 ? true : false"
                    @click="handleSendPrompt"
                >
                    <Loader
                        v-if="loading"
                        class="w-4 h-4 animate-spin"
                    ></Loader>
                    <ArrowUp v-else class="w-4 h-4"></ArrowUp>
                </Button>
            </div>
        </div>

        <div class="p-2 py-1.5 border-t flex justify-between gap-1 bg-accent">
            <div class="flex justify-start gap-2">
                <span
                    class="flex justify-center items-center text-xs gap-1 cursor-pointer opacity-50 hover:opacity-80"
                >
                    Ai Model.3<ChevronDown class="w-4 h-4"></ChevronDown>
                </span>
            </div>

            <div class="flex justify-end gap-2">
                <span
                    class="flex justify-center items-center text-xs gap-1 cursor-pointer opacity-50 hover:opacity-80"
                >
                    <SquareSlash class="w-4 h-4"></SquareSlash> ShortCut
                </span>
                <span
                    class="flex justify-center items-center text-xs gap-1 cursor-pointer opacity-50 hover:opacity-80"
                >
                    <Link class="w-4 h-4"></Link> Attach
                </span>
            </div>
        </div>
    </div>
</template>
