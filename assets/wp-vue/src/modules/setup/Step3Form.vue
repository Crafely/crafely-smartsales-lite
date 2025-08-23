<script setup lang="ts">
    import {
        FormControl,
        FormField,
        FormItem,
        FormLabel,
        FormMessage,
    } from '@/components/ui/form'
    import { Textarea } from '@/components/ui/textarea'
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select'
    import { Checkbox } from '@/components/ui/checkbox'
    import { salesChannels, targetMarkets } from './setupSchema'
    import { toggleItems } from '@/utils'
    defineProps<{
        form: any
    }>()
</script>

<template>
    <div class="space-y-4">
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
                        <SelectItem value="yes">Yes</SelectItem>
                        <SelectItem value="no">No</SelectItem>
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
                                            toggleItems(state, item.id, value)
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
                            <SelectValue placeholder="Select target market" />
                        </SelectTrigger>
                    </FormControl>
                    <SelectContent>
                        <SelectItem
                            v-for="market in targetMarkets"
                            :key="market"
                            :value="market"
                        >
                            {{
                                market.charAt(0).toUpperCase() + market.slice(1)
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
    </div>
</template>
