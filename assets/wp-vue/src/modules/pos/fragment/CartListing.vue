<script setup lang="ts">
    defineProps<{
        carts: number
        activeCartId: number
    }>()
</script>
<template>
    <div>
        <template v-for="(cart, index) in carts">
            <slot
                :index="index"
                :carts="carts"
                :cart="cart"
                :cartNumber="index + 1"
                :id="cart.id"
                :isActive="activeCartId === cart.id"
            />
        </template>
    </div>
</template>
