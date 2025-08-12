<script lang="ts" setup>
    import { ref, computed } from 'vue'
    import AppLayout from '@/layout/AppLayoutWithResize.vue'
    import StickyFooterLayout from '@/layout/StickyFooterLayout.vue'

    import {
        Card,
        CardContent,
        CardDescription,
        CardFooter,
        CardHeader,
        CardTitle,
    } from '@/components/ui/card'
    import { Button } from '@/components/ui/button'
    import { Input } from '@/components/ui/input'
    import {
        Tabs,
        TabsContent,
        TabsList,
        TabsTrigger,
    } from '@/components/ui/tabs'
    import { ScrollArea } from '@/components/ui/scroll-area'
    import {
        FormControl,
        FormField,
        FormItem,
        FormMessage,
    } from '@/components/ui/form'
    import {
        Select,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
    } from '@/components/ui/select'

    import {
        SearchIcon,
        PlusCircle,
        X,
        Mail,
        Printer,
        Send,
    } from 'lucide-vue-next'
    import { PlusCircledIcon } from '@radix-icons/vue'

    import Loader from '@/components/Loader.vue'
    import DatePicker from '@/components/DatePicker.vue'
    import Combobox from '@/components/ComboBox.vue'
    import AddressCard from './fragment/AddressCard.vue'
    import InvoiceListTable from './fragment/InvoiceListTable.vue'
    import ProductTable from './fragment/ProductTable.vue'
    import CustomerForm from '../customer/fragment/CustomerForm.vue'
    import { ConfirmDialog, useConfirmDialog } from '@/components/confirmDialog'
    import { formatAmount } from '@/utils'
    import { useInvoice } from './useInvoice'
    import PrintSection from '@/components/PrintSection.vue'
    import EditableText from '@/components/EditableText.vue'

    const {
        loading,
        hasOutlet,
        appSettings,
        selectedOutlet,
        selectedCustomer,
        invoiceOutlets,
        activeTab,
        subMenuTabs,
        customers,
        createCustomer,
        products,
        searchQuery,
        filteredProducts,
        handleCleare,
        handleAddProduct,
        handleRemove,
        itemTotalPrice,
        subTotalPrice,
        vatCalculate,
        finalTotal,
        invoiceForm,
        submitInvoice,
        updateInvoice,
        createNewInvoice,
        activeInvoice,
        deleteInvoice,
    } = useInvoice()

    const { deleting, showConfirmDialog, handleDelete, confirmDelete } =
        useConfirmDialog()

    const printSectionRef = ref()

    const isSmallScreen = computed(
        () => window.matchMedia('(max-width: 767px)').matches
    )

    const handlePrint = () => {
        printSectionRef.value?.printHandler()
    }
</script>

