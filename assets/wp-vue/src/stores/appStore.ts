import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { useAxios } from '@/composable/useAxios'
import { toast } from 'vue-sonner'
import type { AppSettings } from '@/types'
export const useAppStore = defineStore('app', () => {
    const posRoutes = ref([
        { title: 'Dashboard', path: '/dashboard', name: 'app.home' }, //will redirect to dashboard
        { title: 'POS Panel', path: '/pos', name: 'app.pos' },
        { title: 'Order', path: '/order', name: 'app.order' },
    ])
    const appSettings = ref<AppSettings | null>(null)
    const error = ref(null)
    const themes = ['black', 'orange', 'green', 'rose', 'blue', 'violet']
    const activeTheme = ref('black') // Default theme
    const loading = ref(false)
    const { get, put } = useAxios()

    const currency = computed(() => appSettings.value?.currency || 'USD')
    const businessType = computed(
        () => appSettings.value?.business_type || 'retail'
    )
    const inventorySize = computed(() => {
        const size = appSettings.value?.inventory_size
        if (!size || size <= 50) return 'small'
        if (size <= 500) return 'medium'
        return 'large'
    })

    const isAiConfig = computed(() => {
        return !!localStorage.getItem('_token')
    })

    const getAppSettings = async () => {
        try {
            const { data } = await get('app')
            appSettings.value = data
        } catch (err) {
            throw err
        }
    }

    const updateAppSettings = async (payload: Partial<AppSettings>) => {
        try {
            const { data, error, success, message } = await put('app', payload)
            if (success) {
                appSettings.value = { ...appSettings.value, ...data }
            }
            toast[success ? 'success' : 'error'](message)
            return { data, error, success, message }
        } catch (err) {
            throw err
        }
    }

    const setTheme = (themeName: string) => {
        document.documentElement.className = document.documentElement.className
            .split(' ')
            .filter((cls) => cls === 'dark' || cls === 'light')
            .join(' ')
        const theme = themes.find((t) => t === themeName)
        if (theme) {
            document.documentElement.classList.add(`theme-${theme}`)
            localStorage.setItem('theme', theme)
            activeTheme.value = theme
        }
    }

    const getTheme = () => {
        const themeName = localStorage.getItem('theme') || 'black'
        const theme = themes.find((t) => t === themeName)
        if (theme) {
            document.documentElement.classList.add(`theme-${theme}`)
            activeTheme.value = theme
        }
    }

    return {
        appSettings,
        loading,
        getAppSettings,
        updateAppSettings,
        currency,
        businessType,
        inventorySize,
        posRoutes,
        isAiConfig,
        themes,
        setTheme,
        getTheme,
        activeTheme,
    }
})
