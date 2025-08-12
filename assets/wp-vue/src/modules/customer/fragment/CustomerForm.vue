<script setup lang="ts">
    import AddressForm from './AddressForm.vue'
    import {
        FormControl,
        FormField,
        FormItem,
        FormLabel,
        FormMessage,
    } from '@/components/ui/form'
    import { Switch } from '@/components/ui/switch'
    import { Input } from '@/components/ui/input'
    import { Button } from '@/components/ui/button'
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import * as z from 'zod'
    import { schema } from '../customerSchema'

    const props = defineProps<{
        submitForm: (
            payload: z.infer<typeof schema>,
            actions: any
        ) => Promise<void>
        name?: string
    }>()

    const form = useForm({
        name: props.name ?? 'customerForm',
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
        <div class="grid grid-cols-2 gap-4">
            <!-- Username Field -->
            <FormField
                v-if="name !== 'editForm'"
                v-slot="{ componentField }"
                name="username"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Username</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Enter username"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Email Field -->
            <FormField
                v-if="name !== 'editForm'"
                v-slot="{ componentField }"
                name="email"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Email</FormLabel>
                    <FormControl>
                        <Input
                            type="email"
                            placeholder="email@example.com"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- First Name Field -->
            <FormField
                v-slot="{ componentField }"
                name="first_name"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>First Name</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Enter first name"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Last Name Field -->
            <FormField
                v-slot="{ componentField }"
                name="last_name"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Last Name</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Enter last name"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- Phone Field -->
            <FormField
                v-slot="{ componentField }"
                name="phone"
                :validate-on-blur="!form.isFieldDirty"
            >
                <FormItem>
                    <FormLabel>Phone</FormLabel>
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
        </div>
        <!-- Billing Address Section -->
        <div class="space-y-6">
            <div class="space-y-6">
                <div class="space-y-2">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-medium">Billing Address</h3>
                        <FormField
                            v-slot="{ value, handleChange }"
                            name="sameAddress"
                        >
                            <FormItem
                                class="flex flex-row items-center justify-start gap-x-2 rounded-lg px-2 py-1"
                            >
                                <div>
                                    <FormLabel class="text-xs"
                                        >Use same address for
                                        Shipping</FormLabel
                                    >
                                </div>
                                <FormControl>
                                    <Switch
                                        :checked="value"
                                        @update:checked="handleChange"
                                    />
                                </FormControl>
                            </FormItem>
                        </FormField>
                    </div>
                    <AddressForm type="billing" />
                </div>

                <div v-if="!form.values.sameAddress" class="space-y-2">
                    <h3 class="text-lg font-medium">Shipping Address</h3>
                    <AddressForm type="shipping" />
                </div>
            </div>
        </div>

        <Button type="submit" :loading="form.isSubmitting.value">
            Create Customer
        </Button>
    </form>
</template>
