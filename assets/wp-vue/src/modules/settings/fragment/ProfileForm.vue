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

    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import { userSchema } from '../general/userSchema'

    const form = useForm({
        name: 'profileForm',
        validationSchema: toTypedSchema(userSchema),
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
            <FormField name="username" v-slot="{ componentField }">
                <FormItem>
                    <FormLabel>Username</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Username"
                            disabled
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <FormField name="email" v-slot="{ componentField }">
                <FormItem>
                    <FormLabel>Email</FormLabel>
                    <FormControl>
                        <Input
                            type="email"
                            placeholder="Email"
                            disabled
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>
            <div class="grid grid-cols-2 gap-4">
                <FormField name="first_name" v-slot="{ componentField }">
                    <FormItem>
                        <FormLabel>First Name</FormLabel>
                        <FormControl>
                            <Input
                                type="text"
                                placeholder="First Name"
                                v-bind="componentField"
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>
                <FormField name="last_name" v-slot="{ componentField }">
                    <FormItem>
                        <FormLabel>Last Name</FormLabel>
                        <FormControl>
                            <Input
                                type="text"
                                placeholder="Last Name"
                                v-bind="componentField"
                            />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>
            </div>

            <FormField name="display_name" v-slot="{ componentField }">
                <FormItem>
                    <FormLabel>Display Name</FormLabel>
                    <FormControl>
                        <Input
                            type="text"
                            placeholder="Display Name"
                            v-bind="componentField"
                        />
                    </FormControl>
                    <FormMessage />
                </FormItem>
            </FormField>

            <!-- </div> -->
            <Button type="submit" :loading="form.isSubmitting.value"
                >Update Profile</Button
            >
            <slot name="formFooter"></slot>
        </form>
    </div>
</template>
