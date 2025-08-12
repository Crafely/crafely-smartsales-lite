<script setup lang="ts">
    import {
        CommandEmpty,
        CommandGroup,
        CommandItem,
        CommandList,
    } from '@/components/ui/command'
    import {
        TagsInput,
        TagsInputInput,
        TagsInputItem,
        TagsInputItemDelete,
        TagsInputItemText,
    } from '@/components/ui/tags-input'
    import {
        ComboboxAnchor,
        ComboboxContent,
        ComboboxInput,
        ComboboxPortal,
        ComboboxRoot,
    } from 'radix-vue'
    import { computed, watch, ref } from 'vue'

    const modelValue = defineModel<number[]>({
        default: () => [],
    })
    const open = ref(false)
    const searchTerm = ref('')

    type Props = {
        items: any[]
        itemKey?: string
        itemValue?: string
        placeholder?: string
    }

    const props = withDefaults(defineProps<Props>(), {
        itemKey: 'id',
        itemValue: 'name',
        placeholder: 'Select',
    })

    const emit = defineEmits<{
        onSelect: [value: number[]]
    }>()
    const filteredFrameworks = computed(() =>
        props.items.filter((i) => !modelValue.value.includes(i[props.itemKey]))
    )

    const selectedItems = ref<any[]>([])

    const handleSelect = (item: any) => {
        const value = item[props.itemKey]
        if (modelValue.value.includes(value)) {
            return
        }
        searchTerm.value = ''
        modelValue.value.push(value)
        emit('onSelect', modelValue.value)
        if (filteredFrameworks.value.length === 0) {
            open.value = false
        }
    }

    const removeItem = (item: any) => {
        const value = item[props.itemKey]
        const updatedTags = modelValue.value.filter((tag) => tag !== value)
        emit('onSelect', updatedTags)
    }

    watch(
        modelValue,
        () => {
            selectedItems.value = props.items.filter((i) =>
                modelValue.value.includes(i[props.itemKey])
            )
        },
        { immediate: true }
    )
</script>

<template>
    <TagsInput class="px-0 gap-0 w-full">
        <div class="flex gap-2 flex-wrap items-center px-3">
            <TagsInputItem
                v-for="item in selectedItems"
                :key="item"
                :value="item[itemValue]"
            >
                <TagsInputItemText />
                <TagsInputItemDelete @click="removeItem(item)" />
            </TagsInputItem>
        </div>

        <ComboboxRoot
            v-model="modelValue"
            v-model:open="open"
            v-model:search-term="searchTerm"
            class="w-full"
        >
            <ComboboxAnchor as-child>
                <ComboboxInput :placeholder="placeholder" as-child>
                    <TagsInputInput
                        class="w-full px-3"
                        :class="modelValue.length > 0 ? 'mt-2' : ''"
                        @keydown.enter.prevent
                    />
                </ComboboxInput>
            </ComboboxAnchor>

            <ComboboxPortal>
                <ComboboxContent>
                    <CommandList
                        position="popper"
                        class="w-[--radix-popper-anchor-width] rounded-md mt-2 border bg-popover text-popover-foreground shadow-md outline-none data-[state=open]:animate-in data-[state=closed]:animate-out data-[state=closed]:fade-out-0 data-[state=open]:fade-in-0 data-[state=closed]:zoom-out-95 data-[state=open]:zoom-in-95 data-[side=bottom]:slide-in-from-top-2 data-[side=left]:slide-in-from-right-2 data-[side=right]:slide-in-from-left-2 data-[side=top]:slide-in-from-bottom-2"
                    >
                        <CommandEmpty />
                        <CommandGroup>
                            <CommandItem
                                v-for="item in items"
                                :key="item[itemKey]"
                                :value="item[itemValue]"
                                @select.prevent="handleSelect(item)"
                            >
                                {{ item[itemValue] }}
                            </CommandItem>
                        </CommandGroup>
                    </CommandList>
                </ComboboxContent>
            </ComboboxPortal>
        </ComboboxRoot>
    </TagsInput>
</template>
