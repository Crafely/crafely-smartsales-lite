<script setup lang="ts">
    import { ref, computed } from 'vue'
    import Select from '@/components/Select.vue'
    import { Input } from '@/components/ui/input'
    import { Button } from '@/components/ui/button'
    import { PlusCircledIcon, MinusCircledIcon } from '@radix-icons/vue'
    import type { PaymentMethod } from '@/types'

    const props = defineProps<{
        paymentMethods: PaymentMethod[]
        handleAddPaymentMethod: (paymentMethod: PaymentMethod) => void
        handleRemovePaymentMethod: (index: number) => void
        orderForm: any
        cartTotal?: number
    }>()

    const errors = ref({
        total_split_amount: '',
    })

    const validateSplitField = computed(() => {
        if (props.orderForm?.split_payments) {
            const invalidPayment = props.orderForm.split_payments.find(
                (payment: any) =>
                    !payment.amount || Number(payment.amount) === 0
            )
            if (invalidPayment) {
                return 'Amount cannot be empty or 0'
            }
        }
        return ''
    })

    const validateSplitPayments = computed(() => {
        const totalAmount = props.orderForm?.split_payments?.reduce(
            (sum: number, payment: any) => {
                return sum + (payment.amount || 0)
            },
            0
        )
        const cartTotal =
            typeof props.cartTotal === 'string'
                ? parseFloat(props.cartTotal.replace(/[^0-9.]/g, ''))
                : props.cartTotal

        if (totalAmount !== cartTotal) {
            errors.value.total_split_amount = `Total split payments must equal cart total (${cartTotal})`
            return false
        } else {
            errors.value.total_split_amount = ''
            return true
        }
    })
</script>

<template>
    <div>
        <div v-if="orderForm.payment_method" class="flex items-center gap-x-2">
            <Select
                v-model="orderForm.payment_method"
                :items="paymentMethods"
            />
            <Button
                @click="handleAddPaymentMethod"
                variant="outline"
                size="icon"
                class="ml-3 shrink-0 shadow-sm opacity-50 :hover:opacity-100"
            >
                <PlusCircledIcon class="h-6 w-6" />
            </Button>
        </div>

        <div v-if="orderForm.split_payments" class="space-y-2">
            <template
                v-for="(payment, index) in orderForm.split_payments"
                :key="index"
            >
                <div class="flex gap-x-1">
                    <Select v-model="payment.method" :items="paymentMethods" />
                    <Input
                        v-model="payment.amount"
                        @keyup="validateSplitPayments"
                        type="number"
                        placeholder="Amount"
                    />
                    <Button
                        @click="handleRemovePaymentMethod(index)"
                        variant="outline"
                        size="icon"
                        class="ml-3 shrink-0 shadow-sm opacity-50 :hover:opacity-100"
                    >
                        <MinusCircledIcon class="h-6 w-6" />
                    </Button>
                    <Button
                        @click="handleAddPaymentMethod"
                        variant="outline"
                        size="icon"
                        class="ml-3 shrink-0 shadow-sm opacity-50 :hover:opacity-100"
                    >
                        <PlusCircledIcon class="h-6 w-6" />
                    </Button>
                </div>
                <div class="text-xs text-red-500">
                    {{ validateSplitField }}
                </div>
                <div class="text-xs text-red-500">
                    {{ errors.total_split_amount }}
                </div>
            </template>
        </div>
    </div>
</template>
