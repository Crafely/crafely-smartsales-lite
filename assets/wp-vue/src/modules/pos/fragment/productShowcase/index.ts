import { defineAsyncComponent } from 'vue'

const components = {
    basic: defineAsyncComponent(() => import('./BasicShowcase.vue')),
    standard: defineAsyncComponent(() => import('./StandardShowcase.vue')),
    enterprise: defineAsyncComponent(() => import('./Enterprise.vue'))
}

export const ProductShowcase = components