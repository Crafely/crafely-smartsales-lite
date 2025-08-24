<script setup lang="ts">
    import {
        FormControl,
        FormField,
        FormItem,
        FormLabel,
        FormMessage,
    } from '@/components/ui/form'
    import { Input } from '@/components/ui/input'
    const props = defineProps<{
        type: 'billing' | 'shipping'
        prefix?: string
    }>()

    const fieldPrefix = props.prefix || props.type
</script>

<template>
    <div class="grid grid-cols-2 gap-4">
        <!-- First Name Field -->
        <FormField
            v-slot="{ componentField }"
            :name="`${fieldPrefix}.first_name`"
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
            :name="`${fieldPrefix}.last_name`"
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

        <!-- Company Field -->
        <FormField v-slot="{ componentField }" :name="`${fieldPrefix}.company`">
            <FormItem>
                <FormLabel>Company</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter company name"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Address Line 1 Field -->
        <FormField
            v-slot="{ componentField }"
            :name="`${fieldPrefix}.address_1`"
            class="col-span-2"
        >
            <FormItem>
                <FormLabel>Address Line 1</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter street address"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Address Line 2 Field -->
        <FormField
            v-slot="{ componentField }"
            :name="`${fieldPrefix}.address_2`"
            class="col-span-2"
        >
            <FormItem>
                <FormLabel>Address Line 2</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Apartment, suite, etc. (optional)"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- City Field -->
        <FormField v-slot="{ componentField }" :name="`${fieldPrefix}.city`">
            <FormItem>
                <FormLabel>City</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter city"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- State Field -->
        <FormField v-slot="{ componentField }" :name="`${fieldPrefix}.state`">
            <FormItem>
                <FormLabel>State/Province</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter state"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Postal Code Field -->
        <FormField
            v-slot="{ componentField }"
            :name="`${fieldPrefix}.postcode`"
        >
            <FormItem>
                <FormLabel>Postal Code</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter postal code"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Country Field -->
        <FormField v-slot="{ componentField }" :name="`${fieldPrefix}.country`">
            <FormItem>
                <FormLabel>Country</FormLabel>
                <FormControl>
                    <Input
                        type="text"
                        placeholder="Enter country"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Email Field (Billing Only) -->
        <FormField
            v-if="type === 'billing'"
            v-slot="{ componentField }"
            :name="`${fieldPrefix}.email`"
        >
            <FormItem>
                <FormLabel>Email</FormLabel>
                <FormControl>
                    <Input
                        type="email"
                        placeholder="Enter email"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>

        <!-- Phone Field (Billing Only) -->
        <FormField
            v-if="type === 'billing'"
            v-slot="{ componentField }"
            :name="`${fieldPrefix}.phone`"
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
</template>
