export const systemRoutes = [
    {
        path: '/permission-deny',
        name: 'permission.deny',
        component: () => import('@/system/Permission.vue'),
    },
    {
        path: '/:pathMatch(.*)*',
        name: 'NotFound',
        component: () => import('@/system/404.vue'),
    },
]
