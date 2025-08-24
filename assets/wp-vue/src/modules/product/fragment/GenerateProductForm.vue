<script setup lang="ts">
    import { ref } from 'vue'
    import * as z from 'zod'
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import { SquareSlash, Send, LoaderCircle, Link } from 'lucide-vue-next'

    import { FormControl, FormField, FormItem } from '@/components/ui/form'
    import { Textarea } from '@/components/ui/textarea/'

    import SuggestPrompt from '@/components/SuggestPrompt.vue'

    const shortCut = ref(false)

    const schema = z.object({
        prompt: z.string(),
    })

    const form = useForm({
        name: 'generateProduct',
        validationSchema: toTypedSchema(schema),
    })

    const props = defineProps<{
        suggestPrompts: { id: number; title: string; content: string }[]
        submitForm: (payload: any, actions: any) => Promise<any>
    }>()

    const handleSubmit = form.handleSubmit(async (payload, actions) => {
        await props.submitForm(payload, actions)
    })

    defineExpose({
        form,
    })
</script>

<template>
    <form class="px-10 py-4 space-y-2" @submit.prevent="handleSubmit">
        <div class="p-2 h-auto border rounded-md shadow-sm">
            <FormField
                v-slot="{ componentField }"
                name="prompt"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormControl>
                        <Textarea
                            class="p-0 min-h-0 rounded-none border-none focus-visible:ring-0 shadow-none resize-none !overflow-hidden"
                            type="text"
                            placeholder="Generate product by AI"
                            v-bind="componentField"
                        />
                    </FormControl>
                </FormItem>
            </FormField>

            <div class="flex justify-between gap-1 mt-1">
                <div class="flex justify-start gap-1">
                    <span
                        class="flex justify-center items-center text-xs gap-1 cursor-pointer hover:opacity-50"
                    >
                        <Link class="w-4 h-4"></Link> Attach
                    </span>
                </div>

                <div class="flex justify-end gap-1">
                    <span
                        class="flex justify-center items-center text-xs gap-1 cursor-pointer hover:opacity-50"
                    >
                        <button
                            type="submit"
                            :disabled="!form.values.prompt?.length"
                            class="disabled:opacity-50"
                        >
                            <Send
                                class="w-4 h-4"
                                v-if="!form.isSubmitting.value"
                            ></Send>
                        </button>

                        <LoaderCircle
                            class="w-4 h-4 animate-spin"
                            v-if="form.isSubmitting.value"
                        ></LoaderCircle>
                    </span>
                </div>
            </div>
        </div>

        <!-- ShortCut box -->
        <div class="flex justify-end">
            <span
                class="flex justify-center items-center text-xs gap-1 cursor-pointer opacity-50 hover:opacity-80"
                :class="shortCut ? '!opacity-100' : 'opacity-50'"
                @click="shortCut = !shortCut"
            >
                <SquareSlash class="w-4 h-4"></SquareSlash> ShortCut
            </span>
        </div>
        <SuggestPrompt
            v-if="shortCut"
            :prompts="suggestPrompts"
            size="sm"
            @select="form.setFieldValue('prompt', $event.content)"
        ></SuggestPrompt>
    </form>
</template>
