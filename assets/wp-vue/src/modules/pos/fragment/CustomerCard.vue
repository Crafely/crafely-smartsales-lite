<template>
    <div class="flex items-start p-4 border rounded">
        <div class="flex items-start gap-4 text-sm">
            <span
                class="inline-flex items-center justify-center font-normal text-foreground select-none shrink-0 bg-secondary overflow-hidden h-10 w-10 text-xs rounded-full"
                ><span>WS</span></span
            >
            <div class="grid gap-1">
                <div class="font-semibold">{{ customer.full_name }}</div>
                <div class="line-clamp-1 text-xs">
                    Phone: {{ customer.phone || 'N/A' }}
                </div>
                <div class="line-clamp-1 text-xs">
                    <span class="font-medium">Email:</span>
                    {{ customer.email }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
    import { Customer } from '@/types'
    defineProps<{ customer: Customer }>()
</script>
