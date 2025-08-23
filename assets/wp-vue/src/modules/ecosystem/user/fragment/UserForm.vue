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
    import { Input } from '@/components/ui/input'
    import { Button } from '@/components/ui/button'
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import { computed } from 'vue'
    import { useOutletStore } from '@/stores/outletStore'
    import { storeToRefs } from 'pinia'
    const outletStore = useOutletStore()
    const { outlets, activeOutlet } = storeToRefs(outletStore)

    import * as z from 'zod'
    const schema = z
        .object({
            // Required fields
            name: z
                .string({
                    required_error: 'Username is required',
                })
                .min(3, 'Username must be at least 3 characters'),
            email: z
                .string({
                    required_error: 'Email is required',
                })
                .email('Please enter a valid email address'),

            password: z
                .string({
                    required_error: 'Password is required',
                })
                .min(8, 'Password must be at least 8 characters')
                .optional(),
            role: z.enum(
                [
                    'csmsl_pos_cashier',
                    'csmsl_pos_outlet_manager',
                    'csmsl_pos_shop_manager',
                ],
                {
                    required_error: 'Role is required',
                }
            ),

            // Optional fields
            display_name: z.string().optional(),
            outlet_id: z.number().optional(),
            counter_id: z.number().optional(),
            phone: z.string().optional(),
            status: z.string().optional(),
        })
        .refine(
            (data) => {
                if (
                    data.role === 'csmsl_pos_outlet_manager' &&
                    !data.outlet_id
                ) {
                    return false
                }
                return true
            },
            {
                message: 'Outlet ID is required for outlet managers',
                path: ['outlet_id'],
            }
        )

    const props = defineProps<{
        name: 'createForm' | 'editForm'
        submitForm: (
            payload: z.infer<typeof schema>,
            actions: any
        ) => Promise<void>
    }>()

    const form = useForm({
        name: props.name ?? 'userForm',
        validationSchema: toTypedSchema(schema),
    })

    defineExpose({
        form,
    })

    const showCounterId = computed(() => {
        return form.values.role === 'csmsl_pos_cashier'
    })

    const handleSubmit = form.handleSubmit(async (payload, actions) => {
        await props.submitForm(payload, actions)
    })

    const handleGetCounters = async (value) => {
        await outletStore.getSingleOutlet(value)
    }
</script>

<template>
    <form class="p-10 space-y-6" @submit.prevent="handleSubmit">
        <!-- Username Field -->
        <FormField
            v-slot="{ componentField }"
            name="name"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Username</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        :disabled="name === 'editForm'"
                        placeholder="Enter username (no spaces)"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <!-- Password Field -->
        <FormField
            v-slot="{ componentField }"
            name="password"
            v-if="name === 'createForm'"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Password</FormLabel>
                <FormControl>
                    <Input
                        type="password"
                        autofill="0"
                        placeholder="Enter password"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <!-- Display Name Field -->
        <FormField
            v-slot="{ componentField }"
            name="display_name"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Display Name</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter full name"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Email Field -->
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
                        :disabled="name === 'editForm'"
                        placeholder="Enter email address"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Role Field -->
        <FormField
            v-slot="{ componentField }"
            name="role"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Role</FormLabel>
                <FormControl>
                    <Select v-bind="componentField">
                        <SelectTrigger>
                            <SelectValue placeholder="Select role" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="csmsl_pos_cashier">
                                Cashier
                            </SelectItem>
                            <SelectItem value="csmsl_pos_outlet_manager"
                                >Outlet manager</SelectItem
                            >
                        </SelectContent>
                    </Select>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Outlet ID Field -->
        <FormField
            v-slot="{ componentField }"
            name="outlet_id"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Select Outlet</FormLabel>
                <FormControl>
                    <Select
                        v-bind="componentField"
                        @update:modelValue="handleGetCounters"
                    >
                        <SelectTrigger>
                            <SelectValue placeholder="Select outlet" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="outlet in outlets"
                                :key="outlet.id"
                                :value="outlet.id"
                            >
                                {{ outlet.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Counter ID Field (Only for Cashiers) -->
        <FormField
            v-if="showCounterId"
            v-slot="{ componentField }"
            name="counter_id"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Select Counter</FormLabel>
                <FormControl>
                    <Select v-bind="componentField">
                        <SelectTrigger>
                            <SelectValue placeholder="Select counter" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="counter in activeOutlet?.counters || []"
                                :key="counter.id"
                                :value="counter.id"
                            >
                                {{ counter.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
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
            name="status"
            v-if="name === 'editForm'"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Status</FormLabel>
                <FormControl>
                    <Select v-bind="componentField">
                        <SelectTrigger>
                            <SelectValue placeholder="Select status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="active"> Active </SelectItem>
                            <SelectItem value="inactive"> Inactive </SelectItem>
                        </SelectContent>
                    </Select>
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <Button type="submit" :loading="form.isSubmitting.value">
            {{ name === 'createForm' ? 'Create' : 'Update' }} User
        </Button>
    </form>
</template>
