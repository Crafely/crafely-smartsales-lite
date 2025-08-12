<script setup lang="ts">
    import { Button } from '@/components/ui/button'
    import {
        Command,
        CommandEmpty,
        CommandGroup,
        CommandInput,
        CommandItem,
        CommandList,
    } from '@/components/ui/command'
    import {
        Popover,
        PopoverContent,
        PopoverTrigger,
    } from '@/components/ui/popover'
    import { cn } from '@/lib/utils'
    import { CaretSortIcon, AvatarIcon, CheckIcon } from '@radix-icons/vue'
    import { computed, ref } from 'vue'

    const props = withDefaults(
        defineProps<{
            modelValue: any
            items: any[]
            placeholder?: string
            emptyText?: string
            itemValue?: string
            itemLabel?: string
        }>(),
        {
            placeholder: 'Select an option...',
            emptyText: 'Nothing found.',
            itemValue: 'id',
            itemLabel: 'name',
        }
    )

    const emit = defineEmits<{
        (e: 'update:modelValue', value: string): void
        (e: 'onSelect', value: any): void
    }>()
    const open = ref(false)
    const updateValue = (value: any) => {
        emit('onSelect', value)
        emit('update:modelValue', value[props.itemValue])
        open.value = false
    }

    const selectedItem = computed(() => {
        return props.items.find(
            (item) => item[props.itemValue] === props.modelValue
        )
    })
</script>

<template>
    <div class="relative w-full">
        <Popover v-model:open="open" class="w-full">
            <PopoverTrigger as-child class="w-full">
                <Button
                    variant="outline"
                    role="combobox"
                    :aria-expanded="open"
                    class="w-full"
                    :class="
                        cn(
                            'justify-start',
                            !modelValue && 'text-muted-foreground'
                        )
                    "
                >
                    <AvatarIcon class="mr-2 h-4 w-4 shrink-0 opacity-50" />
                    <slot name="selected" :item="selectedItem">
                        {{ selectedItem?.[itemLabel] || placeholder }}
                    </slot>
                    <CaretSortIcon
                        class="ml-auto h-4 w-4 shrink-0 opacity-50"
                    />
                </Button>
            </PopoverTrigger>
            <PopoverContent
                class="p-0"
                style="width: var(--radix-popover-trigger-width)"
            >
                <Command>
                    <CommandInput placeholder="Search..." />
                    <CommandEmpty>{{ emptyText }}</CommandEmpty>
                    <CommandList>
                        <CommandGroup>
                            <CommandItem
                                v-for="item in items"
                                :key="item[itemValue]"
                                :value="item[itemValue]"
                                @select="() => updateValue(item)"
                            >
                                <slot
                                    name="item"
                                    :item="item"
                                    :selected="item[itemValue] === modelValue"
                                >
                                    {{ item[itemLabel] }}
                                    <CheckIcon
                                        :class="
                                            cn(
                                                'ml-auto h-4 w-4',
                                                item[itemValue] === modelValue
                                                    ? 'opacity-100'
                                                    : 'opacity-0'
                                            )
                                        "
                                    />
                                </slot>
                            </CommandItem>
                        </CommandGroup>
                    </CommandList>
                </Command>
            </PopoverContent>
        </Popover>
    </div>
</template>
