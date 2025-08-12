<script setup lang="ts">
    import {
        Card,
        CardContent,
        CardDescription,
        CardFooter,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card'
    import { Button } from '@/components/ui/button'
    import { Form } from '@/components/ui/form'
    import Stepper from './Stepper.vue'
    import Step1Form from './Step1Form.vue'
    import Step2Form from './Step2Form.vue'
    import Step3Form from './Step3Form.vue'
    import { useSetupForm } from './useSetupForm'

    const { currentStep, totalSteps, form, nextStep, prevStep, onSubmit } =
        useSetupForm()
</script>

<template>
    <div class="max-w-3xl mx-auto p-4 min-h-screen flex items-center">
        <form @submit.prevent="onSubmit">
            <Card class="w-full">
                <CardHeader class="text-center">
                    <CardTitle class="text-2xl font-bold"
                        >Welcome to Crafely</CardTitle
                    >
                    <CardDescription>
                        Let's set up your business profile to get you started
                    </CardDescription>
                    <Stepper v-model="currentStep" class="mt-6 mb-8" />
                </CardHeader>
                <CardContent>
                    <Step1Form v-if="currentStep === 1" :form="form" />
                    <Step2Form v-if="currentStep === 2" :form="form" />
                    <Step3Form v-if="currentStep === 3" :form="form" />
                </CardContent>

                <CardFooter class="flex gap-x-3 justify-between">
                    <Button
                        v-if="currentStep > 1"
                        variant="outline"
                        @click="prevStep"
                        type="button"
                    >
                        Previous
                    </Button>
                    <Button
                        variant="outline"
                        @click="nextStep"
                        class="ml-auto"
                        type="button"
                    >
                        Skip
                    </Button>
                    <Button
                        v-if="currentStep < totalSteps"
                        @click="nextStep"
                        type="button"
                    >
                        Next
                    </Button>
                    <Button v-if="currentStep === totalSteps" type="submit">
                        Complete Setup
                    </Button>
                </CardFooter>
            </Card>
        </form>
    </div>
</template>
