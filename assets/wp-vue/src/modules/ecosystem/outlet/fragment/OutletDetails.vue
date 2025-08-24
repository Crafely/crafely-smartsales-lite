<script setup lang="ts">
    import { Button } from '@/components/ui/button'
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card'
    import { Separator } from '@/components/ui/separator'
    import { ConfirmDialog, useConfirmDialog } from '@/components/confirmDialog'

    import { MapPin, Trash2, Mail, Phone, Clock, User } from 'lucide-vue-next'
    import CounterTable from '../counter/CounterTable.vue'
    import { formatDate } from '@/utils'
    import type { Outlet } from '@/types'
    import { useCounterStore } from '@/stores/counterStore'
    import { storeToRefs } from 'pinia'
    const counterStore = useCounterStore()
    const { counters } = storeToRefs(counterStore)
    const {
        deleting,
        showConfirmDialog,
        handleDelete: handleCounterDelete,
        confirmDelete,
    } = useConfirmDialog()
    defineProps<{
        activeOutlet: Outlet
        handleDelete: (outlet: Outlet) => void
    }>()
</script>

<template>
    <div>
        <Card
            class="outlet-card overflow-hidden rounded-none shadow-none border-none"
        >
            <CardHeader class="flex flex-row items-start bg-muted/50">
                <div class="grid gap-0.5">
                    <CardTitle class="group flex items-center gap-2 text-lg">
                        {{ activeOutlet?.name || '' }}
                    </CardTitle>
                    <CardDescription>
                        Created: {{ formatDate(activeOutlet?.created_at) }}
                    </CardDescription>

                    <CardDescription>
                        Status:
                        <span
                            :class="{
                                'text-green-500':
                                    activeOutlet?.status === 'active',
                                'text-red-500':
                                    activeOutlet?.status === 'inactive',
                            }"
                            class="capitalize"
                        >
                            {{ activeOutlet?.status }}
                        </span>
                    </CardDescription>
                </div>
                <div class="ml-auto">
                    <Button
                        size="sm"
                        variant="outline"
                        class="hover:text-red-400"
                        @click="handleDelete(activeOutlet)"
                    >
                        <Trash2 class="w-4 h-4" /> Delete
                    </Button>
                </div>
            </CardHeader>
            <CardContent class="p-6 text-sm">
                <template v-if="counters.length">
                    <div class="grid gap-3">
                        <div class="font-semibold">Counters</div>
                        <CounterTable
                            :counters="counters"
                            :handleDelete="
                                (counter) => handleCounterDelete(counter.id)
                            "
                        />
                    </div>
                    <Separator class="my-4" />
                </template>
                <div class="grid gap-3">
                    <div class="font-semibold">Contact Information</div>
                    <dl class="grid gap-3">
                        <div class="flex items-center justify-between">
                            <dt
                                class="flex items-center gap-1 text-muted-foreground"
                            >
                                <Mail class="h-4 w-4" />
                                Email
                            </dt>
                            <dd>
                                <a :href="`mailto:${activeOutlet?.email}`">{{
                                    activeOutlet?.email
                                }}</a>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt
                                class="flex items-center gap-1 text-muted-foreground"
                            >
                                <Phone class="h-4 w-4" />
                                Phone
                            </dt>
                            <dd>
                                <a :href="`tel:${activeOutlet?.phone}`">{{
                                    activeOutlet?.phone
                                }}</a>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt
                                class="flex items-center gap-1 text-muted-foreground"
                            >
                                <MapPin class="h-4 w-4" />
                                Address
                            </dt>
                            <dd>{{ activeOutlet?.address }}</dd>
                        </div>
                    </dl>
                </div>

                <Separator class="my-4" />
                <div class="grid gap-3">
                    <div class="font-semibold">Operations</div>
                    <dl class="grid gap-3">
                        <div class="flex items-center justify-between">
                            <dt
                                class="flex items-center gap-1 text-muted-foreground"
                            >
                                <User class="h-4 w-4" />
                                Manager
                            </dt>
                            <dd>{{ activeOutlet?.manager_name }}</dd>
                        </div>
                        <div class="flex items-center justify-between">
                            <dt
                                class="flex items-center gap-1 text-muted-foreground"
                            >
                                <Clock class="h-4 w-4" />
                                Operating Hours
                            </dt>
                            <dd>{{ activeOutlet?.operating_hours }}</dd>
                        </div>
                    </dl>
                </div>
            </CardContent>
        </Card>
        <ConfirmDialog
            title="Are you sure you want to delete this counter?"
            description="This action will move the counter to the trash and can be restored within 30 days."
            v-model="showConfirmDialog"
            :deleting="deleting"
            @confirm="
                confirmDelete((counterId) =>
                    counterStore.deleteCounter(activeOutlet?.id, counterId)
                )
            "
        />
    </div>
</template>
