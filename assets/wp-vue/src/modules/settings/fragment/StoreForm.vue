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
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select'

    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import { storeSchema, currencies } from '../general/appSchema'

    const form = useForm({
        name: 'storeForm',
        validationSchema: toTypedSchema(storeSchema),
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
    <div class="profile-form max-w-4xl">
        <slot name="formHeader"></slot>
        <form @submit.prevent="handleSubmit" class="space-y-4">
            <!-- <div class="grid grid-cols-2 gap-4"> -->
            <FormField name="site_name" v-slot="{ componentField }">
                <FormItem>
                    <FormLabel>Store Name</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Store Name"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- <div class="grid grid-cols-2 gap-4">
            </div> -->

            <FormField name="store_country" v-slot="{ componentField }">
                <FormItem>
                    <FormLabel>Store Country</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Store Country"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField name="store_city" v-slot="{ componentField }">
                <FormItem>
                    <FormLabel>Store City</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Store City"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField name="store_postcode" v-slot="{ componentField }">
                <FormItem>
                    <FormLabel>Store Postcode</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Store Postcode"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField name="store_address" v-slot="{ componentField }">
                <FormItem>
                    <FormLabel>Store Address</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Store Address"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField name="store_address_2" v-slot="{ componentField }">
                <FormItem>
                    <FormLabel>Store Address Secondary</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Store Address Secondary"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <FormField
                v-slot="{ componentField }"
                :validate-on-blur="!form.isFieldDirty"
                name="currency"
            >
                <FormItem>
                    <FormLabel>Currency</FormLabel>
                    <Select v-bind="componentField">
                        <FormControl>
                            <SelectTrigger>
                                <SelectValue placeholder="Select Currency" />
                            </SelectTrigger>
                        </FormControl>
                        <SelectContent>
                            <SelectItem
                                v-for="currency in currencies"
                                :key="currency"
                                :value="currency"
                            >
                                {{
                                    currency.charAt(0).toUpperCase() +
                                    currency.slice(1)
                                }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- </div> -->
            <Button type="submit" :loading="form.isSubmitting.value"
                >Update Store</Button
            >
            <slot name="formFooter"></slot>
        </form>
    </div>
</template>
