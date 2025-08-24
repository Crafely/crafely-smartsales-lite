<script setup>
    import { Button } from '@/components/ui/button'
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuItem,
        DropdownMenuSeparator,
        DropdownMenuShortcut,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu'
    import { DotsHorizontalIcon } from '@radix-icons/vue'
    const props = defineProps({
        row: {
            type: Object,
            required: true,
        },
        labels: {
            type: Array,
            required: true,
        },
        setActiveProduct: {
            type: Function,
            required: true,
        },
    })
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button
                variant="ghost"
                class="flex h-8 w-8 p-0 data-[state=open]:bg-muted"
            >
                <DotsHorizontalIcon class="h-4 w-4" />
                <span class="sr-only">Open menu</span>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent align="end" class="w-[160px]">
            <DropdownMenuItem @click="setActiveProduct(row.original)">
                Details
            </DropdownMenuItem>
            <DropdownMenuItem>Make a copy</DropdownMenuItem>
            <DropdownMenuItem>Favorite</DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem>
                Delete
                <DropdownMenuShortcut>⌘⌫</DropdownMenuShortcut>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
