<script lang="ts" setup>
    import { Address } from '@/types'
    import { startCase } from 'lodash'
    defineProps<{
        address: Address
    }>()
</script>

<template>
    <div class="grid auto-rows-max gap-3">
        <address class="grid gap-0.5 not-italic">
            <div
                v-for="(key, value) in address || []"
                :key="key"
                class="flex items-center justify-between"
            >
                <dt class="text-muted-foreground">{{ startCase(value) }}:</dt>
                <dd>{{ key }}</dd>
            </div>
        </address>
    </div>
</template>
