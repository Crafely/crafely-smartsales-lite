import type { RouteRecordRaw } from 'vue-router'
import { systemRoutes } from '@/system/routes'
import { Bot } from 'lucide-vue-next'
import { Settings2, Wrench } from 'lucide-vue-next'

export const freeRoutes: RouteRecordRaw[] = [
    {
        path: '/',
        name: 'app.dashboard',
        component: () => import('@/modules/dashboard/Index.vue'),
        meta: {
            title: 'Dashboard',
            accessibleTo: [
                'administrator',
                'aipos_outlet_manager',
                'aipos_shop_manager',
            ],
            order: 1,
        },
    },
    {
        path: '/product',
        name: 'app.product',
        component: () => import('@/modules/product/Index.vue'),
        meta: {
            title: 'Product',
            accessibleTo: [
                'administrator',
                'aipos_outlet_manager',
                'aipos_shop_manager',
            ],
            order: 2,
        },
    },
    {
        path: '/customer',
        name: 'app.customer',
        component: () => import('@/modules/customer/Index.vue'),
        meta: {
            title: 'Customer',
            accessibleTo: [
                'administrator',
                'aipos_cashier',
                'aipos_outlet_manager',
                'aipos_shop_manager',
            ],
            order: 4,
        },
    },
    {
        path: '/pos',
        name: 'app.pos',
        component: () => import('@/modules/pos/Index.vue'),
        meta: {
            accessibleTo: [
                'administrator',
                'aipos_cashier',
                'aipos_outlet_manager',
            ],
            title: 'POS',
            order: 3,
        },
    },
    {
        path: '/order',
        name: 'app.order',
        component: () => import('@/modules/order/Index.vue'),
        meta: {
            accessibleTo: ['all'],
            title: 'Order',
            order: 5,
        },
    },
    {
        path: '/ecosystem',
        name: 'app.ecosystem',
        component: () => import('@/modules/ecosystem/Index.vue'),
        redirect: { name: 'app.ecosystem.outlet' },
        meta: {
            accessibleTo: ['administrator'],
            title: 'Ecosystem',
            order: 6,
        },
        children: [
            {
                path: 'outlet',
                name: 'app.ecosystem.outlet',
                component: () => import('@/modules/ecosystem/outlet/Index.vue'),
                meta: {
                    title: 'Outlet',
                    accessibleTo: ['administrator'],
                    isChild: true,
                },
            },
            {
                path: 'users',
                name: 'app.ecosystem.user',
                component: () => import('@/modules/ecosystem/user/Index.vue'),
                meta: {
                    title: 'Users',
                    isChild: true,
                },
            },
        ],
    },
    {
        path: '/invoice',
        name: 'app.invoice',
        component: () => import('@/modules/invoice/Index.vue'),
        meta: {
            accessibleTo: ['all'],
            title: 'Invoice',
            order: 7,
        },
    },
    {
        path: '/settings',
        name: 'app.settings',
        component: () => import('@/modules/settings/Index.vue'),
        redirect: { name: 'app.settings.general' },
        children: [
            {
                path: 'general-settings',
                name: 'app.settings.general',
                component: () => import('@/modules/settings/general/Index.vue'),
                meta: {
                    accessibleTo: ['administrator'],
                    title: 'General Settings',
                    icon: Settings2,
                    isChild: true,
                },
            },
            {
                path: 'setup',
                name: 'app.settings.setup',
                component: () => import('@/modules/settings/setup/Index.vue'),
                meta: {
                    accessibleTo: ['administrator'],
                    title: 'Setup Settings',
                    icon: Wrench,
                    isChild: true,
                },
            },
            {
                path: 'aiConfig',
                name: 'app.settings.aiConfig',
                component: () =>
                    import('@/modules/settings/aiConfig/Index.vue'),
                meta: {
                    accessibleTo: ['administrator'],
                    title: 'Ai Config Settings',
                    icon: Bot,
                    isChild: true,
                },
            },
        ],
    },
    ...systemRoutes,
]
