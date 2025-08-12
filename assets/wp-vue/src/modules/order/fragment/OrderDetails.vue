<script setup lang="ts">
    import { Button } from '@/components/ui/button'
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card'
    import {
        Accordion,
        AccordionContent,
        AccordionItem,
        AccordionTrigger,
    } from '@/components/ui/accordion'
    import { useAppStore } from '@/stores/appStore'
    import { storeToRefs } from 'pinia'
    import { Separator } from '@/components/ui/separator'
    import CustomerAddress from '@/molecules/customer/CustomerAddress.vue'
    import { Order } from '@/types'
    import { Copy, Trash2, CreditCard, Printer } from 'lucide-vue-next'
    import { formatAmount, formatDateTime } from '@/utils'
    const { currency } = storeToRefs(useAppStore())

    defineProps<{
        activeOrder: Order
        handleDelete: (order: Order) => void
    }>()
</script>

<template>
    <div>
        <Card
            class="order-card overflow-hidden rounded-none shadow-none border-none"
        >
            <CardHeader class="flex flex-row items-start bg-muted/50">
                <div class="grid gap-0.5">
                    <CardTitle class="group flex items-center gap-2 text-lg">
                        Order ID: #{{ activeOrder?.id || '' }}
                    </CardTitle>
                    <CardDescription>
                        Date: {{ formatDateTime(activeOrder?.created_at) }}
                    </CardDescription>
                </div>
                <div class="ml-auto flex items-center gap-2 no-print">
                    <Button
                        size="sm"
                        variant="outline"
                        class="hover:text-red-400"
                        @click="handleDelete(activeOrder)"
                    >
                        <Trash2 class="w-4 h-4" /> Delete
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="p-6 text-sm">
                <div class="grid gap-3">
                    <div class="font-semibold">Order Details</div>
                    <ul class="grid gap-3">
                        <li
                            v-for="item in Object.values(
                                activeOrder?.line_items || {}
                            )"
                            :key="item.product_id"
                            class="flex items-center justify-between"
                        >
                            <span class="text-muted-foreground">
                                {{ item.name }} x
                                <span>{{ item.quantity }}</span>
                            </span>
                            <span>{{
                                formatAmount(item.total, currency)
                            }}</span>
                        </li>
                    </ul>
                    <Separator class="my-2" />
                    <ul class="grid gap-3">
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Subtotal</span>
                            <span>{{
                                formatAmount(activeOrder?.total, currency)
                            }}</span>
                        </li>
                        <li class="flex items-center justify-between">
                            <span class="text-muted-foreground">Discount</span>
                            <span>{{
                                formatAmount(
                                    activeOrder?.discount_total,
                                    currency
                                )
                            }}</span>
                        </li>
                        <li
                            class="flex items-center justify-between font-semibold"
                        >
                            <span class="text-muted-foreground">Total</span>
                            <span>{{
                                formatAmount(activeOrder?.total, currency)
                            }}</span>
                        </li>
                    </ul>
                </div>

                <Separator class="my-4" />
                <div class="grid gap-3">
                    <div class="font-semibold">Customer Information</div>
                    <dl class="grid gap-3">
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Customer</dt>
                            <dd>{{ activeOrder?.customer?.full_name }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Email</dt>
                            <dd>
                                <a
                                    :href="`mailto:${activeOrder?.customer?.email}`"
                                    >{{ activeOrder?.customer?.email }}</a
                                >
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt class="text-muted-foreground">Phone</dt>
                            <dd>
                                <a
                                    :href="`tel:${activeOrder?.customer?.phone}`"
                                    >{{
                                        activeOrder?.customer?.phone || 'N/A'
                                    }}</a
                                >
                            </dd>
                        </div>
                    </dl>
                </div>
                <Separator class="my-4" />
                <Accordion type="single" collapsible v-model="activeAccordion">
                    <AccordionItem value="billing">
                        <AccordionTrigger>Billing Address</AccordionTrigger>
                        <AccordionContent>
                            <CustomerAddress
                                :address="activeOrder?.customer?.billing"
                            />
                        </AccordionContent>
                    </AccordionItem>

                    <AccordionItem value="shipping">
                        <AccordionTrigger>Shipping Address</AccordionTrigger>
                        <AccordionContent>
                            <CustomerAddress
                                :address="activeOrder?.customer?.shipping"
                            />
                        </AccordionContent>
                    </AccordionItem>
                </Accordion>

                <div class="grid gap-3 mt-3">
                    <div class="font-semibold">Payment Information</div>
                    <dl class="grid gap-3">
                        <template
                            v-if="
                                activeOrder?.payment_details?.split_payments
                                    ?.length
                            "
                        >
                            <div
                                v-for="(payment, index) in activeOrder
                                    ?.payment_details?.split_payments"
                                :key="index"
                                class="flex items-center justify-between"
                            >
                                <dt
                                    class="flex items-center gap-1 text-muted-foreground"
                                >
                                    <CreditCard class="h-4 w-4" />
                                    {{ payment.method }}
                                </dt>
                                <dd>
                                    {{ formatAmount(payment.amount, currency) }}
                                </dd>
                            </div>
                        </template>
                        <template v-else>
                            <div class="flex items-center justify-between">
                                <dt
                                    class="flex items-center gap-1 text-muted-foreground"
                                >
                                    <CreditCard class="h-4 w-4" />
                                    Method
                                </dt>
                                <dd>
                                    {{
                                        activeOrder?.payment_details
                                            ?.payment_method || 'N/A'
                                    }}
                                </dd>
                            </div>
                        </template>
                    </dl>
                </div>
            </CardContent>
        </Card>
    </div>
</template>
