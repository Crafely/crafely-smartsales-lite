<template>
    <div class="grid auto-rows-max gap-3">
        <div class="grid gap-0.5 not-italic space-y-4">
            <div v-for="(key, value) in permissions || []" :key="key">
                <UserDescriptionList :label="startCase(value)">
                    <component
                        :class="[key ? 'text-green-400' : 'text-red-400']"
                        class="w-4 h-4"
                        :is="key ? CheckCircle : XCircle"
                    ></component>
                </UserDescriptionList>
            </div>
        </div>
    </div>
</template>

<script lang="ts" setup>
    import { CheckCircle, XCircle } from 'lucide-vue-next'
    import startCase from 'lodash/startCase'
    import UserDescriptionList from './UserDescriptionList.vue'
    import type { User } from '@/types'
    defineProps<{
        permissions: User['permissions']
    }>()
</script>
