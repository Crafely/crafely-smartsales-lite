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
    import { RadioGroup, RadioGroupItem } from '@/components/ui/radio-group'
    import { Switch } from '@/components/ui/switch'
</script>

<template>
    <div class="profile-form max-w-4xl">
        <slot name="formHeader"></slot>

        <form @submit.prevent="onSubmit" class="space-y-4">
            <!-- <div class="grid grid-cols-2 gap-4"> -->
            <FormField
                name="notificationPreferences"
                v-slot="{ componentField }"
            >
                <FormItem class="space-y-3">
                    <FormLabel>Notify me about...</FormLabel>

                    <FormControl>
                        <RadioGroup
                            class="flex flex-col space-y-1"
                            v-bind="componentField"
                        >
                            <FormItem
                                class="flex items-center space-y-0 gap-x-3"
                            >
                                <FormControl>
                                    <RadioGroupItem value="all" />
                                </FormControl>
                                <FormLabel class="font-normal">
                                    All new messages
                                </FormLabel>
                            </FormItem>
                            <FormItem
                                class="flex items-center space-y-0 gap-x-3"
                            >
                                <FormControl>
                                    <RadioGroupItem value="mentions" />
                                </FormControl>
                                <FormLabel class="font-normal">
                                    Direct messages and mentions
                                </FormLabel>
                            </FormItem>
                            <FormItem
                                class="flex items-center space-y-0 gap-x-3"
                            >
                                <FormControl>
                                    <RadioGroupItem value="none" />
                                </FormControl>
                                <FormLabel class="font-normal">
                                    Nothing
                                </FormLabel>
                            </FormItem>
                        </RadioGroup>
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <h3 class="mb-4 text-lg font-medium">Email Notifications</h3>

            <div class="space-y-4">
                <FormField
                    v-slot="{ value, handleChange }"
                    name="marketing_emails"
                >
                    <FormItem
                        class="flex flex-row items-center justify-between rounded-lg border p-4"
                    >
                        <div class="space-y-0.5">
                            <FormLabel class="text-base">
                                Marketing emails
                            </FormLabel>
                            <FormDescription>
                                Receive emails about new products, features, and
                                more.
                            </FormDescription>
                        </div>
                        <FormControl>
                            <Switch
                                :model-value="value"
                                @update:model-value="handleChange"
                            />
                        </FormControl>
                    </FormItem>
                </FormField>
                <FormField
                    v-slot="{ value, handleChange }"
                    name="security_emails"
                >
                    <FormItem
                        class="flex flex-row items-center justify-between rounded-lg border p-4"
                    >
                        <div class="space-y-0.5">
                            <FormLabel class="text-base">
                                Security emails
                            </FormLabel>
                            <FormDescription>
                                Receive emails about your account security.
                            </FormDescription>
                        </div>
                        <FormControl>
                            <Switch
                                :model-value="value"
                                disabled
                                aria-readonly
                                @update:model-value="handleChange"
                            />
                        </FormControl>
                    </FormItem>
                </FormField>
            </div>

            <!-- </div> -->
            <slot name="formFooter"></slot>
        </form>
    </div>
</template>
