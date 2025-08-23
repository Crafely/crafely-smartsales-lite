<script setup lang="ts">
    import {
        Card,
        CardContent,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card'
    import {
        Popover,
        PopoverContent,
        PopoverTrigger,
    } from '@/components/ui/popover'
    import { ref, onMounted, watchEffect, nextTick } from 'vue'
    import { Input } from '@/components/ui/input/'
    import { Textarea } from '@/components/ui/textarea/'
    import PromptInput from '@/components/PromptInput.vue'
    import { Button } from '@/components/ui/button'
    import { ScrollArea } from '@/components/ui/scroll-area'
    import SuggestPrompt from '@/components/SuggestPrompt.vue'
    import Markdown from '@/components/Markdown.vue'
    import { ReloadIcon } from '@radix-icons/vue'
    import { useChat } from './useChat'
    import { ListBulletIcon, PlusIcon, Cross2Icon } from '@radix-icons/vue'
    import { useThreadStore } from '@/stores/threadStore'
    import { ConfirmDialog, useConfirmDialog } from '@/components/confirmDialog'
    import { storeToRefs } from 'pinia'
    import { useRoute } from 'vue-router'
    import { Bot, Settings } from 'lucide-vue-next'
    import { useThread } from '@/composable/useThread'
    const threadStore = useThreadStore()
    const { threads, activeThread } = storeToRefs(threadStore)
    const route = useRoute()
    const {
        initializeThread,
        createThread,
        deleteThread,
        switchThread,
        updateThread,
    } = useThread()
    const { form, messages, suggestPrompts, handleSendMessage, loadMessages } =
        useChat()
    const { deleting, showConfirmDialog, handleDelete, confirmDelete } =
        useConfirmDialog()
    const isGenerating = ref(false)

    const endOfMessages = ref<HTMLElement | null>(null)

    const scrollToBottom = () => {
        endOfMessages.value?.scrollIntoView({ behavior: 'smooth' })
    }

    watchEffect(() => {
        if (messages.value.length > 0) {
            nextTick(() => {
                scrollToBottom()
            })
        }
    })

    const handleSwitchThread = async (threadId) => {
        switchThread(threadId)
        await loadMessages(threadId)
    }

    onMounted(async () => {
        await initializeThread()
        if (activeThread.value) {
            await loadMessages(activeThread.value.id)
        }
        scrollToBottom()
    })
</script>

<template>
    <Card class="overflow-hidden rounded-none shadow-none border-none">
        <CardHeader class="flex flex-row items-start gap-x-2 bg-muted/50">
            <CardTitle class="flex-grow">
                <input
                    v-if="activeThread"
                    type="text"
                    v-model="activeThread.name"
                    class="bg-transparent focus:outline-none focus:ring-0 focus:border-transparent text-lg font-semibold min-w-full hover:ring-1"
                    @input="updateThread"
                />
            </CardTitle>
            <div class="ml-auto flex items-center gap-2">
                <Button size="sm" variant="outline" @click="createThread">
                    <!-- <ReloadIcon class="animate-spin" /> -->
                    <PlusIcon /> Create
                </Button>

                <Popover>
                    <PopoverTrigger as-child>
                        <Button size="sm" variant="outline">
                            <ListBulletIcon /> List
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-80 mr-5 p-0">
                        <div class="max-h-[520px] overflow-auto">
                            <div
                                v-for="thread in threads"
                                :key="thread.id"
                                class="flex relative items-center gap-2 mb-2 cursor-pointer hover:bg-slate-100 p-2"
                                @click="handleSwitchThread(thread.id)"
                                :class="[
                                    thread.id === activeThread?.id
                                        ? 'bg-slate-100'
                                        : 'bg-white dark:bg-gray-800',
                                ]"
                            >
                                <Bot class="flex-shrink-0 w-5 h-5" />
                                <div class="space-y-1">
                                    <p
                                        class="text-sm font-normal leading-none line-clamp-2"
                                    >
                                        {{ thread.name }}
                                    </p>
                                </div>
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    @click.stop="handleDelete(thread.id)"
                                    class="ml-auto h-5 w-5 flex-shrink-0 rounded-full hover:text-red-500 hover:bg-red-100"
                                >
                                    <Cross2Icon />
                                </Button>
                            </div>
                        </div>
                    </PopoverContent>
                </Popover>
                <Button size="sm" variant="outline" @click="$emit('onCancel')">
                    <Settings /> Config
                </Button>
            </div>
        </CardHeader>
        <CardContent>
            <ScrollArea class="flex flex-col h-[calc(100vh-340px)]">
                <div
                    v-if="messages.length"
                    class="flex-grow overflow-y-auto space-y-4 my-4"
                >
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        :class="[
                            'flex w-max max-w-[90%] flex-col gap-2 border shadow rounded-lg px-3 py-2 text-sm overflow-x-auto',
                            message.role === 'user' ? 'ml-auto bg-accent' : '',
                        ]"
                    >
                        <Markdown :content="message.content" />
                    </div>
                </div>

                <div v-else>
                    <SuggestPrompt
                        class="py-4 justify-center"
                        :prompts="suggestPrompts"
                        size="sm"
                        @select="form.prompt = $event.content"
                    >
                    </SuggestPrompt>
                </div>
                <div ref="endOfMessages"></div>
            </ScrollArea>
            <div
                class="sticky bottom-0 flex justify-between items-center gap-2 py-2 bg-white dark:bg-gray-800 border-t"
            >
                <PromptInput
                    v-model="form.prompt"
                    :loading="form.loading"
                    @submit="handleSendMessage()"
                ></PromptInput>
            </div>
        </CardContent>
    </Card>
    <ConfirmDialog
        title="Are you sure you want to delete this Assistant?"
        description="This action will move the product to the trash and can be restored within 30 days."
        v-model="showConfirmDialog"
        :deleting="deleting"
        @confirm="confirmDelete(deleteThread)"
    />
</template>
