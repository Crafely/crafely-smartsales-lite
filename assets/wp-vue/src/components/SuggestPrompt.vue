<script setup lang="ts">
    import { Button } from '@/components/ui/button'
    const props = defineProps<{
        prompts: { id: number; title: string; content: string; icon?: object }[]
        size?: string
        icon?: object
    }>()

    const emit = defineEmits<{
        (
            e: 'select',
            prompt: { id: number; title: string; content: string }
        ): void
    }>()

    const handlePrompt = (prompt: {
        id: number
        title: string
        content: string
    }) => {
        emit('select', prompt)
    }
</script>

<template>
    <div class="flex flex-wrap gap-2">
        <Button
            v-for="prompt in prompts"
            :key="prompt.id"
            @click="handlePrompt(prompt)"
            type="button"
            class="w-fit rounded-full"
            variant="outline"
            :icon="icon"
            :size="size"
        >
            <slot />{{ prompt.title }}</Button
        >
    </div>
</template>
