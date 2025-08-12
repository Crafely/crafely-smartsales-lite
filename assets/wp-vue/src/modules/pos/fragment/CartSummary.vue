<script setup>
    import { round } from 'lodash'
    const props = defineProps({
        cartItemsCount: Number,
        cartTotal: Number,
    })
</script>

<template>
    <div class="space-y-2">
        <div v-if="cartTotal" class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Original Price:</span>
            <span class="font-medium">{{ cartTotal }}</span>
        </div>
        <div class="flex justify-between items-center text-sm">
            <span class="text-gray-600">Discount Price:</span>
            <span class="font-medium text-green-600"
                >${{ round(cartTotal * 0.9 || 0, 2) }}</span
            >
        </div>
        <div class="flex justify-between items-center text-sm pt-2">
            <span class="text-gray-600">You Save:</span>
            <span class="font-medium text-green-600"
                >${{ round(cartTotal * 0.1 || 0, 2) }}</span
            >
        </div>
    </div>
</template>
