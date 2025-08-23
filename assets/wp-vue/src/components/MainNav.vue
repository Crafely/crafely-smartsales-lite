<script setup lang="ts">
    import { cn } from '@/lib/utils'
    import DropdownMenu from './DropdownMenu.vue'
    import {
        Menubar,
        MenubarContent,
        MenubarItem,
        MenubarMenu,
        MenubarSub,
        MenubarSubContent,
        MenubarSubTrigger,
        MenubarTrigger,
    } from '@/components/ui/menubar'

    defineProps<{
        activeRouteName: String
        routes: any[]
    }>()
</script>

<template>
    <nav
        :class="
            cn(
                'hidden md:flex items-center space-x-4 lg:space-x-6',
                $attrs.class ?? ''
            )
        "
    >
        <Menubar class="border-none shadow-none bg-transparent space-x-6">
            <template v-for="route in routes" :key="route.path">
                <!-- For routes with children (nested menu) -->
                <MenubarMenu v-if="route.children?.length">
                    <MenubarTrigger
                        :class="[
                            'text-sm font-medium transition-all hover:opacity-100',
                            activeRouteName === route.name
                                ? 'text-primary opacity-100'
                                : 'opacity-60',
                        ]"
                    >
                        {{ route.meta?.title }}
                    </MenubarTrigger>
                    <MenubarContent>
                        <template
                            v-for="child in route.children"
                            :key="child.path"
                        >
                            <router-link :to="{ name: child.name }">
                                <MenubarItem>{{
                                    child.meta?.title
                                }}</MenubarItem>
                            </router-link>
                        </template>
                    </MenubarContent>
                </MenubarMenu>

                <!-- For regular routes without children -->
                <router-link
                    v-else
                    :to="{ name: route.name }"
                    class="text-sm font-medium transition-all hover:opacity-100"
                    :class="[
                        activeRouteName === route.name
                            ? 'text-primary opacity-100'
                            : 'opacity-60',
                    ]"
                >
                    {{ route.meta?.title }}
                </router-link>
            </template>
        </Menubar>
    </nav>
</template>
