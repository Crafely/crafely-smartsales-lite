import { streamResponse } from './_streamResponse'
import { useAxiosNode } from './useAxios.node'
type CallBacks = {
    textCreated?: () => void
    textDelta?: (data: { value: string }) => void
    textDone?: (data: { value: string }) => void
}

export const useCrafelyAi = () => {
    const _token = localStorage.getItem('_token')
    const _useAxiosNode = useAxiosNode(_token)

    const sendMessage = async (url, payload, cbs: CallBacks) => {
        let _text = ''
        const stream = streamResponse(url, payload)
        for await (const event of stream) {
            if (event.type === 'text_created') {
                cbs?.textCreated?.()
            }
            if (event.type === 'text_delta') {
                _text += event.content
                cbs?.textDelta?.({ value: _text })
            }
            if (event.type === 'text_done') {
                cbs?.textDone?.({ value: event.content })
            }
        }
    }

    const sendMessageWithStructure = async (url, payload) => {
        return await _useAxiosNode.post(url, payload)
    }

    const sendMessageWithTool = async (
        url: string,
        payload: any = {},
        toolMap: any = {},
        cbs: CallBacks
    ) => {
        const { sessionId } = payload
        const stream = streamResponse(url, payload)
        let _text = ''
        let tool_call: { tools: any[]; signatures: any[] } = {
            tools: [],
            signatures: [],
        }
        for await (const event of stream) {
            if (event.type === 'text_created') {
                cbs?.textCreated?.()
            }
            if (event.type === 'text_delta') {
                _text += event.content
                cbs?.textDelta?.({ value: _text })
            }
            if (event.type === 'text_done') {
                cbs?.textDone?.({ value: event.content })
            }
            if (event.type === 'tool_call') {
                tool_call = event.tool_call
            }
        }

        if (!tool_call.tools.length) {
            return
        }

        tool_call.tools = await Promise.all(
            tool_call.tools.map(async (item: any) => {
                return {
                    content: (await toolMap[item.name]?.(item.args)) || '',
                    tool_call_id: item.id,
                }
            })
        )
        const stream2 = streamResponse(url, {
            sessionId,
            prompt: null,
            ...tool_call,
        })
        let _text2 = ''
        for await (const event of stream2) {
            if (event.type === 'text_created') {
                cbs?.textCreated?.()
            }
            if (event.type === 'text_delta') {
                _text2 += event.content
                cbs?.textDelta?.({ value: _text2 })
            }
            if (event.type === 'tool_call') {
                // Second time tool call need to handle
            }
        }
        tool_call.tools = []
    }

    const getMessages = async (threadId: string) => {
        const { data, success } = await _useAxiosNode.get('chat/messages', {
            params: {
                sessionId: threadId,
            },
        })
        return success ? data : []
    }

    return {
        ..._useAxiosNode,
        sendMessage,
        sendMessageWithTool,
        sendMessageWithStructure,
        getMessages,
    }
}
