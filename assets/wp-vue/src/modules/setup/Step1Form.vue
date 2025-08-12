<script setup lang="ts">
    import {
        Form,
        FormControl,
        FormField,
        FormItem,
        FormLabel,
        FormMessage,
    } from '@/components/ui/form'
    import { Input } from '@/components/ui/input'
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select'
    import { businessTypes } from './setupSchema'

    defineProps<{
        form: any
    }>()
</script>

<template>
    <div class="space-y-4">
        <FormField
            v-slot="{ componentField }"
            :validate-on-blur="!form.isFieldDirty"
            name="company_name"
        >
            <FormItem>
                <FormLabel>Company Name</FormLabel>
                <FormControl>
                    <Input
                        v-bind="componentField"
                        placeholder="Enter your company name"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <FormField
            v-slot="{ componentField }"
            :validate-on-blur="!form.isFieldDirty"
            name="business_type"
        >
            <FormItem>
                <FormLabel>Business Type</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger>
                            <SelectValue placeholder="Select business type" />
                        </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem
                            v-for="type in businessTypes"
                            :key="type"
                            :value="type"
                        >
                            {{ type.charAt(0).toUpperCase() + type.slice(1) }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            :validate-on-blur="!form.isFieldDirty"
            name="industry_sector"
        >
            <FormItem>
                <FormLabel>Industry Sector</FormLabel>
                <FormControl>
                    <Input
                        v-bind="componentField"
                        placeholder="e.g., Fashion, Technology"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
    </div>
</template>
