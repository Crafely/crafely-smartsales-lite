<script setup lang="ts">
    import { Moon, Sun } from 'lucide-vue-next'
    import { useColorMode } from '@vueuse/core'
    import { Button } from '@/components/ui/button'
    const mode = useColorMode()
</script>

<template>
    <div>
        <Button
            variant="link"
            size="icon"
            class="w-7 h-7 text-text-accent-foreground"
            v-show="mode === 'light'"
            @click="mode = 'dark'"
        >
            <Moon />
        </Button>
        <Button
            variant="link"
            size="icon"
            class="w-7 h-7 text-text-accent-foreground"
            v-show="mode === 'dark'"
            @click="mode = 'light'"
        >
            <Sun />
        </Button>
    </div>
</template>
