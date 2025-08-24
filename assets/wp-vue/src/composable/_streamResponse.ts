import { createParser, type EventSourceMessage } from 'eventsource-parser'
import { appConfig } from '@/config/app'
export async function* streamResponse(url, payload: any): AsyncGenerator<any> {
    const _token = localStorage.getItem('_token')
    const res = await fetch(`${appConfig.aiBaseURL}/${url}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Authorization: `Bearer ${_token}`,
        },
        body: JSON.stringify(payload),
    })

    if (!res.body) {
        throw new Error('No response body')
    }

    const eventQueue: any[] = []
    let isDone = false
    let eventResolver: () => void = () => {}

    const parser = createParser({
        onEvent: (event: EventSourceMessage) => {
            try {
                const parsed = JSON.parse(event.data)
                eventQueue.push(parsed)
                eventResolver()
            } catch (err) {
                console.error('Error parsing event data:', err)
            }
        },
    })

    // Modified event generator that can terminate
    async function* eventGenerator() {
        while (!isDone || eventQueue.length > 0) {
            if (eventQueue.length === 0) {
                await new Promise<void>((resolve) => {
                    eventResolver = resolve
                })
            }
            while (eventQueue.length > 0) {
                yield eventQueue.shift()!
            }
        }
    }

    // Read the stream and feed parser
    const reader = res.body.getReader()
    const decoder = new TextDecoder()

    ;(async () => {
        try {
            while (true) {
                const { done, value } = await reader.read()
                if (done) {
                    isDone = true
                    eventResolver() // Resolve one last time to unblock the generator
                    break
                }
                const chunk = decoder.decode(value, { stream: true })
                parser.feed(chunk)
            }
        } catch (error) {
            console.error('Stream reading error:', error)
            isDone = true
            eventResolver()
        }
    })()

    // Yield events as they come
    for await (const event of eventGenerator()) {
        yield event
    }
}
