<script setup lang="ts">
    import { ref } from 'vue'
    import CartItemCard from './CartItemCard.vue'
    import CashSelector from './CashSelector.vue'
    import StickyFooterLayout from '@/layout/StickyFooterLayout.vue'
    import { ShoppingCart, Loader2 } from 'lucide-vue-next'
    import CustomerForm from './CustomerForm.vue'
    import CustomerCard from './CustomerCard.vue'
    import Combobox from '@/components/ComboBox.vue'
    import { PlusCircledIcon } from '@radix-icons/vue'
    import { Button } from '@/components/ui/button'
    import { Separator } from '@/components/ui/separator'

    import type { Customer, Product } from '@/types'
    import { useSalesStore } from '@/stores/salesStore'
    import { useSales } from '../useSales'
    import { ScrollArea } from '@/components/ui/scroll-area'
    import { validate } from 'vee-validate'

    const salesStore = useSalesStore()
    const {
        addToCart,
        decreaseQuantity,
        removeFromCart,
        loading,
        paymentMethods,
    } = salesStore

    const {
        activeCustomer,
        setActiveCustomer,
        showCustomerForm,
        orderForm,
        submitOrder,
        handleAddPaymentMethod,
        handleRemovePaymentMethod,
        cartTotal,
        clearCartHandler,
        createCustomer,
        syncPending,
        totalPending,
        handlePending,
    } = useSales()

    defineProps<{
        customers: Customer[]
        items: Product[]
        id: number
    }>()

    const errors = ref({
        customer: '',
    })

    const validateForm = () => {
        let isValid = true
        errors.value = {
            customer: '',
        }

        if (!orderForm.value.customer_id) {
            errors.value.customer = 'Customer is required'
            isValid = false
        }

        return isValid
    }

    const handleSubmitOrder = async () => {
        if (!validateForm()) return
        try {
            await submitOrder()
        } catch (error) {
            console.error('Failed to create customer:', error)
        }
    }
</script>
<template>
    <StickyFooterLayout class="p-8 pt-2">
        <template #header>
            <div class="space-y-4 mb-4">
                <div>
                    <div class="w-full flex items-center">
                        <Combobox
                            v-model="orderForm.customer_id"
                            placeholder="Select Customer"
                            :items="customers"
                            item-label="full_name"
                            @onSelect="setActiveCustomer"
                        />

                        <Button
                            variant="outline"
                            size="icon"
                            class="ml-3 shrink-0 shadow-sm opacity-50 :hover:opacity-100"
                            @click="showCustomerForm = !showCustomerForm"
                        >
                            <PlusCircledIcon
                                :class="showCustomerForm && 'rotate-45'"
                                class="h-6 w-6"
                            />
                        </Button>
                    </div>
                    <div class="text-sm text-red-500" v-if="errors.customer">
                        {{ errors.customer }}
                    </div>
                </div>

                <div v-if="showCustomerForm">
                    <CustomerForm
                        :submitForm="createCustomer"
                        name="createForm"
                        ref="customerCreateFormRef"
                    />
                </div>
                <div v-if="activeCustomer">
                    <CustomerCard :customer="activeCustomer" />
                </div>
                <ScrollArea>
                    <CashSelector
                        class="max-h-36 p-0.5"
                        :paymentMethods="paymentMethods"
                        :handleAddPaymentMethod="handleAddPaymentMethod"
                        :handleRemovePaymentMethod="handleRemovePaymentMethod"
                        :orderForm="orderForm"
                        :cartTotal="cartTotal"
                    />
                </ScrollArea>
            </div>
        </template>
        <div class="space-y-4 divide-y divide-gray-200">
            <CartItemCard
                v-for="item in items"
                :key="item.id"
                :item="item"
                :increment="addToCart"
                :decrement="decreaseQuantity"
                @remove="removeFromCart"
            />
        </div>
        <template #footer>
            <div
                class="text-right pt-5 flex flex-row-reverse gap-1 justify-between items-center"
            >
                <Button
                    class="max-w-[430px] flex-inline items-center justify-center space-x-2"
                    :disabled="!items.length || loading"
                    @click="handleSubmitOrder"
                >
                    <Loader2 v-if="loading" class="h-5 w-5 animate-spin" />
                    <ShoppingCart v-else class="h-5 w-5" />
                    <span>Place Order</span>
                    <Separator v-if="cartTotal" orientation="vertical" />
                    <span>{{ cartTotal }}</span>
                </Button>

                <Button
                    variant="outline"
                    v-if="syncPending"
                    @click="handlePending"
                    >Sync ({{ totalPending }})</Button
                >
                <Button
                    variant="outline"
                    v-if="items.length"
                    @click="clearCartHandler(id)"
                    >Clear Cart</Button
                >
            </div>
        </template>
    </StickyFooterLayout>
</template>