<template>
    <Loader :loading="loading"></Loader>
    <AppLayout activeRouteName="app.invoice">
        <div class="p-8" :style="{ zoom: isSmallScreen ? 0.6 : 1 }">
            <PrintSection ref="printSectionRef">
                <Card
                    class="print:mb-1 print:p-1 print:border-none print:shadow-none print:bg-white print:text-black mb-4"
                >
                    <CardHeader>
                        <div class="flex justify-between border-b pb-2 mb-4">
                            <div>
                                <h1 class="text-2xl font-bold">
                                    Invoice
                                    <span
                                        v-if="activeInvoice"
                                        class="text-sm text-slate-400"
                                        >#{{ activeInvoice.id }}</span
                                    >
                                </h1>
                            </div>
                            <div class="print:hidden flex items-center gap-2">
                                <Button
                                    @click="createNewInvoice"
                                    v-if="activeInvoice"
                                    variant="outline"
                                    >Create</Button
                                >
                                <Button
                                    v-if="activeInvoice"
                                    @click="handleDelete(activeInvoice.id)"
                                    variant="outline"
                                    >Delete</Button
                                >
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <!-- Billing Information -->
                        <div class="flex justify-between gap-4 mb-4">
                            <AddressCard
                                :label="'Billing Form'"
                                :title="
                                    selectedOutlet?.name ||
                                    appSettings?.site_name ||
                                    'Marchent Outlet'
                                "
                                :email="
                                    selectedOutlet?.email || appSettings?.email
                                "
                                :address="
                                    selectedOutlet?.address ||
                                    appSettings?.store_address
                                "
                            >
                                <template v-if="hasOutlet">
                                    <div
                                        class="print:hidden w-full flex items-center"
                                    >
                                        <FormField name="outlet">
                                            <FormItem :class="`w-full`">
                                                <FormControl>
                                                    <Select
                                                        v-model="
                                                            invoiceForm.outlet_id
                                                        "
                                                    >
                                                        <SelectTrigger>
                                                            <SelectValue
                                                                placeholder="Select outlet"
                                                            />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            <SelectItem
                                                                v-for="outlet in invoiceOutlets.value"
                                                                :key="outlet.id"
                                                                :value="
                                                                    outlet.id
                                                                "
                                                            >
                                                                {{
                                                                    outlet.name
                                                                }}
                                                            </SelectItem>
                                                        </SelectContent>
                                                    </Select>
                                                </FormControl>
                                                <FormMessage />
                                            </FormItem>
                                        </FormField>
                                    </div>
                                </template>
                            </AddressCard>
                            <AddressCard
                                :label="'Billing to'"
                                :title="
                                    selectedCustomer
                                        ? `${selectedCustomer.first_name} ${selectedCustomer.last_name}`
                                        : 'Client Name'
                                "
                                :email="selectedCustomer?.email"
                                :address="selectedCustomer?.billing.address_1"
                            >
                                <template v-if="customers" class="relative">
                                    <!-- Customer Address -->
                                    <div
                                        class="print:hidden w-full flex items-center"
                                    >
                                        <Combobox
                                            v-model="invoiceForm.customer_id"
                                            placeholder="Select Customer"
                                            :items="customers"
                                            item-label="full_name"
                                        />
                                        <Button
                                            variant="outline"
                                            size="icon"
                                            class="ml-3 shrink-0 shadow-sm opacity-50 hover:opacity-100"
                                            @click="
                                                activeTab =
                                                    activeTab === 'create'
                                                        ? 'details'
                                                        : 'create'
                                            "
                                        >
                                            <PlusCircledIcon
                                                :class="
                                                    activeTab === 'create' &&
                                                    'rotate-45'
                                                "
                                                class="h-6 w-6"
                                            />
                                        </Button>
                                    </div>
                                </template>
                            </AddressCard>
                        </div>
                        <div class="py-2 flex justify-between">
                            <div
                                class="flex print:w-fit print:justify-between gap-4"
                            >
                                <p
                                    class="print:text-sm text-sm text-gray-500 flex gap-1 items-center"
                                >
                                    <DatePicker
                                        label="Issue Date:"
                                        v-model="invoiceForm.issue_date"
                                    />
                                </p>
                                <p
                                    class="print:text-sm text-sm text-gray-500 flex gap-1 items-center"
                                >
                                    <DatePicker
                                        label="Due Date:"
                                        v-model="invoiceForm.due_date"
                                    />
                                </p>
                            </div>

                            <Button
                                class="print:hidden"
                                @click="activeTab = 'addItem'"
                                >Add Item <PlusCircledIcon class="h-6 w-6"
                            /></Button>
                        </div>

                        <ProductTable
                            v-model="invoiceForm.line_items"
                            :itemTotalPrice="itemTotalPrice"
                            @handleRemove="handleRemove"
                        ></ProductTable>

                        <div class="flex justify-end mt-4">
                            <div class="space-y-2 min-w-[300px]">
                                <div class="flex justify-between">
                                    <span class="text-sm">Subtotal</span>
                                    <span class="font-medium"
                                        >${{ subTotalPrice }}</span
                                    >
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-sm"
                                        >Vat
                                        <span
                                            class="border-b-2 pb-1 overflow-hidden print:hidden"
                                        >
                                            (
                                            <EditableText
                                                :class="' inline-block'"
                                                :is-number="true"
                                                v-model="invoiceForm.vat"
                                            ></EditableText>
                                            %)</span
                                        ></span
                                    >
                                    <span class="font-medium">{{
                                        formatAmount(vatCalculate)
                                    }}</span>
                                </div>
                                <Separator />
                                <div class="flex justify-between border-t">
                                    <span class="font-semibold">Total</span>
                                    <span class="font-semibol">{{
                                        formatAmount(finalTotal)
                                    }}</span>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </PrintSection>
            <!-- Footer Action -->
            <div class="print:hidden flex justify-center gap-2">
                <Button variant="outline"
                    >Send Mail
                    <Mail class="w-4 h-4 ml-2" />
                </Button>
                <Button variant="outline" @click="handlePrint"
                    >Print PDF <Printer class="w-4 h-4 ml-2"
                /></Button>
                <Button
                    v-if="!activeInvoice"
                    @click="submitInvoice"
                    :loading="loading"
                >
                    Submit <Send class="w-4 h-4 ml-2"
                /></Button>
                <Button
                    v-if="activeInvoice"
                    @click="updateInvoice(invoiceForm)"
                >
                    Update <Send class="w-4 h-4 ml-2"
                /></Button>
            </div>
        </div>
        <template #sidebar>
            <Tabs class="print:hidden w-full" v-model="activeTab">
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
                        <InvoiceListTable></InvoiceListTable>
                    </TabsContent>
                    <TabsContent value="create" class="mt-0">
                        <CustomerForm
                            :submitForm="createCustomer"
                            name="createForm"
                            ref="customerCreateFormRef"
                        />
                    </TabsContent>
                    <TabsContent value="addItem" class="mt-0">
                        <!-- Add Item -->
                        <div class="additem w-full p-4">
                            <div class="flex justify-end">
                                <Button
                                    @click="activeTab = 'details'"
                                    variant="outline"
                                    >Close
                                    <X class="w-4 h-4 ml-2" />
                                </Button>
                            </div>
                            <div class="item-show-modal w-full relative mt-2">
                                <!-- Search Bar -->
                                <div
                                    class="relative rounded-md rounded-b-none border bg-popover shadow-sm"
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
                                        @click="handleCleare"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>

                                <!-- Product List -->
                                <div
                                    class="w-full rounded-md border border-t-0 rounded-t-none bg-popover shadow-sm"
                                >
                                    <ScrollArea
                                        class="h-auto"
                                        style="height: calc(100vh - 250px)"
                                    >
                                        <div
                                            v-for="product in products || []"
                                            :key="product.id"
                                            class="flex items-center justify-between px-2 p-1 hover:bg-muted"
                                        >
                                            <div class="flex-1">
                                                <h4 class="text-xs font-medium">
                                                    {{ product.name }}
                                                </h4>
                                                <p
                                                    class="text-xs text-muted-foreground"
                                                >
                                                    Price: ${{ product.price }}
                                                </p>
                                            </div>
                                            <Button
                                                variant="ghost"
                                                size="icon"
                                                type="button"
                                                @click="
                                                    handleAddProduct(product)
                                                "
                                            >
                                                <PlusCircle class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </ScrollArea>
                                </div>
                            </div>
                        </div>
                    </TabsContent>
                </StickyFooterLayout>
            </Tabs>
        </template>
        <ConfirmDialog
            title="Are you sure you want to delete this invoice?"
            description="This action will move the invoice to the trash and can be restored within 30 days."
            v-model="showConfirmDialog"
            :deleting="deleting"
            @confirm="confirmDelete(deleteInvoice)"
        />
    </AppLayout>
</template>
