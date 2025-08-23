import { ref, Ref, watch } from 'vue'
import {
    useRouter as coreRouter,
    useRoute as coreRoute,
    RouteRecordRaw,
    RouteLocationNormalizedLoadedGeneric,
} from 'vue-router'

interface RouteIds {
    productId: Ref<number>
    customerId: Ref<number>
    orderId: Ref<number>
    invoiceId: Ref<number>
    outletId: Ref<number>
    userId: Ref<number>
}

type UseRouteReturn = RouteIds & {
    route: RouteLocationNormalizedLoadedGeneric
    getChildRoutes: (routeName: string) => RouteRecordRaw[] | []
    routerReplace: (query: any, routeName?: string | null) => Promise<void>
    packageName: string | null | {}
}

export const useRouter = () => coreRouter()
export const useRoute = (): UseRouteReturn => {
    const route = coreRoute()
    const router = useRouter()
    const productId = ref()
    const customerId = ref<number>()
    const orderId = ref<number>()
    const invoiceId = ref<number>()
    const outletId = ref<number>()
    const userId = ref<number>()

    const getChildRoutes = (routeName: string): RouteRecordRaw[] | [] => {
        let pageRoute = route.matched.find((item) => item.name === routeName)
        return pageRoute?.children || []
    }

    const handleParams = ({ params, query }) => {
        productId.value = params.productId || query.productId
        customerId.value = params.customerId || query.customerId
        orderId.value = params.orderId || query.orderId
        invoiceId.value = params.invoiceId || query.invoiceId
        outletId.value = params.outletId || query.outletId
        userId.value = params.userId || query.userId
    }

    watch(() => route, handleParams, { immediate: true })

    return {
        route,
        orderId,
        invoiceId,
        outletId,
        productId,
        customerId,
        userId,
        routerReplace: async (
            query: any,
            routeName?: string | null
        ): Promise<void> => {
            await router.replace({
                name: routeName || route.name,
                query: { ...route.query, ...query },
            })
        },
        getChildRoutes,
        packageName: route?.meta?.packageName || 'free',
    }
}
