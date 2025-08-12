import { h } from 'vue'
import { freeRoutes } from '@/packages/free/routes'

export const useSetting = () => {
    const settingsRoute = freeRoutes.find(
        (route) => route.name === 'app.settings'
    )

    const settingItems = (settingsRoute?.children || []).map((route) => ({
        value: route.name,
        title: route.meta?.title ?? route.name,
        icon: route.meta?.icon ? h(route.meta.icon) : null,
    }))

    return {
        settingItems,
    }
}
