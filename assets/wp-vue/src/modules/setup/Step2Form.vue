<script setup lang="ts">
    import {
        Form,
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
    import { inventoryRanges, companySizes } from './setupSchema'
    import type { Form as UseFormReturn } from 'vee-validate'
    defineProps<{
        form: any
    }>()

    const revenueRanges = [
        { value: '0-10000', label: '$0 - $10,000' },
        { value: '10000-50000', label: '$10,000 - $50,000' },
        { value: '50000-100000', label: '$50,000 - $100,000' },
        { value: '100000+', label: '$100,000+' },
    ]
</script>

<template>
    <div class="space-y-4">
        <FormField
            v-slot="{ componentField }"
            :validate-on-blur="!form.isFieldDirty"
            name="inventory_range"
        >
            <FormItem>
                <FormLabel>Inventory Range</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger>
                            <SelectValue placeholder="Select inventory range" />
                        </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem
                            v-for="range in inventoryRanges"
                            :key="range"
                            :value="range"
                        >
                            {{ range.charAt(0).toUpperCase() + range.slice(1) }}
                            {{
                                range === 'small'
                                    ? '(< 1000 items)'
                                    : range === 'medium'
                                    ? '(1000-10000 items)'
                                    : range === 'large'
                                    ? '(10000-50000 items)'
                                    : '(50000+ items)'
                            }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            :validate-on-blur="!form.isFieldDirty"
            name="company_size"
        >
            <FormItem>
                <FormLabel>Company Size</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger>
                            <SelectValue placeholder="Select company size" />
                        </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem
                            v-for="size in companySizes"
                            :key="size"
                            :value="size"
                        >
                            {{ size.charAt(0).toUpperCase() + size.slice(1) }}
                            {{
                                size === 'small'
                                    ? '(1-50 employees)'
                                    : size === 'medium'
                                    ? '(51-200 employees)'
                                    : '(200+ employees)'
                            }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>

        <FormField
            v-slot="{ componentField }"
            :validate-on-blur="!form.isFieldDirty"
            name="monthly_revenue"
        >
            <FormItem>
                <FormLabel>Monthly Revenue Range</FormLabel>
                <Select v-bind="componentField">
                    <FormControl>
                        <SelectTrigger>
                            <SelectValue placeholder="Select revenue range" />
                        </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem
                            v-for="range in revenueRanges"
                            :key="range.value"
                            :value="range.value"
                        >
                            {{ range.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <FormMessage />
            </FormItem>
        </FormField>
    </div>
</template>
