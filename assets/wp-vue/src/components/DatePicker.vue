<script setup lang="ts">
    import { Calendar } from '@/components/ui/calendar'
    import {
        Popover,
        PopoverContent,
        PopoverTrigger,
    } from '@/components/ui/popover'
    import { cn } from '@/lib/utils'
    import {
        DateFormatter,
        parseDate,
        type DateValue,
        getLocalTimeZone,
    } from '@internationalized/date'
    import { CalendarIcon } from '@radix-icons/vue'
    import { computed, watch } from 'vue'

    const df = new DateFormatter('en-US', {
        dateStyle: 'long',
    })

    // const value = ref<DateValue>()
    // value.value.toDate(new Date('2/11/2022'))
    // const formatDate = (date: DateValue) => {
    //     if (!date) return '--/--/----'
    //     const d = date.toDate(getLocalTimeZone())
    //     return `${d.getFullYear()}-${d.getMonth() + 1}-${d.getDate()}`
    // }

    const dateModel = defineModel<string>()
    const props = defineProps<{
        label?: string
    }>()

    const value = computed<DateValue | undefined>({
        get: () => {
            if (!dateModel.value) {
                return
            }
            return parseDate(dateModel.value)
        },
        set: (value) => (dateModel.value = value?.toString()),
    })
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <div
                class="flex relative w-full items-center justify-between gap-1 h-auto text-nowrap cursor-pointer"
                :class="
                    cn(
                        'justify-start text-left font-medium',
                        !value && 'text-muted-foreground'
                    )
                "
            >
                <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                <label class="text-nowrap cursor-pointer">{{ label }}</label>
                {{
                    value
                        ? df.format(value.toDate(getLocalTimeZone()))
                        : '--/--/----'
                }}
            </div>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0">
            <Calendar v-model="value" initial-focus />
        </PopoverContent>
    </Popover>
</template>
