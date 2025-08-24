<script lang="ts" setup>
    import {
        Card,
        CardTitle,
        CardHeader,
        CardDescription,
        CardContent,
    } from '@/components/ui/card'
    import { Trash2 } from 'lucide-vue-next'
    import { Button } from '@/components/ui'
    import { Separator } from '@/components/ui/separator'
    import UserDescriptionList from './UserDescriptionList.vue'
    import UserInformationSection from './UserInformationSection.vue'
    import UserPermissions from './UserPermissions.vue'
    import UserOutlet from './UserOutlet.vue'
    import { formatShortDate } from '@/utils'
    import type { User } from '@/types'
    import startCase from 'lodash/startCase'
    import { computed } from 'vue'
    const props = defineProps<{
        user?: User
        handleDelete: (user: User) => void
    }>()

    const userRoles = computed(() =>
        props.user?.roles.map((role) => startCase(role)).join(', ')
    )
</script>

<template>
    <Card class="overflow-hidden rounded-none shadow-none border-none">
        <CardHeader class="flex flex-row justify-between bg-muted/50">
            <div class="rounded-md overflow-hidden border-2 mr-4 w-16 h-16">
                <img
                    :src="user?.avatar || ''"
                    class="w-full h-full object-cover object-center"
                    alt=""
                />
            </div>
            <div class="flex-1">
                <CardTitle class="text-lg">
                    {{ user?.name || '' }}
                </CardTitle>
                <div class="flex items-center gap-2">
                    <div>
                        <CardDescription>
                            Email:
                            {{ user?.email || '' }}
                        </CardDescription>

                        <CardDescription>
                            Status:
                            <span
                                :class="{
                                    'text-green-500': user?.status === 'active',
                                    'text-red-500': user?.status === 'inactive',
                                }"
                                class="capitalize"
                            >
                                {{ user?.status }}
                            </span>
                        </CardDescription>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <Button
                            size="sm"
                            variant="outline"
                            class="hover:text-red-400"
                            @click="handleDelete(user)"
                        >
                            <Trash2 class="w-4 h-4" /> Delete
                        </Button>
                    </div>
                </div>
            </div>
        </CardHeader>
        <CardContent class="p-6 text-sm">
            <UserInformationSection title="Personal Information">
                <UserDescriptionList
                    label="First Name"
                    :value="user?.first_name"
                />
                <UserDescriptionList
                    label="Last Name"
                    :value="user?.last_name"
                />
                <UserDescriptionList label="Roles" :value="userRoles" />
                <UserDescriptionList
                    label="Join Date"
                    :value="formatShortDate(user?.created_at)"
                />
                <UserDescriptionList
                    label="Last Login"
                    :value="user?.last_login"
                />
            </UserInformationSection>
        </CardContent>
        <Separator class="mb-6" />
        <CardContent class="text-sm">
            <UserInformationSection title="User Outlet">
                <UserOutlet :outlet="user?.outlet" />
            </UserInformationSection>
        </CardContent>
        <Separator class="mb-4" />
        <CardContent class="text-sm">
            <UserInformationSection title="User Permission">
                <UserPermissions :permissions="user?.permissions" />
            </UserInformationSection>
        </CardContent>
    </Card>
</template>
