<script setup lang="ts">
    import { Sheet, SheetTrigger, SheetContent } from '@/components/ui/sheet'
    import { AlignLeft, AlignRight } from 'lucide-vue-next'
    import { Button } from '@/components/ui/button'
    import {
        Accordion,
        AccordionContent,
        AccordionItem,
        AccordionTrigger,
    } from '@/components/ui/accordion'

    defineProps<{
        activeRouteName: String
        routes: any[]
        isRight?: boolean
    }>()
</script>
<template>
    <div class="flex md:hidden items-center justify-between">
        <Sheet>
            <SheetTrigger>
                <component :is="isRight ? AlignRight : AlignLeft" />
            </SheetTrigger>
            <SheetContent side="left">
                <nav class="flex flex-col space-y-2 mt-6">
                    <template v-for="route in routes" :key="route.path">
                        <Accordion
                            v-if="route.children?.length"
                            type="single"
                            collapsible
                        >
                            <AccordionItem value="item-1" class="border-none">
                                <AccordionTrigger class="py-2 pl-4">
                                    {{ route.meta?.title }}
                                </AccordionTrigger>
                                <AccordionContent class="pl-4 py-1">
                                    <template
                                        v-for="child in route.children"
                                        :key="child.path"
                                    >
                                        <router-link :to="{ name: child.name }">
                                            <Button
                                                variant="ghost"
                                                class="w-full justify-start mt-1"
                                                :class="[
                                                    $route.meta?.title ==
                                                    child.meta?.title
                                                        ? 'bg-accent text-accent-foreground'
                                                        : ' ',
                                                ]"
                                                >{{ child.meta?.title }}</Button
                                            >
                                        </router-link>
                                    </template>
                                </AccordionContent>
                            </AccordionItem>
                        </Accordion>

                        <router-link v-else :to="{ name: route.name }">
                            <Button
                                variant="ghost"
                                class="w-full justify-start"
                                :class="[
                                    activeRouteName === route.name
                                        ? 'bg-accent text-accent-foreground'
                                        : ' ',
                                ]"
                                >{{ route.meta?.title }}</Button
                            >
                        </router-link>
                    </template>
                </nav>
            </SheetContent>
        </Sheet>
    </div>
</template>
