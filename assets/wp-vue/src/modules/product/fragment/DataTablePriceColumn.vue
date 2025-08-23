<script setup lang="ts">
    import { formatAmount } from '@/utils'
    import { computed } from 'vue'
    const props = defineProps<{
        row: any
    }>()

    const regularPrice = computed(() => props.row.getValue('regular_price'))
    const salePrice = computed(() => props.row.original['sale_price'])
    const currency = computed(() => props.row.original['currency'])
</script>

<template>
    <div class="flex flex-col gap-1">
        <template v-if="salePrice">
            <span class="text-sm line-through text-muted-foreground">
                {{ formatAmount(regularPrice, currency) }}
            </span>
            <span class="font-medium">
                {{ formatAmount(salePrice, currency) }}
            </span>
        </template>
        <span v-else class="font-medium">
            {{ formatAmount(regularPrice, currency) }}
        </span>
    </div>
</template>
