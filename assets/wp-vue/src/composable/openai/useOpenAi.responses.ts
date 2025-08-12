import { useOpenAi } from './useOpenAi'

export const useOpenAiResponses = () => {
    // @ts-ignore
    const { openai } = useOpenAi()
    const sendMessage = async (payload, config) => {
        const _response = await openai?.responses.create({
            model: 'gpt-4o',
            stream: true,
            ...payload,
        })
        if (!_response) {
            return
        }
        for await (const event of _response) {
            if (event.type === 'response.created') {
                config?.textCreated?.(event.response)
            }
            if (event.type === 'response.output_text.delta') {
                config?.textDelta?.({ value: event.delta })
            }
            if (event.type === 'response.output_text.done') {
                config?.textDone?.({ value: event.text })
            }
        }
    }

    const getMessagesByResponseId = async (responseId: string) => {
        const response = await openai?.responses.inputItems.list(responseId)
        if (!response) {
            return []
        }
        return response.body.data.map((item) => {
            return {
                role: item.role,
                content: item.content[0].text,
            }
        })
    }

    return {
        sendMessage,
        getMessagesByResponseId,
    }
}
