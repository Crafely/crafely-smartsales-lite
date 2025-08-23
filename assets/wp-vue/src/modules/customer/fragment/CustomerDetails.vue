<script lang="ts" setup>
    import {
        Card,
        CardTitle,
        CardHeader,
        CardDescription,
        CardContent,
    } from '@/components/ui/card'
    import {
        Accordion,
        AccordionContent,
        AccordionItem,
        AccordionTrigger,
    } from '@/components/ui/accordion'
    import CustomerAddress from '@/molecules/customer/CustomerAddress.vue'
    import { Trash2 } from 'lucide-vue-next'
    import { Button } from '@/components/ui'
    import type { Customer } from '@/types'
    import CustomerOrders from './CustomerOrders.vue'
    import { ref, watch } from 'vue'
    const props = defineProps<{
        customer: Customer
        handleDelete: (customer: Customer) => void
    }>()
    const activeAccordion = ref('shipping')

    watch(
        () => props.customer,
        () => {
            activeAccordion.value = props.customer?.orders.length
                ? 'orders'
                : 'billing'
        },
        { immediate: true }
    )
</script>

<template>
    <Card class="overflow-hidden rounded-none shadow-none border-none">
        <CardHeader class="flex flex-row justify-between bg-muted/50">
            <div class="rounded-md overflow-hidden border-2 mr-4 w-16 h-16">
                <img
                    :src="customer?.profile_image || ''"
                    class="w-full h-full object-cover object-center"
                    alt=""
                />
            </div>
            <div class="flex-1">
                <CardTitle class="text-lg">
                    {{ customer?.full_name || '' }}
                </CardTitle>
                <div class="flex items-center gap-2">
                    <div>
                        <CardDescription>
                            Email:
                            {{ customer?.email || '' }}
                        </CardDescription>
                        <CardDescription>
                            Phone:
                            {{ customer?.phone || '' }}
                        </CardDescription>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <Button
                            size="sm"
                            variant="outline"
                            class="hover:text-red-400"
                            @click="handleDelete(customer)"
                        >
                            <Trash2 class="w-4 h-4" /> Delete
                        </Button>
                    </div>
                </div>
            </div>
        </CardHeader>

        <CardContent class="p-6 text-sm">
            <Accordion type="single" collapsible v-model="activeAccordion">
                <AccordionItem v-if="customer?.orders?.length" value="orders">
                    <AccordionTrigger>Order List</AccordionTrigger>
                    <AccordionContent>
                        <CustomerOrders :orders="customer.orders" />
                    </AccordionContent>
                </AccordionItem>

                <AccordionItem value="billing">
                    <AccordionTrigger>Billing Address</AccordionTrigger>
                    <AccordionContent>
                        <CustomerAddress :address="customer?.billing" />
                    </AccordionContent>
                </AccordionItem>

                <AccordionItem value="shipping">
                    <AccordionTrigger>Shipping Address</AccordionTrigger>
                    <AccordionContent>
                        <CustomerAddress :address="customer?.shipping" />
                    </AccordionContent>
                </AccordionItem>
            </Accordion>
        </CardContent>
    </Card>
</template>
