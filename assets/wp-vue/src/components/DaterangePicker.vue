<script setup lang="ts">
import type { DateRange } from 'reka-ui'
import { cn } from '@/lib/utils'

import { Button } from '@/components/ui/button'
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover'
import { RangeCalendar } from '@/components/ui/range-calendar'
import {
  CalendarDate,
  DateFormatter,
  getLocalTimeZone,
} from '@internationalized/date'
import { Calendar as CalendarIcon } from 'lucide-vue-next'
import { type Ref, ref, watch } from 'vue'

interface DateRangeProps {
  dateRange?: {
    start: CalendarDate | null
    end: CalendarDate | null
  }
}

const props = defineProps<DateRangeProps>()

const emit = defineEmits<{
  'update:dateRange': [{
    start: CalendarDate | null
    end: CalendarDate | null
  }]
}>()

const df = new DateFormatter('en-US', {
  dateStyle: 'medium',
})

const value = ref(props.dateRange || { start: null, end: null }) as Ref<DateRange>

// Watch for internal value changes and emit updates
watch(value, (newValue) => {
  emit('update:dateRange', {
    start: newValue.start,
    end: newValue.end,
  })
}, { deep: true })

// Watch for prop changes to update internal value
watch(() => props.dateRange, (newValue) => {
  if (newValue && (newValue.start !== value.value.start || newValue.end !== value.value.end)) {
    value.value = {
      start: newValue.start,
      end: newValue.end,
    }
  }
}, { deep: true })
</script>

<template>
  <Popover>
    <PopoverTrigger as-child>
      <Button
        variant="outline"
        :class="cn(
          'w-[280px] justify-start text-left font-normal',
          (!value || (!value.start && !value.end)) && 'text-muted-foreground',
        )"
      >
        <CalendarIcon class="mr-2 h-4 w-4" />
        <template v-if="value.start">
          <template v-if="value.end">
            {{ df.format(value.start.toDate(getLocalTimeZone())) }} - {{ df.format(value.end.toDate(getLocalTimeZone())) }}
          </template>

          <template v-else>
            {{ df.format(value.start.toDate(getLocalTimeZone())) }}
          </template>
        </template>
        <template v-else>
          Pick a date
        </template>
      </Button>
    </PopoverTrigger>
    <PopoverContent class="w-auto p-0">
      <RangeCalendar v-model="value" initial-focus :number-of-months="2" />
    </PopoverContent>
  </Popover>
</template>