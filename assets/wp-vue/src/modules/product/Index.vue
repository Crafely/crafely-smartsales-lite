<script lang="ts" setup>
    import {
        DataTable,
        DataTableToolbar,
        DataTableSearch,
        DataTableViewOptions,
        DataTableFacetedFilter,
    } from '@/components/dataTable'
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs'
    import AppLayout from '@/layout/AppLayoutWithResize.vue'
    import StickyFooterLayout from '@/layout/StickyFooterLayout.vue'
    import ProductDetails from './fragment/ProductDetails.vue'
    import ProductForm from './fragment/ProductForm.vue'
    import { ConfirmDialog, useConfirmDialog } from '@/components/confirmDialog'
    import DataTableBulkAction from './fragment/DataTableBulkAction.vue'
    import { statuses, columns } from './productConfig'
    import { useProduct } from './useProduct'
    import { useProductAi } from './useProduct.ai'
    import { useCategoryStore } from '@/stores/categoryStore'
    import { storeToRefs } from 'pinia'
    import Loader from '@/components/Loader.vue'
    import GenerateProductForm from './fragment/GenerateProductForm.vue'
    import { useAppStore } from '@/stores/appStore'
    const categoryStore = useCategoryStore()
    const { categoryOptions } = storeToRefs(categoryStore)
    const {
        subMenuTabs,
        products,
        pagination,
        activeTab,
        activeProduct,
        switchTab,
        getProducts,
        createProduct,
        generateProduct,
        suggestPrompts,
        updateProduct,
        deleteProduct,
        deleteBulkProduct,
        getSingleProduct,
        loading,
    } = useProduct()

    const { generate_description } = useProductAi()

    const { deleting, showConfirmDialog, handleDelete, confirmDelete } =
        useConfirmDialog()
    const appStore = useAppStore()
    const { isAiConfig } = storeToRefs(appStore)
</script>

<template>
    <AppLayout activeRouteName="app.product">
        <Loader :loading="loading"></Loader>
        <div class="p-8">
            <DataTable
                :data="products"
                :columns="columns"
                :pagination="pagination"
                :reFetch="getProducts"
                :activeProduct="activeProduct"
                @rowClick="(_product) => getSingleProduct(_product.id)"
            >
                <template #toolbar="{ table }">
                    <DataTableToolbar :table="table">
                        <template #search>
                            <DataTableSearch
                                placeholder="Product search..."
                                :table="table"
                            />
                        </template>
                        <template #filter>
                            <DataTableFacetedFilter
                                v-if="table.getColumn('status')"
                                :column="table.getColumn('status')"
                                title="Status"
                                :options="statuses"
                            />
                            <DataTableFacetedFilter
                                v-if="table.getColumn('categories')"
                                :column="table.getColumn('categories')"
                                title="Categories"
                                :options="categoryOptions"
                            />
                            <DataTableBulkAction
                                :table="table"
                                :deleteBulkProduct="deleteBulkProduct"
                            />
                        </template>
                        <template #viewOption>
                            <DataTableViewOptions :table="table" />
                        </template>
                    </DataTableToolbar>
                </template>
            </DataTable>
        </div>
        <template #sidebar>
            <Tabs
                class="w-full"
                v-model="activeTab"
                @update:model-value="switchTab"
            >
                <StickyFooterLayout>
                    <template #header>
                        <TabsList class="w-full rounded-none">
                            <TabsTrigger
                                v-for="tab in subMenuTabs"
                                :key="tab.value"
                                class="data-[state=active]:shadow-none data-[state=active]:bg-transparent"
                                :value="tab.value"
                            >
                                <div
                                    class="flex items-center gap-2 transition-opacity duration-200 hover:opacity-80"
                                >
                                    <component :is="tab.icon" class="w-4 h-4" />
                                    {{ tab.label }}
                                </div>
                            </TabsTrigger>
                        </TabsList>
                    </template>

                    <TabsContent value="details" class="mt-0">
                        <ProductDetails
                            v-if="activeProduct"
                            :product="activeProduct"
                            :handleDelete="handleDelete"
                        />
                    </TabsContent>
                    <TabsContent value="edit" class="mt-0">
                        <ProductForm
                            :submitForm="updateProduct"
                            :product="activeProduct"
                            :isAiConfig="isAiConfig"
                            ref="productEditFormRef"
                            @generateDescription="generate_description"
                        />
                    </TabsContent>
                    <TabsContent value="create" class="mt-0">
                        <GenerateProductForm
                            v-if="isAiConfig"
                            :submitForm="generateProduct"
                            :suggestPrompts="suggestPrompts"
                            ref="generatePromptRef"
                        ></GenerateProductForm>
                        <ProductForm
                            :submitForm="createProduct"
                            :isAiConfig="isAiConfig"
                            ref="productCreateFormRef"
                            @generateDescription="generate_description"
                        />
                    </TabsContent>
                </StickyFooterLayout>
            </Tabs>
        </template>
        <ConfirmDialog
            title="Are you sure you want to delete this product?"
            description="This action will move the product to the trash and can be restored within 30 days."
            v-model="showConfirmDialog"
            :deleting="deleting"
            @confirm="confirmDelete(deleteProduct)"
        />
    </AppLayout>
</template>
