<script setup lang="ts">
    import { Input } from '@/components/ui/input'
    import { Button } from '@/components/ui/button'
    import { Trash2 } from 'lucide-vue-next'
    import AddOrderProduct from './AddOrderProduct.vue'
    import concat from 'lodash/concat'
    import remove from 'lodash/remove'

    const props = defineProps<{
        form: any
    }>()

    const emit = defineEmits(['update:modelValue'])

    const addProduct = (product) => {
        props.form.setFieldValue(
            'line_items',
            concat(props.form.values.line_items, product)
        )
    }

    const removeItem = (index: number) => {
        const items = [...props.form.values.line_items]
        remove(items, (_, i) => i === index)
        props.form.setFieldValue('line_items', items)
    }
</script>

<template>
    <div class="space-y-4">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-xl font-semibold">Order Items</h2>
            <div class="flex-1">
                <div class="flex justify-end">
                    <AddOrderProduct @addProduct="addProduct" />
                </div>
            </div>
        </div>

        <div class="rounded-md border">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b">
                        <th class="p-2 text-left">Product</th>
                        <th class="p-2 text-left">Price</th>
                        <th class="p-2 text-left w-[80px]">Quantity</th>
                        <th class="p-2 text-left">Total</th>
                        <th class="p-2"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="(item, index) in form.values.line_items"
                        :key="index"
                        class="border-b"
                    >
                        <td class="p-2 line-clamp-2">
                            {{ item.name }}
                        </td>
                        <td class="p-2">
                            {{ item.price }}
                        </td>
                        <td class="p-2">
                            <Input type="number" :model-value="item.quantity" />
                        </td>
                        <td class="p-2">
                            {{ item.total }}
                        </td>
                        <td class="p-2">
                            <Button
                                variant="ghost"
                                size="icon"
                                @click="removeItem(index)"
                            >
                                <Trash2 class="h-4 w-4" />
                            </Button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</template>
