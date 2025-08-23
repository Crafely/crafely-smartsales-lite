import { h, ref } from 'vue'
import { Bot } from 'lucide-vue-next'

export const tabs = [
    {
        value: 'overview',
        label: 'Overview',
    },
    {
        value: 'analytics',
        label: 'Analytics',
    },
    {
        value: 'recentActivities',
        label: 'Activities',
    },
]

export const subMenuTabs = ref([
    {
        value: 'assistant',
        label: 'Assistant',
        icon: h(Bot),
    },
])
