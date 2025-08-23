<script lang="ts" setup>
    import { onMounted } from 'vue'
    import { computed } from 'vue'
    import AppLayout from '@/layout/AppLayoutWithResize.vue'
    import { useSales } from './useSales'
    import SingleCart from './fragment/SingleCart.vue'
    import { useSalesStore } from '@/stores/salesStore'
    import { storeToRefs } from 'pinia'
    import { useAppStore } from '@/stores/appStore'
    //ui
    import { ProductShowcase } from './fragment/productShowcase'
    import CartListing from './fragment/CartListing.vue'
    import { Button } from '@/components/ui/button'
    import { X, Plus } from 'lucide-vue-next'
    import { ScrollArea, ScrollBar } from '@/components/ui/scroll-area'

    defineOptions({
        name: 'POSPanel',
    })
    document.title = 'Point of Sale'

    onMounted(() => {
        if (carts.value.length === 0) {
            addNewCart()
        }
    })
    const salesStore = useSalesStore()
    const { addNewCart, deleteCart } = salesStore
    const { carts, activeCartId } = storeToRefs(salesStore)

    const { salesProducts, customers, createCustomer } = useSales()

    const appStore = useAppStore()
    const { inventorySize } = storeToRefs(appStore)
    // @feedback: move this computed to app store
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
    <AppLayout activeRouteName="app.pos">
        <div class="p-8">
            <component
                :is="ProductShowcase[businessTypeComponent || 'basic']"
                :products="salesProducts"
            />
        </div>

        <template #sidebar>
            <ScrollArea>
                <div
                    class="flex justify-start items-center gap-1 pt-1 px-1 text-sm bg-accent"
                >
                    <CartListing
                        :carts="carts"
                        :activeCartId="activeCartId"
                        class="flex gap-1 divide-x-2"
                    >
                        <template #default="{ isActive, cartNumber, id }">
                            <div
                                class="flex items-center gap-1 px-1.5 py-1 mb-1 hover:rounded-lg hover:bg-background hover:cursor-default"
                                :class="{
                                    'px-4': carts.length === 1,
                                    'bg-background hover:bg-accent rounded-t-lg hover:rounded-b-none relative before:absolute before:bg-background before:content-[\'\'] before:w-full before:h-1 before:bg-sudu before:left-0 before:-bottom-1':
                                        isActive,
                                }"
                            >
                                <div
                                    class="text-nowrap"
                                    @click="activeCartId = id"
                                >
                                    Cart {{ cartNumber }}
                                </div>
                                <div
                                    class="p-1 rounded-full hover:cursor-pointer hover:bg-accent"
                                    v-if="carts.length > 1"
                                    @click="deleteCart(id)"
                                >
                                    <X class="w-3 h-3"></X>
                                </div>
                            </div>
                        </template>
                    </CartListing>
                    <div
                        class="p-1 mb-1 rounded-full hover:cursor-pointer hover:bg-background"
                        @click="addNewCart"
                    >
                        <Plus class="w-4 h-4"></Plus>
                    </div>
                </div>
                <ScrollBar class="!h-2" orientation="horizontal" />
            </ScrollArea>

            <CartListing :carts="carts" :activeCartId="activeCartId">
                <template #default="{ isActive, index, cart, id, cartNumber }">
                    <SingleCart
                        v-show="isActive"
                        :customers="customers"
                        :createCustomer="createCustomer"
                        :items="cart.items"
                        :id="id"
                    />
                </template>
            </CartListing>
        </template>
    </AppLayout>
</template>
