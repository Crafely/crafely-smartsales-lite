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
            required_error: 'Outlet name is required.',
        }),
        address: z.string({
            required_error: 'Address is required.',
        }),
        phone: z.string({
            required_error: 'Phone number is required.',
        }),
        email: z
            .string({
                required_error: 'Email is required.',
            })
            .email('Please enter a valid email address.'),
        operating_hours: z.string({
            required_error: 'Operating hours are required.',
        }),
        manager_name: z.string({
            required_error: 'Manager name is required.',
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
        name: props.name ?? 'outletForm',
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
                <FormLabel>Outlet Name</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter outlet name"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            name="address"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Address</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter outlet address"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            name="phone"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Phone Number</FormLabel>
                <FormControl>
                    <Input
                        type="tel"
                        placeholder="Enter phone number"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            name="email"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Email</FormLabel>
                <FormControl>
                    <Input
                        type="email"
                        placeholder="Enter email address"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            name="operating_hours"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Operating Hours</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="e.g., Mon-Sun: 8AM-8PM"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            name="manager_name"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Manager Name</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter manager name"
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
            {{ name === 'createForm' ? 'Create' : 'Update' }} Outlet
        </Button>
    </form>
</template>
