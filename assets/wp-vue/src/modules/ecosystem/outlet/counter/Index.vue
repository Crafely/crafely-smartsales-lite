<template>
    <CounterForm
        v-if="activeOutlet"
        :submitForm="createCounter"
        name="createForm"
        ref="counterCreateFormRef"
    />
</template>

<script lang="ts" setup>
    import { useCounterStore } from '@/stores/counterStore'
    import { useOutletStore } from '@/stores/outletStore'
    import { storeToRefs } from 'pinia'
    import CounterForm from './CounterForm.vue'
    import { useTemplateRef } from 'vue'
    import { toast } from 'vue-sonner'
    const counterCreateFormRef = useTemplateRef<HTMLInputElement>(
        'counterCreateFormRef'
    )
    const counterStore = useCounterStore()
    const { activeOutlet } = storeToRefs(useOutletStore())

    const createCounter = async (payload: any, actions: any) => {
        if (!activeOutlet.value) {
            toast.error('Please select an outlet first')
            return
        }
        const response = await counterStore.createCounter(
            activeOutlet.value.id,
            payload
        )
        if (!response.success) {
            const createForm = counterCreateFormRef.value?.form
            createForm.setErrors(response.error)
        } else {
            actions.resetForm()
        }
    }
</script>
