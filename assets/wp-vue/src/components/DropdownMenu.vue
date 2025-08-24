<script setup lang="ts">
    import {
        NavigationMenu,
        NavigationMenuContent,
        NavigationMenuItem,
        NavigationMenuLink,
        NavigationMenuList,
        NavigationMenuTrigger,
        navigationMenuTriggerStyle,
    } from '@/components/ui/navigation-menu'

    const components: { title: string; href: string; description: string }[] = [
        {
            title: 'Alert Dialog',
            href: '/docs/components/alert-dialog',
            description:
                'A modal dialog that interrupts the user with important content and expects a response.',
        },
        {
            title: 'Hover Card',
            href: '/docs/components/hover-card',
            description:
                'For sighted users to preview content available behind a link.',
        },
        {
            title: 'Progress',
            href: '/docs/components/progress',
            description:
                'Displays an indicator showing the completion progress of a task, typically displayed as a progress bar.',
        },
        {
            title: 'Scroll-area',
            href: '/docs/components/scroll-area',
            description: 'Visually or semantically separates content.',
        },
        {
            title: 'Tabs',
            href: '/docs/components/tabs',
            description:
                'A set of layered sections of content—known as tab panels—that are displayed one at a time.',
        },
        {
            title: 'Tooltip',
            href: '/docs/components/tooltip',
            description:
                'A popup that displays information related to an element when the element receives keyboard focus or the mouse hovers over it.',
        },
    ]
</script>

<template>
    <NavigationMenu>
        <NavigationMenuList>
            <NavigationMenuItem>
                <NavigationMenuTrigger class="opacity-60"
                    >Module Store</NavigationMenuTrigger
                >
                <NavigationMenuContent>
                    <NavigationMenuLink
                        v-for="component in components"
                        :key="component.title"
                        :href="component.href"
                        class="p-10"
                    >
                        {{ component.title }}
                    </NavigationMenuLink>
                </NavigationMenuContent>
            </NavigationMenuItem>
        </NavigationMenuList>
    </NavigationMenu>
</template>
