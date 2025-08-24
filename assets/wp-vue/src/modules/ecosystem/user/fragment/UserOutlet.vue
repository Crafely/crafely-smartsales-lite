<script setup lang="ts">
    import { Card, CardContent } from '@/components/ui/card'
    import { Badge } from '@/components/ui/badge'
    import UserDescriptionList from './UserDescriptionList.vue'
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table'
    import type { User } from '@/types'

    defineProps<{
        outlet: User['outlet']
    }>()
</script>

<template>
    <Card
        class="outlet-card overflow-hidden rounded-none shadow-none border-none"
    >
        <CardContent class="p-0">
            <div class="grid gap-3">
                <div class="flex items-center">
                    <div class="text-lg font-semibold">
                        {{ outlet?.name || '' }}
                    </div>
                    <Badge variant="outline" class="ml-auto">
                        {{ outlet?.status }}
                    </Badge>
                </div>
                <dl class="grid gap-3">
                    <UserDescriptionList label="Email">
                        <a :href="`mailto:${outlet?.email}`">{{
                            outlet?.email
                        }}</a>
                    </UserDescriptionList>
                    <UserDescriptionList label="Phone">
                        <a :href="`tel:${outlet?.phone}`">{{
                            outlet?.phone
                        }}</a>
                    </UserDescriptionList>
                    <UserDescriptionList
                        label="Address"
                        :value="outlet?.address"
                    />
                    <UserDescriptionList
                        label="Manager"
                        :value="outlet?.manager_name"
                    />
                </dl>
            </div>
            <div class="grid gap-3">
                <div
                    v-if="outlet?.counters?.length"
                    class="border rounded-md mt-3"
                >
                    <Table class="text-sm">
                        <TableHeader>
                            <TableRow>
                                <TableHead>Counter Name</TableHead>
                                <TableHead>Position</TableHead>
                                <TableHead>Status</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow
                                v-for="counter in outlet.counters"
                                :key="counter.id"
                            >
                                <TableCell class="font-medium">{{
                                    counter.name
                                }}</TableCell>
                                <TableCell>{{
                                    counter.position || 'Not specified'
                                }}</TableCell>
                                <TableCell class="capitalize">{{
                                    counter.status
                                }}</TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
                <div v-else class="text-muted-foreground text-sm">
                    No counters available
                </div>
            </div>
        </CardContent>
    </Card>
</template>
