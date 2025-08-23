<script setup>
import { cn } from '@/lib/utils'
import { Primitive } from 'radix-vue'
import { buttonVariants } from '.'
import { ReloadIcon } from '@radix-icons/vue'
const props = defineProps({
    variant: { type: null, required: false },
    size: { type: null, required: false },
    class: { type: null, required: false },
    asChild: { type: Boolean, required: false },
    as: { type: null, required: false, default: 'button' },
    loading: { type: Boolean, required: false },
    icon: { type: Object, required: false },
})
</script>

<template>
    <Primitive
        :as="as"
        :as-child="asChild"
        :class="cn(buttonVariants({ variant, size }), props.class)"
        :disabled="loading"
    >
        <component
            :is="loading ? ReloadIcon : icon"
            :class="[
                loading ? 'animate-spin' : '',
                loading || icon ? 'w-4 h-4 mr-1' : '',
            ]"
        />
        <slot />
    </Primitive>
</template>
