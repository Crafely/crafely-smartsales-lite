<template>
    <div class="flex items-center flex-wrap">
        <Badge
            variant="secondary"
            class="scale-90"
            v-for="category in assignedCategories"
            :key="category.id"
        >
            {{ category.name }}
        </Badge>
    </div>
</template>

<script setup>
    import { computed } from 'vue'
    import { Badge } from '@/components/ui/badge'
    import { useCategoryStore } from '@/stores/categoryStore'
    import { storeToRefs } from 'pinia'
    const categoryStore = useCategoryStore()
    const { categories } = storeToRefs(categoryStore)

    const props = defineProps({
        categories: {
            type: Array,
            required: true,
        },
    })

    const assignedCategories = computed(() =>
        categories.value.filter((category) =>
            props.categories.includes(category.id)
        )
    )
</script>
