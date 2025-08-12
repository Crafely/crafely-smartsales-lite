<script setup lang="ts">
    import {
        FormControl,
        FormField,
        FormItem,
        FormLabel,
        FormMessage,
    } from '@/components/ui/form'
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select'
    import { Button } from '@/components/ui/button'
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'
    import LineItemsTable from './LineItemsTable.vue'
    import { ref, watch } from 'vue'

    const schema = z.object({
        status: z.enum([
            'pending',
            'processing',
            'completed',
            'cancelled',
            'refunded',
            'failed',
            'on-hold',
        ]),
        line_items: z
            .array(
                z.object({
                    product_id: z.number(),
                    quantity: z.number().min(1),
                })
            )
            .min(1, 'At least one item is required'),
    })

    const props = defineProps<{
        submitForm?: (
            payload: z.infer<typeof schema>,
            actions: any
        ) => Promise<void>
        name?: string
    }>()

    const form = useForm({
        name: props.name ?? 'orderForm',
        validationSchema: toTypedSchema(schema),
    })

    defineExpose({
        form,
    })

    const handleSubmit = form.handleSubmit(async (payload, actions) => {
        await props.submitForm(payload, actions)
    })
</script>

<template>
    <form class="p-10 space-y-6" @submit.prevent="handleSubmit">
        <!-- Status Field -->
        <FormField
            v-slot="{ componentField }"
            name="status"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Order Status</FormLabel>
                <FormControl>
                    <Select v-bind="componentField">
                        <SelectTrigger>
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="pending">Pending</SelectItem>
                            <SelectItem value="processing"
                                >Processing</SelectItem
                            >
                            <SelectItem value="completed">Completed</SelectItem>
                            <SelectItem value="cancelled">Cancelled</SelectItem>
                            <SelectItem value="refunded">Refunded</SelectItem>
                            <SelectItem value="failed">Failed</SelectItem>
                            <SelectItem value="on-hold">On Hold</SelectItem>
                        </SelectContent>
                    </Select>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Line Items Table -->
        <FormField name="line_items" v-slot="{ field }">
            <FormItem>
                <LineItemsTable :form="form" />
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Submit Button -->
        <Button type="submit" :loading="form.isSubmitting.value">
            Update Order
        </Button>
    </form>
</template>
