<script setup lang="ts">
    import { toast } from 'vue-sonner'
    import { useAxiosNode } from '@/composable/useAxios.node'
    import {
        FormControl,
        FormField,
        FormItem,
        FormLabel,
        FormMessage,
    } from '@/components/ui/form'
    import { Input } from '@/components/ui/input'
    import { Button } from '@/components/ui/button'
    import { useForm } from 'vee-validate'
    import { toTypedSchema } from '@vee-validate/zod'
    import { Alert, AlertDescription, AlertTitle } from '@/components/ui/alert'
    import { Rocket } from 'lucide-vue-next'
    import * as z from 'zod'
    const schema = z.object({
        organization_name: z.string().optional(),
        api_key: z.string({
            required_error: 'API Key is required.',
        }),
    })

    const _token = localStorage.getItem('_token')

    const form = useForm({
        name: 'configForm',
        validationSchema: toTypedSchema(schema),
        initialValues: {
            api_key: _token || '',
        },
    })

    const handleSubmit = form.handleSubmit(async (payload) => {
        const { get } = useAxiosNode(payload.api_key)
        const { success } = await get('thread') // Server request for key validation
        if (!success) {
            toast.error('Make sure your api key is a valid')
            return
        }
        localStorage.setItem('_token', payload.api_key)
    })
</script>

<template>
    <form class="p-10 space-y-6" @submit.prevent="handleSubmit">
        <Alert class="text-left">
            <Rocket class="h-4 w-4" />
            <AlertTitle>How to Get Your Crafely AI API Key</AlertTitle>
            <AlertDescription class="mt-2 space-y-2">
                <p>
                    To use AI features, you'll need a Crafely AI API key. Here's
                    how to get one:
                </p>
                <ol class="list-decimal ml-4 space-y-1">
                    <li>
                        Visit
                        <a
                            href="https://crafely.com"
                            target="_blank"
                            class="text-primary hover:underline"
                            >Crafely AI's website</a
                        >
                    </li>
                    <li>
                        Create an account or log in to your Crafely AI dashboard
                    </li>
                    <li>
                        Navigate to the API section in your account settings
                    </li>
                    <li>Click on "Generate new API key"</li>
                    <li>Copy your API key and enter it below</li>
                </ol>
                <p class="text-sm mt-2 text-muted-foreground">
                    Note: Keep your API key secure and do not share it with
                    others. Your usage will be subject to Crafely AI's pricing
                    and terms.
                </p>
            </AlertDescription>
        </Alert>
        <FormField
            v-slot="{ componentField }"
            name="api_key"
            :validate-on-blur="!form.isFieldDirty"
        >
            <FormItem>
                <FormLabel>Crafely API Key</FormLabel>
                <FormControl>
                    <Input
                        type="password"
                        placeholder="Enter api key"
                        v-bind="componentField"
                    />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <div class="flex justify-end gap-x-2">
            <Button type="submit" :loading="form.isSubmitting.value">
                Update Configuration
            </Button>
        </div>
    </form>
</template>
