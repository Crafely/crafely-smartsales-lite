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
                'csmsl_pos_outlet_manager',
                'csmsl_pos_shop_manager',
            ],
            order: 1,
        },
    },
    {
        path: '/products',
        name: 'app.products',
        component: () => import('@/modules/product/Index.vue'),
        meta: {
            title: 'Products',
            accessibleTo: [
                'administrator',
                'csmsl_pos_outlet_manager',
                'csmsl_pos_shop_manager',
            ],
            order: 2,
        },
    },
    {
        path: '/customers',
        name: 'app.customers',
        component: () => import('@/modules/customer/Index.vue'),
        meta: {
            title: 'Customers',
            accessibleTo: [
                'administrator',
                'csmsl_pos_cashier',
                'csmsl_pos_outlet_manager',
                'csmsl_pos_shop_manager',
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
                'csmsl_pos_cashier',
                'csmsl_pos_outlet_manager',
            ],
            title: 'POS',
            order: 3,
        },
    },
    {
        path: '/orders',
        name: 'app.orders',
        component: () => import('@/modules/order/Index.vue'),
        meta: {
            accessibleTo: ['all'],
            title: 'Orders',
            order: 5,
        },
    },
    {
        path: '/ecosystem',
        name: 'app.ecosystem',
        component: () => import('@/modules/ecosystem/Index.vue'),
        redirect: { name: 'app.ecosystem.outlets' },
        meta: {
            accessibleTo: ['administrator'],
            title: 'Ecosystem',
            order: 6,
        },
        children: [
            {
                path: 'outlets',
                name: 'app.ecosystem.outlets',
                component: () => import('@/modules/ecosystem/outlet/Index.vue'),
                meta: {
                    title: 'Outlets',
                    accessibleTo: ['administrator'],
                    isChild: true,
                },
            },
            {
                path: 'staffs',
                name: 'app.ecosystem.staff',
                component: () => import('@/modules/ecosystem/user/Index.vue'),
                meta: {
                    title: 'Staffs',
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
