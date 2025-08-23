import { ref } from 'vue'
import { useForm } from 'vee-validate'
import { setupSchema } from './setupSchema'
import { toTypedSchema } from '@vee-validate/zod'

export function useSetupForm() {
    const currentStep = ref(1)
    const totalSteps = 3

    const form = useForm({
        validationSchema: toTypedSchema(setupSchema),
        keepValuesOnUnmount: true,
        initialValues: {
            business_type: 'retail',
            inventory_range: 'small',
            company_name: '',
            industry_sector: '',
            has_outlet: 'no',
            company_size: 'small',
            monthly_revenue: '0-10000',
            sales_channel: ['website'],
            target_market: 'local',
            additional_notes: '',
        },
    })

    const nextStep = async () => {
        const stepFields = {
            1: ['business_type', 'company_name', 'industry_sector'],
            2: ['inventory_range'],
            3: ['has_outlet', 'sales_channel', 'target_market'],
        }

        const currentFields = stepFields[currentStep.value] || []
        const validationResults = await Promise.all(
            currentFields.map((field) => form.validateField(field))
        )
        const allValid = validationResults.every((result) => result.valid)
        if (allValid && currentStep.value < totalSteps) {
            currentStep.value++
        }
    }

    const prevStep = () => {
        if (currentStep.value > 1) {
            currentStep.value--
        }
    }

    const onSubmit = form.handleSubmit((values) => {
        console.log('Form submitted:', values)
        // Handle form submission
    })

    return {
        currentStep,
        totalSteps,
        form,
        nextStep,
        prevStep,
        onSubmit,
    }
}
