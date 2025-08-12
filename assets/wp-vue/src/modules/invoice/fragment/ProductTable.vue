<script setup lang="ts">
    import { formatAmount } from '@/utils'
    import { Trash2 } from 'lucide-vue-next'
    import EditableText from '@/components/EditableText.vue'
    import {
        Table,
        TableBody,
        TableCaption,
        TableCell,
        TableHead,
        TableHeader,
        TableRow,
    } from '@/components/ui/table'
    const props = defineProps({
        modelValue: {
            type: Object,
            required: true,
        },
        itemTotalPrice: {
            type: Number,
            default: 0,
        },
    })

    const emit = defineEmits(['handleRemove'])
</script>
<template>
    <Table>
        <TableCaption v-if="!modelValue.length">No product added.</TableCaption>
        <TableHeader>
            <TableRow>
                <TableHead class="w-[60%]">Item</TableHead>
                <TableHead class="w-12">Qty</TableHead>
                <TableHead class="w-12">Rate</TableHead>
                <TableHead> Amount </TableHead>
                <TableHead class="print:hidden"> ... </TableHead>
            </TableRow>
        </TableHeader>
        <TableBody v-if="modelValue.length">
            <TableRow
                v-for="(item, index) in modelValue"
                :key="index"
                class="group hover:bg-accent"
            >
                <TableCell class="w-[60%]">
                    <div>
                        <p class="font-medium">
                            <EditableText
                                v-model="item.custom_name"
                            ></EditableText>
                        </p>
                        <p class="text-sm text-muted-foreground">
                            <div v-html="item.custom_description"></div>
                        </p>
                    </div>
                </TableCell>
                <TableCell class="w-12">
                    <input
                        type="number"
                        v-model="item.quantity"
                        :min="1"
                        class="bg-transparent w-full"
                    />
                </TableCell>
                <TableCell class="w-20">
                    <input
                        type="number"
                        v-model="item.custom_price"
                        :min="0"
                        class="bg-transparent w-full"
                    />
                </TableCell>
                <TableCell  class="w-20">
                    {{ formatAmount(itemTotalPrice[index]) }}
                </TableCell>
                <TableCell class="print:hidden w-8">
                    <Trash2
                        @click="$emit('handleRemove', item)"
                        class="w-4 h-4 hover:text-red-400 cursor-pointer"
                    />
                </TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
