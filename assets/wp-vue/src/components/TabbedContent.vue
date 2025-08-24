<script setup lang="ts">
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs'

    interface Tab {
        value: string
        label: string
    }

    defineProps<{
        tabs: Tab[]
    }>()
    const defaultValue = defineModel<string>()
</script>

<template>
    <Tabs :defaultValue="defaultValue" class="space-y-4">
        <div class="block space-y-4 md:flex gap-x-4 md:space-y-0">
            <TabsList>
                <TabsTrigger
                    v-for="tab in tabs"
                    :key="tab.value"
                    :value="tab.value"
                >
                    {{ tab.label }}
                </TabsTrigger>
            </TabsList>
            <slot name="afterTab" />
        </div>

        <TabsContent v-for="tab in tabs" :key="tab.value" :value="tab.value">
            <slot :currentTab="tab.value" />
        </TabsContent>
    </Tabs>
</template>
