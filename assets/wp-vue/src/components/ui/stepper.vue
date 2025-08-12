<script setup lang="ts">
import { computed } from 'vue'

interface Step {
    title: string
    description?: string
}

const props = defineProps<{
    steps: Step[]
    currentStep: number
}>()

const isStepComplete = (index: number) => {
    return index < props.currentStep - 1
}

const isCurrentStep = (index: number) => {
    return index === props.currentStep - 1
}
</script>

<template>
    <div class="relative">
        <div
            class="absolute top-4 left-1 w-full h-0.5 bg-gray-200"
            aria-hidden="true"
        >
            <div
                class="absolute h-full bg-primary transition-all duration-500 ease-in-out"
                :style="{
                    width: `${((currentStep - 1) / (steps.length - 1)) * 100}%`,
                }"
            ></div>
        </div>

        <ul class="relative flex justify-between w-full">
            <li
                v-for="(step, index) in steps"
                :key="index"
                class="flex flex-col items-center"
                :class="{
                    'text-primary': isCurrentStep(index) || isStepComplete(index),
                }"
            >
                <div
                    class="flex items-center justify-center w-8 h-8 rounded-full border-2 transition-colors duration-300 ease-in-out mb-2"
                    :class="{
                        'border-primary bg-primary text-white':
                            isCurrentStep(index) || isStepComplete(index),
                        'border-gray-300 bg-white': !isCurrentStep(index) && !isStepComplete(index),
                    }"
                >
                    <span v-if="isStepComplete(index)" class="text-sm">✓</span>
                    <span v-else class="text-sm">{{ index + 1 }}</span>
                </div>
                <span
                    class="text-sm font-medium transition-colors duration-300 ease-in-out"
                    :class="{
                        'text-gray-900': isCurrentStep(index) || isStepComplete(index),
                        'text-gray-500': !isCurrentStep(index) && !isStepComplete(index),
                    }"
                    >{{ step.title }}</span
                >
                <span
                    v-if="step.description"
                    class="text-xs text-gray-500 mt-1"
                    >{{ step.description }}</span
                >
            </li>
        </ul>
    </div>
</template>