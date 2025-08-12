<script setup lang="ts">
    import {
        FormControl,
        FormField,
        FormItem,
        FormLabel,
        FormMessage,
    } from '@/components/ui/form'
    import { Input } from '@/components/ui/input'
    import { Button } from '@/components/ui/button'
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'

    import * as z from 'zod'
    const schema = z.object({
        name: z.string({
            required_error: 'Counter name is required.',
        }),
        description: z.string({
            required_error: 'Description is required.',
        }),
        position: z.string({
            required_error: 'Position is required.',
        }),
        status: z.enum(['active', 'inactive'], {
            required_error: 'Status is required.',
        }),
    })

    const props = defineProps<{
        submitForm?: (
            payload: z.infer<typeof schema>,
            actions: any
        ) => Promise<void>
        name: 'createForm' | 'editForm'
    }>()

    const form = useForm({
        name: props.name ?? 'counterForm',
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
        <FormField
            v-slot="{ componentField }"
            name="name"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Counter Name</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter counter name"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            name="description"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Description</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter counter description"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            name="position"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Position</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter counter position"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            name="status"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Status</FormLabel>
                <FormControl>
                    <select
                        class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                        v-bind="componentField"
                    >
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <Button type="submit" :loading="form.isSubmitting.value">
            {{ name === 'createForm' ? 'Create' : 'Update' }} Counter
        </Button>
    </form>
</template>
