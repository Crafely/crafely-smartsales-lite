<script setup lang="ts">
    import {
        Card,
        CardContent,
        CardDescription,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card'
    import { Badge } from '@/components/ui/badge'
    import { Button } from '@/components/ui/button'
    import { Separator } from '@/components/ui/separator'
    import { Product } from '@/types'
    import { formatAmount } from '@/utils'
    import { TrashIcon } from '@radix-icons/vue'
    import { Trash2 } from 'lucide-vue-next'
    import { storeToRefs } from 'pinia'
    import { useCategoryStore } from '@/stores/categoryStore'
    import { computed } from 'vue'
    const categoryStore = useCategoryStore()

    const { categories } = storeToRefs(categoryStore)

    const props = defineProps<{
        product: Product
        handleDelete: (product: Product) => void
    }>()

    const productCategories = computed(() =>
        categories.value.filter((category) =>
            props.product.categories.includes(category.id)
        )
    )
</script>

<template>
    <Card class="overflow-hidden rounded-none shadow-none border-none">
        <CardHeader class="flex flex-row justify-between bg-muted/50">
            <div class="rounded-md overflow-hidden border-2 mr-4 w-24 h-24">
                <img
                    :src="product.image_url"
                    class="w-full h-full object-cover object-center"
                    alt=""
                />
            </div>
            <div class="flex-1">
                <CardTitle class="text-lg">
                    {{ product.name }}
                </CardTitle>
                <div class="flex items-center gap-2">
                    <div>
                        <CardDescription>
                            Price:
                            {{ formatAmount(product.price, product.currency) }}
                        </CardDescription>
                        <CardDescription>
                            Stock:
                            {{ product.stock || 'Out of stock' }}
                        </CardDescription>
                    </div>
                    <div class="ml-auto flex items-center gap-2">
                        <Button
                            size="sm"
                            variant="outline"
                            class="hover:text-red-400"
                            @click="handleDelete(product)"
                        >
                            <Trash2 class="w-4 h-4" /> Delete
                        </Button>
                    </div>
                </div>

                <CardDescription>
                    SKU:
                    {{ product.sku }}
                </CardDescription>
            </div>
        </CardHeader>
        <CardContent class="p-6 text-sm">
            <div class="grid gap-3">
                <div class="font-semibold">Product Details</div>
                <ul class="grid gap-3">
                    <li
                        v-if="product.categories.length"
                        class="flex items-center gap-x-2"
                    >
                        <span class="text-muted-foreground min-w-[117px]">
                            Categories:
                        </span>
                        <div class="flex gap-2">
                            <Badge
                                variant="secondary"
                                v-for="category in productCategories"
                                :key="category.id"
                            >
                                {{ category.name }}
                            </Badge>
                        </div>
                    </li>
                    <li class="flex items-center gap-x-2">
                        <span class="text-muted-foreground min-w-[117px]">
                            Status:
                        </span>
                        <div class="flex gap-2">
                            <Badge variant="outline">
                                {{ product.status }}
                            </Badge>
                        </div>
                    </li>
                    <li class="flex gap-x-2" v-if="product.short_description">
                        <span class="text-muted-foreground text-nowrap">
                            Short Description:
                        </span>
                        <div v-html="product.short_description"></div>
                    </li>
                </ul>
                <Separator class="my-2" />
                <div v-html="product.description" class="text-sm"></div>
            </div>
        </CardContent>
    </Card>
</template>
