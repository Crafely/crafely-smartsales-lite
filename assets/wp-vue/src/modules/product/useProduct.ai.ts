import { useCrafelyAi } from '@/composable/useCrafelyAi'
import { getDescriptionGeneratePayload } from './payload'
import { validateProduct } from '@/lib/crafelyai.schema'
import { toast } from 'vue-sonner'

export const useProductAi = () => {
    const { sendMessage } = useCrafelyAi()

    const generate_description = async (type: string, form: any) => {
        try {
            validateProduct(form.values)
        } catch (error: any) {
            error.errors.forEach((e: any) => {
                toast.error(e.message)
            })
            return
        }

        const payload = getDescriptionGeneratePayload({
            type,
            form,
        })
        const fieldKey = type === 'short' ? 'short_description' : 'description'
        await sendMessage('product/generate-description', payload, {
            textCreated: () => {
                form.setFieldValue(fieldKey, '')
            },
            textDelta: ({ value }) => {
                form.setFieldValue(fieldKey, value)
            },
            textDone: ({ value }) => {
                form.setFieldValue(fieldKey, value)
            },
        })
    }

    return {
        generate_description,
    }
}
