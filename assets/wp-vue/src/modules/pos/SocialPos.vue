<script lang="ts" setup>
import { computed } from 'vue'
import AppLayout from '@/packages/pos/layout/AppLayout.vue'
import { useSales } from './useSales'
import CartItemCard from './fragment/CartItemCard.vue'
import { useSalesStore } from '@/stores/salesStore'
import { storeToRefs } from 'pinia'
// import { useUserStore } from '@/stores/userStore'
import { useAppStore } from '@/stores/appStore'
//ui
import CashSelector from './fragment/CashSelector.vue'
import StickyFooterLayout from '@/layout/StickyFooterLayout.vue'
import { ShoppingCart } from 'lucide-vue-next'
import CustomerForm from './fragment/CustomerForm.vue'
import CustomerCard from './fragment/CustomerCard.vue'
import { ProductShowcase } from './fragment/productShowcase'
import Combobox from '@/components/ComboBox.vue'
import { PlusCircledIcon } from '@radix-icons/vue'
import { Button } from '@/components/ui/button'
import { Separator } from '@/components/ui/separator'
import { Loader2 } from 'lucide-vue-next'
import {
    ResizableHandle,
    ResizablePanel,
    ResizablePanelGroup,
} from '@/components/ui/resizable'

defineOptions({
    name: 'POSPanel'
})
document.title = 'Point of Sale'
const store = useSalesStore()
const { items } = storeToRefs(store)
const {
    customers,
    salesProducts,
    showCustomerForm,
    activeCustomer,
    setActiveCustomer,
    createCustomer,
} = useSales()

// const userStore = useUserStore()
// const { authUser } = storeToRefs(userStore)

const appStore = useAppStore()
const { inventorySize } = storeToRefs(appStore)

const businessTypeComponent = computed(() => {
    switch (inventorySize.value) {
        case 'small':
            return 'basic'
        case 'medium':
            return 'standard'
        case 'large':
            return 'enterprise'
        default:
            return 'basic'
    }
})

</script>

<template>
    <AppLayout activeRouteName="app.sales" :sidebar-width="40">
        <ResizablePanelGroup direction="horizontal">
            <ResizablePanel>
                <div class="p-8">
                    <component 
                        :is="ProductShowcase[businessTypeComponent || 'basic']" 
                        :products="salesProducts" 
                    />
                </div>
            </ResizablePanel>
            <ResizableHandle with-handle />
            <ResizablePanel :default-size="30">
                <StickyFooterLayout class="p-8">
                    <template #header>
                        <div class="space-y-4 mb-4">
                            <div class="w-full flex items-center">
                                <Combobox
                                    v-model="store.orderForm.customer_id"
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
                            <div v-if="showCustomerForm">
                                <CustomerForm :createCustomer="createCustomer" />
                            </div>
                            <div v-if="activeCustomer">
                                <CustomerCard :customer="activeCustomer" />
                            </div>
                            <CashSelector />
                        </div>
                    </template>
                    <div class="space-y-4 divide-y divide-gray-200">
                        <CartItemCard
                            v-for="item in items"
                            :key="item.id"
                            :item="item"
                            :increment="store.addToCart"
                            :decrement="store.decreaseQuantity"
                            @remove="store.removeFromCart"
                        />
                    </div>
                <template #footer>
                        <div class="text-right pt-5">
                            <Button
                                class="max-w-[430px] flex-inline items-center justify-center space-x-2"
                                :disabled="!items.length || store.loading"
                                @click="store.submitOrder"
                            >
                                <Loader2
                                    v-if="store.loading"
                                    class="h-5 w-5 animate-spin"
                                />
                                <ShoppingCart v-else class="h-5 w-5" />
                                <span>Place Order</span>
                                <Separator
                                    v-if="store.cartTotal"
                                    orientation="vertical"
                                />
                                <span>{{ store.cartTotal }}</span>
                            </Button>
                        </div>
                </template>
                </StickyFooterLayout>
            </ResizablePanel>
        </ResizablePanelGroup>
    </AppLayout>
</template>
