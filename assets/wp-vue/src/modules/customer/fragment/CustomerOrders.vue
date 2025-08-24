<script setup lang="ts">
    import { Badge } from '@/components/ui/badge'
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table'
    import { Card, CardContent } from '@/components/ui/card'
    import { CustomerOrder } from '@/types'
    import { formatDateTime } from '@/utils'
    defineProps<{
        orders: CustomerOrder[]
    }>()

    const getStatusColor = (status: string) => {
        switch (status.toLowerCase()) {
            case 'completed':
                return 'bg-green-100 text-green-800'
            case 'pending':
                return 'bg-yellow-100 text-yellow-800'
            case 'cancelled':
                return 'bg-red-100 text-red-800'
            default:
                return 'bg-gray-100 text-gray-800'
        }
    }
</script>

<template>
    <Card class="rounded-none shadow-none border-none">
        <CardContent class="p-0">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Order ID</TableHead>
                        <TableHead>Date</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead class="text-right">Total</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="order in orders" :key="order.order_id">
                        <TableCell class="font-medium"
                            >#{{ order.order_id }}</TableCell
                        >
                        <TableCell>{{ formatDateTime(order.date) }}</TableCell>
                        <TableCell>
                            <Badge
                                class="shadow-none scale-90"
                                :class="getStatusColor(order.status)"
                                variant="solid"
                            >
                                {{ order.status }}
                            </Badge>
                        </TableCell>
                        <TableCell class="text-right"
                            >${{ order.total }}</TableCell
                        >
                    </TableRow>
                </TableBody>
            </Table>
        </CardContent>
    </Card>
</template>
