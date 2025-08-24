<script setup lang="ts">
    import { Button } from '@/components/ui/button'

    const props = defineProps<{
        settingItems: {
            value: string
            title: string
            icon: any
        }[]
    }>()
</script>

<template>
    <ul class="space-y-1 flex gap-1 justify-start items-center md:block">
        <li v-for="(item, index) in settingItems" :key="index">
            <router-link :to="{ name: item.value }">
                <Button
                    :variant="'ghost'"
                    :class="[
                        'w-auto p-3 md:w-full !justify-start !text-base',
                        item.value === $route.name
                            ? 'bg-accent text-accent-foreground'
                            : '',
                    ]"
                >
                    <component :is="item.icon" class="w-4 h-4" />
                    <span
                        class="md:block"
                        :class="[
                            item.value === $route.name ? 'block' : 'hidden',
                        ]"
                        >{{ item.title }}</span
                    ></Button
                >
            </router-link>
        </li>
    </ul>
</template>
