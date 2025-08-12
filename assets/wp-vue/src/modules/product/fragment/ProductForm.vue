<script setup lang="ts">
    import {
        FormControl,
        FormField,
        FormItem,
        FormLabel,
        FormMessage,
    } from '@/components/ui/form'
    import { Switch } from '@/components/ui/switch'
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select'
    import { Input } from '@/components/ui/input'
    import { Button } from '@/components/ui/button'
    import TagsInput from '@/components/TagsInput.vue'
    import { useCategoryStore } from '@/stores/categoryStore'
    import BasicUploader from '@/components/upload/Index.vue'
    import { useRoute } from '@/composable/useVueRouter'
    import { storeToRefs } from 'pinia'
    import { useForm } from 'vee-validate'
    import { productCreateSchema } from '../productSchema'
    import { toTypedSchema } from '@vee-validate/zod'
    import { QuillEditor } from '@/components/Quill'
    import { ref } from 'vue'
    import { set } from 'lodash'
    import { Product } from '@/types'
    import { Bot } from 'lucide-vue-next'
    import UploadTrigger from '@/components/upload/UploadTrigger.vue'
    import PreviewImage from '@/components/upload/PreviewImage.vue'
    const categoryStore = useCategoryStore()
    const { categories } = storeToRefs(categoryStore)
    const form = useForm({
        name: 'productForm',
        validationSchema: toTypedSchema(productCreateSchema),
    })

    const props = defineProps<{
        product?: Product
        isAiConfig?: boolean
        submitForm: (payload: any, actions: any) => Promise<any>
    }>()
    defineEmits<{
        generateDescription: [type: string, form: any]
    }>()
    defineExpose({
        form,
    })

    const handleSubmit = form.handleSubmit(async (payload, actions) => {
        await props.submitForm(payload, actions)
    })

    const showGallery = ref(false)
</script>

<template>
    <form class="p-10 pt-2 space-y-6" @submit.prevent="handleSubmit">
        <FormField
            v-slot="{ componentField }"
            name="name"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Name</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Product name"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <div class="grid grid-cols-2 gap-4">
            <FormField
                v-slot="{ componentField }"
                name="regular_price"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Regular Price</FormLabel>
                    <FormControl>
                        <Input
                            type="number"
                            placeholder="Enter price"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField
                v-slot="{ componentField }"
                name="sale_price"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Sale Price</FormLabel>
                    <FormControl>
                        <Input
                            type="number"
                            placeholder="Enter price"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <FormField
                v-slot="{ componentField }"
                name="stock"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Stock</FormLabel>
                    <FormControl>
                        <Input
                            type="number"
                            placeholder="Enter stock quantity"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField
                v-slot="{ componentField }"
                name="sku"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>SKU</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="SKU"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <FormField
                v-slot="{ value }"
                name="categories"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Categories</FormLabel>
                    <FormControl>
                        <TagsInput
                            :items="categories"
                            :model-value="value"
                            @onSelect="
                                (_value) =>
                                    form.setFieldValue('categories', _value)
                            "
                            itemKey="id"
                            itemValue="name"
                            placeholder="Select categories"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField
                v-slot="{ componentField }"
                name="status"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Status</FormLabel>
                    <FormControl>
                        <Select v-bind="componentField">
                            <SelectTrigger>
                                <SelectValue placeholder="Select status" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="publish">
                                    Published
                                </SelectItem>
                                <SelectItem value="draft">Draft</SelectItem>
                                <SelectItem value="pending">Pending</SelectItem>
                                <SelectItem value="private">Private</SelectItem>
                            </SelectContent>
                        </Select>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField
                v-slot="{ value, handleChange }"
                name="featured"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Featured Product</FormLabel>
                    <div
                        class="flex flex-row items-center justify-between rounded-sm border py-1.5 px-3"
                    >
                        <p class="text-sm font-normal text-gray-400">
                            Product will be featured
                        </p>

                        <FormControl>
                            <Switch
                                :checked="value"
                                @update:checked="handleChange"
                            />
                        </FormControl>
                    </div>
                </FormItem>
            </FormField>
            <FormField
                v-slot="{ value }"
                name="image"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Image</FormLabel>
                    <FormControl>
                        <BasicUploader
                            v-model="showGallery"
                            :url="product?.image_url"
                            @update:url="
                                (url) => set(product, 'image_url', url)
                            "
                            @select="
                                (_value) => {
                                    form.setFieldValue('image', _value)
                                    showGallery = false
                                }
                            "
                            #="{ previewUrl, closePreview }"
                        >
                            <UploadTrigger @click="showGallery = true">
                                <PreviewImage
                                    :src="previewUrl"
                                    @close="closePreview"
                                    v-if="previewUrl"
                                />
                                <span v-else class="font-medium select-none"
                                    >Choose file</span
                                >
                            </UploadTrigger>
                        </BasicUploader>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
        </div>
        <FormField
            v-slot="{ value }"
            name="short_description"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <div class="flex items-end justify-between">
                    <FormLabel>Short Description</FormLabel>
                    <Button
                        type="button"
                        variant="ghost"
                        size="xs"
                        v-if="isAiConfig"
                        @click="$emit('generateDescription', 'short', form)"
                    >
                        <Bot />
                    </Button>
                </div>
                <FormControl>
                    <QuillEditor
                        :content="value"
                        @update:content="
                            (_value) =>
                                form.setFieldValue('short_description', _value)
                        "
                        toolbar="essential"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ value }"
            name="description"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <div class="flex items-end justify-between">
                    <FormLabel>Description</FormLabel>
                    <Button
                        type="button"
                        variant="ghost"
                        size="xs"
                        v-if="isAiConfig"
                        @click="$emit('generateDescription', 'long', form)"
                    >
                        <Bot />
                    </Button>
                </div>
                <FormControl>
                    <QuillEditor
                        :content="value"
                        @update:content="
                            (_value) =>
                                form.setFieldValue('description', _value)
                        "
                        toolbar="essential"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <Button type="submit" :loading="form.isSubmitting.value">
            Submit
        </Button>
    </form>
</template>
