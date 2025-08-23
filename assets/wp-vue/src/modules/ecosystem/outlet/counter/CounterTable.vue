<script setup lang="ts">
    import { Trash2 } from 'lucide-vue-next'
    import { Button } from '@/components/ui'
    import { Counter } from '@/types'
    import {
        Table,
        TableBody,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table'
    defineProps<{
        counters: Counter[]
        handleDelete: (counter: Counter) => void
    }>()
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead> Name </TableHead>
                <TableHead>Description</TableHead>
                <TableHead class="text-right"> Action </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="counter in counters" :key="counter.id">
                <TableCell class="font-medium">
                    {{ counter.name }}
                </TableCell>
                <TableCell>{{ counter.description }}</TableCell>

                <TableCell class="text-right">
                    <Button
                        @click="handleDelete(counter)"
                        variant="ghost"
                        size="icon"
                    >
                        <Trash2 class="h-4 w-4" />
                    </Button>
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
