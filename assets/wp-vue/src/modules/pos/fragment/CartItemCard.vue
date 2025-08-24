<script setup lang="ts">
    import { Product } from '@/types'
    import { Cross2Icon } from '@radix-icons/vue'

    import {
        NumberField,
        NumberFieldContent,
        NumberFieldDecrement,
        NumberFieldIncrement,
        NumberFieldInput,
    } from '@/components/ui/number-field'

    import { formatAmount } from '@/utils'
    interface Props {
        item: Product
        increment: (item: Props['item']) => void
        decrement: (item: Props['item']) => void
    }

    defineProps<Props>()
    const emit = defineEmits(['remove'])
</script>

<template>
    <div class="flex items-center justify-between pt-4">
        <div class="flex flex-row items-center space-x-2">
            <div class="rounded-md overflow-hidden mr-4 min-w-14 h-14">
                <img
                    :src="item.image_url || '/products/01.jpeg'"
                    :alt="item.name"
                    class="w-full h-full object-cover object-center"
                />
            </div>

            <div class="space-y-1">
                <h4 class="text-sm font-semibold">
                    {{ item.name }}
                </h4>
                <p v-if="item.sale_price" class="text-xs text-muted-foreground">
                    {{ formatAmount(item.price, item.currency) }}
                </p>
                <p v-else class="text-xs text-muted-foreground">
                    {{ formatAmount(item.price, item.currency) }}
                </p>
            </div>
        </div>
        <div class="ml-auto mr-8 max-w-[110px]">
            <NumberField v-model="item.quantity" :min="1">
                <NumberFieldContent>
                    <NumberFieldDecrement />
                    <NumberFieldInput />
                    <NumberFieldIncrement />
                </NumberFieldContent>
            </NumberField>
        </div>
        <div class="mr-8 text-xs text-muted-foreground">
            {{
                formatAmount(
                    (item.sale_price || item.price || 0) * item.quantity,
                    item.currency
                )
            }}
        </div>
        <button
            @click="emit('remove', item.id)"
            class="p-1 hover:bg-gray-100 rounded-full"
        >
            <Cross2Icon class="h-4 w-4 text-gray-500" />
        </button>
    </div>
</template>
