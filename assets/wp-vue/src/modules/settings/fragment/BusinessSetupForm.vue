<script setup lang="ts">
    import { Button } from '@/components/ui/button'
    import {
        FormControl,
        FormDescription,
        FormField,
        FormItem,
        FormLabel,
        FormMessage,
    } from '@/components/ui/form'
    import { Input } from '@/components/ui/input'
    import { Textarea } from '@/components/ui/textarea'
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select'

    import { Checkbox } from '@/components/ui/checkbox'
    import { toggleItems } from '@/utils'

    import { useForm } from 'vee-validate'
    import { setupSchema } from '@/modules/setup/setupSchema'
    import { toTypedSchema } from '@vee-validate/zod'

    import {
        businessTypes,
        inventoryRanges,
        companySizes,
        revenueRanges,
        salesChannels,
        targetMarkets,
    } from '@/modules/setup/setupSchema'

    const form = useForm({
        name: 'setupForm',
        validationSchema: toTypedSchema(setupSchema),
        initialValues: {
            sales_channel: [''],
        },
    })

    const props = defineProps<{
        form?: any
        submitForm: (payload: any, actions: any) => Promise<any>
    }>()

    defineExpose({ form })

    const handleSubmit = form.handleSubmit(async (payload, actions) => {
        await props.submitForm(payload, actions)
    })
</script>

<template>
    <div class="company-form max-w-4xl">
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <div class="mb-4 border-b">
                <h1 class="text-xl">Business Info Settings</h1>
                <p class="mb-6 text-gray-600">
                    Set up your business profile to get started.
                </p>
            </div>
            <!-- <div class="grid grid-cols-2 gap-4"> -->
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
                                <SelectValue
                                    placeholder="Select business type"
                                />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem
                                v-for="type in businessTypes"
                                :key="type"
                                :value="type"
                            >
                                {{
                                    type.charAt(0).toUpperCase() + type.slice(1)
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
            <!-- </div> -->

            <!-- BusinessScale -->
            <div class="mb-4 border-b">
                <h1 class="text-xl">Business Scale Settings</h1>
                <p class="mb-6 text-gray-600">
                    Configure your business scale to tailor the experience to
                    your needs.
                </p>
            </div>

            <!-- <div class="grid grid-cols-2 gap-4"> -->
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
                                <SelectValue
                                    placeholder="Select inventory range"
                                />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem
                                v-for="range in inventoryRanges"
                                :key="range"
                                :value="range"
                            >
                                {{
                                    range.charAt(0).toUpperCase() +
                                    range.slice(1)
                                }}
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
                                <SelectValue
                                    placeholder="Select company size"
                                />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem
                                v-for="size in companySizes"
                                :key="size"
                                :value="size"
                            >
                                {{
                                    size.charAt(0).toUpperCase() + size.slice(1)
                                }}
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
                                <SelectValue
                                    placeholder="Select revenue range"
                                />
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
            <!-- </div> -->

            <!-- BusinessArea -->
            <div class="mb-4 border-b">
                <h1 class="text-xl">Business Areas</h1>
                <p class="mb-6 text-gray-600">
                    Define the areas of your business to help us understand your
                    operations better.
                </p>
            </div>

            <!-- <div class="grid grid-cols-2 gap-4"> -->
            <FormField
                v-slot="{ componentField }"
                :validate-on-blur="!form.isFieldDirty"
                name="has_outlet"
            >
                <FormItem>
                    <FormLabel>Do you have multiple outlets?</FormLabel>
                    <Select v-bind="componentField">
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue placeholder="Select option" />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem :value="true">Yes</SelectItem>
                            <SelectItem :value="false">No</SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField
                :validate-on-blur="!form.isFieldDirty"
                name="sales_channel"
                v-slot="{ value, setValue }"
            >
                <FormItem>
                    <div class="mb-4">
                        <FormLabel>Sales Channel</FormLabel>
                    </div>
                    <FormField
                        v-for="item in salesChannels"
                        :key="item.id"
                        type="checkbox"
                        :value="item.id"
                        :unchecked-value="false"
                        name="sales_channel"
                    >
                        <FormItem
                            class="flex flex-row items-start space-x-3 space-y-0"
                        >
                            <FormControl>
                                <Checkbox
                                    :checked="value.includes(item.id)"
                                    @update:checked="
                                        (state) =>
                                            setValue(
                                                toggleItems(
                                                    state,
                                                    item.id,
                                                    value
                                                )
                                            )
                                    "
                                />
                            </FormControl>
                            <FormLabel class="font-normal">
                                {{ item.label }}
                            </FormLabel>
                        </FormItem>
                    </FormField>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField
                v-slot="{ componentField }"
                :validate-on-blur="!form.isFieldDirty"
                name="target_market"
            >
                <FormItem>
                    <FormLabel>Target Market</FormLabel>
                    <Select v-bind="componentField">
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue
                                    placeholder="Select target market"
                                />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem
                                v-for="market in targetMarkets"
                                :key="market"
                                :value="market"
                            >
                                {{
                                    market.charAt(0).toUpperCase() +
                                    market.slice(1)
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
                name="additional_notes"
            >
                <FormItem>
                    <FormLabel>Additional Notes</FormLabel>
                    <FormControl>
                        <Textarea
                            v-bind="componentField"
                            placeholder="Any additional setup information"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <!-- </div> -->
            <Button type="submit" :loading="form.isSubmitting.value"
                >Update Setup</Button
            >
        </form>
    </div>
</template>
