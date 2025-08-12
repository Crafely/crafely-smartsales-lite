<script setup lang="ts">
    import { computed } from 'vue'
    import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
    import { Button } from '@/components/ui/button'
    import { extractInitials, getPublicPath } from '@/utils'
    import {
        DropdownMenu,
        DropdownMenuContent,
        DropdownMenuLabel,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
    } from '@/components/ui/dropdown-menu'

    import { defineProps } from 'vue'

    const props = defineProps<{
        user: any
    }>()

    const authUser = computed(() => props.user)

    const userInitials = computed(() => extractInitials(authUser.value?.name))
</script>

<template>
    <DropdownMenu>
        <DropdownMenuTrigger as-child>
            <Button variant="ghost" class="relative h-8 w-8 rounded-full">
                <Avatar class="h-8 w-8">
                    <AvatarImage
                        :src="getPublicPath(`${user?.avatar}`)"
                        alt="user"
                    />
                    <AvatarFallback>{{ userInitials }}</AvatarFallback>
                </Avatar>
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-56" align="end">
            <DropdownMenuLabel class="font-normal flex">
                <div class="flex flex-col space-y-1">
                    <p class="text-sm font-medium leading-none">
                        {{ user?.name }}
                    </p>
                    <p class="text-xs leading-none text-muted-foreground">
                        {{ user?.email }}
                    </p>
                </div>
            </DropdownMenuLabel>
            <DropdownMenuSeparator />
            <slot />
        </DropdownMenuContent>
    </DropdownMenu>
</template>
