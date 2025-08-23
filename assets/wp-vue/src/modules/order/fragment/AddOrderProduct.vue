<script lang="ts" setup>
    import { ref, onMounted } from 'vue'
    import { SearchIcon, PlusCircle, X } from 'lucide-vue-next'
    import { LineItem, Product } from '@/types'
    import { Button } from '@/components/ui/button'
    import { Input } from '@/components/ui/input'
    import { ScrollArea } from '@/components/ui/scroll-area'
    import { storeToRefs } from 'pinia'
    import { useProductStore } from '@/stores/productStore'
    const productStore = useProductStore()
    const { products, searchQuery } = storeToRefs(productStore)

    const emit = defineEmits<{
        (e: 'addProduct', product: LineItem): void
        (e: 'close'): void
    }>()
    const showProductPanel = ref(true)

    const filteredProducts = async () => {
        await productStore.getProductsByQuery()
    }

    const handleAddProduct = (product: Product) => {
        emit('addProduct', {
            name: product.name,
            product_id: product.id,
            quantity: 1,
            price: product.price,
            total: product.price,
        })
    }

    const handleClose = () => {
        showProductPanel.value = false
        searchQuery.value = ''
        emit('close')
    }

    onMounted(() => {
        productStore.getProducts()
    })
</script>

<template>
    <div class="relative w-full max-w-md">
        <!-- Search Bar -->
        <div
            class="relative rounded-md border bg-popover shadow-sm"
            :class="{ 'rounded-b-none': showProductPanel }"
        >
            <SearchIcon
                class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground"
            />
            <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search products..."
                class="pl-10 pr-10 py-5 rounded-none border-none focus-visible:ring-0"
                @input="filteredProducts"
            />
            <Button
                variant="ghost"
                size="icon"
                type="button"
                class="absolute right-2 top-1/2 transform -translate-y-1/2"
                @click="handleClose"
            >
                <X class="h-4 w-4" />
            </Button>
        </div>

        <!-- Product List -->
        <div
            v-if="showProductPanel && searchQuery.length > 0"
            class="absolute z-50 right-0 top-[30] w-full bg-white rounded-md border border-t-0 rounded-t-none bg-popover shadow-sm"
        >
            <ScrollArea class="h-[300px]">
                <div
                    v-for="product in products || []"
                    :key="product.id"
                    class="flex items-center justify-between px-2 p-1 hover:bg-muted"
                >
                    <div class="flex-1">
                        <h4 class="text-xs font-medium">
                            {{ product.name }}
                        </h4>
                        <p class="text-xs text-muted-foreground">
                            Price: ${{ product.price }}
                        </p>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        type="button"
                        @click="handleAddProduct(product)"
                    >
                        <PlusCircle class="h-4 w-4" />
                    </Button>
                </div>
            </ScrollArea>
        </div>
    </div>
</template>
