import { useCrafelyAi } from '@/composable/useCrafelyAi'
import { ref } from 'vue'
import { useDashboardStore } from '@/stores/dashboardStore'
import { get_dashboard_summary } from './useChat.tool'
import { useRoute } from 'vue-router'
export const useChat = () => {
    const { sendMessageWithTool, getMessages } = useCrafelyAi()
    const { getDashboardSummary } = useDashboardStore()
    const messages = ref<any>([])
    const form = ref({
        prompt: '',
        loading: false,
    })
    const suggestPrompts = ref([
        {
            id: 1,
            title: 'Add Product',
            content:
                'Generate an e-commerce product with name, price, and SKU. Example: iPhone 11 Pro Max, $100, SKU iphone-11',
        },
        {
            id: 2,
            title: 'Stock Update',
            content:
                'Update the stock quantity for a product. Example: Update stock for SKU iphone-11 to 50 units.',
        },
        {
            id: 3,
            title: 'Sales Report',
            content:
                'Generate a sales report for the last month. Example: Show sales report for September 2023.',
        },
        {
            id: 4,
            title: 'Low Stock',
            content:
                'List all products with low stock. Example: Show products with stock less than 10 units.',
        },
        {
            id: 5,
            title: 'Order Status',
            content:
                'Check the status of an order. Example: Check status for Order ID 12345.',
        },
    ])

    const route = useRoute()

    const toolMap = {
        get_dashboard_summary: async (query: any) => {
            let { data } = await getDashboardSummary(query)
            return JSON.stringify(data || {})
        },
    }

    const handleSendMessage = async () => {
        if (!form.value.prompt) return
        form.value.loading = true
        messages.value.push({
            role: 'user',
            content: form.value.prompt,
        })

        const payload = {
            prompt: form.value.prompt,
            sessionId: route.query.thread_id,
            tools_schema: [get_dashboard_summary()],
        }
        form.value.prompt = ''
        messages.value.push({
            role: 'assistant',
            content: 'Thinking ...',
        })
        await sendMessageWithTool('chat/completion', payload, toolMap, {
            textCreated: () => {
                messages.value[messages.value.length - 1].content = ''
            },
            textDelta: ({ value }) => {
                messages.value[messages.value.length - 1].content = value
            },
            textDone: ({ value }) => {
                // Server save state
            },
        })
        form.value.loading = false
    }

    const loadMessages = async (id) => {
        messages.value = []
        messages.value = await getMessages(id)
    }
    return {
        form,
        messages,
        suggestPrompts,
        loadMessages,
        handleSendMessage,
    }
}
