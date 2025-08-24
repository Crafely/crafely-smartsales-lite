<script lang="ts" setup>
    import { ref, computed } from 'vue'
    import { SearchIcon, ScanLine } from 'lucide-vue-next'
    import { Product } from '@/types'
    import { useSalesStore } from '@/stores/salesStore'
    import { Button } from '@/components/ui/button'
    import { Input } from '@/components/ui/input'
    const store = useSalesStore()
    const searchQuery = ref('')
    const showProductPanel = ref(false)
    const props = defineProps<{
        products: Product[]
    }>()

    const filteredProducts = computed(() => {
        if (!searchQuery.value) return props.products
        const query = searchQuery.value.toLowerCase()
        return props.products.filter((product) =>
            product.name.toLowerCase().includes(query)
        )
    })

    const hideProductPanel = () => {
        setTimeout(() => {
            showProductPanel.value = false
        }, 200)
    }
</script>

<template>
    <div class="relative max-w-2xl mx-auto">
        <!-- Search Bar -->
        <div class="relative">
            <SearchIcon
                class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground"
            />
            <Input
                v-model="searchQuery"
                type="text"
                placeholder="Search..."
                class="pl-10 py-5 text-lg"
                @focus="showProductPanel = true"
                @blur="hideProductPanel"
            />
            <Button
                variant="ghost"
                class="absolute right-1 top-1/2 transform -translate-y-1/2"
            >
                <ScanLine class="h-4 w-4 text-muted-foreground" />
            </Button>
        </div>

        <!-- Product List -->
        <div
            class="absolute w-full mt-2 z-50 bg-background rounded-md border border-input shadow-lg max-h-[300px] overflow-auto"
            v-if="showProductPanel"
        >
            <div v-if="filteredProducts.length">
                <div
                    v-for="product in filteredProducts"
                    :key="product.id"
                    class="flex items-center gap-3 p-2 hover:bg-accent cursor-pointer"
                    @click="store.addToCart(product)"
                >
                    <img
                        :src="product.image_url || '/assets/products/01.jpeg'"
                        :alt="product.name"
                        class="h-12 w-12 object-cover rounded-md"
                    />
                    <div class="flex-1">
                        <h3 class="text-sm font-medium">
                            {{ product.name }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            BDT {{ product.price.toFixed(2) }}
                        </p>
                    </div>
                </div>
            </div>
            <div v-else class="p-5 text-sm text-center">
                Search result does not matched
            </div>
        </div>
    </div>
</template>
