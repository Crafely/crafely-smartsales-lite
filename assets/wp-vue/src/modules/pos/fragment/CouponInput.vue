<script setup>
    import { ref } from 'vue'
    import { Ticket } from 'lucide-vue-next'

    const couponCode = ref('')
    const emit = defineEmits(['apply'])

    const handleApply = () => {
        emit('apply', couponCode.value)
    }
</script>

<template>
    <div class="relative">
        <input
            type="text"
            v-model="couponCode"
            placeholder="Enter coupon code"
            class="w-full pl-10 pr-4 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
        />
        <Ticket class="absolute left-3 top-2.5 h-4 w-4 text-gray-400" />
        <button
            @click="handleApply"
            class="absolute right-2 top-1.5 px-3 py-1 text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 rounded transition-colors duration-200"
        >
            Apply
        </button>
    </div>
</template>
