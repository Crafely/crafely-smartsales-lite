// import { ref, computed } from 'vue'

// import { useUserStore } from '@/stores/userStore'

// import { find, findIndex, isEmpty } from 'lodash'
// import { toast } from 'vue-sonner'
// import { useAxios } from '@/composable/useAxios'

// import { formatAmount } from '@/utils'

// import type { Product, OrderForm, Customer } from '@/types'

// export const useOrder = () => {
//     const activeCustomer = ref<Customer>()
//     const showCustomerForm = ref(false)
//     const paymentMethods = [
//         { id: 'cash', name: 'Cash' },
//         { id: 'card', name: 'Card' },
//         { id: 'bank_transfer', name: 'Bank Transfer' },
//         { id: 'paypal', name: 'PayPal' },
//         { id: 'upi', name: 'UPI' },
//         { id: 'cryptocurrency', name: 'Cryptocurrency' },
//         { id: 'cod', name: 'Cash on Delivery' },
//     ]
//     const userStore = useUserStore()
//     const items = ref<Product[]>([])
//     const { post } = useAxios()
//     const orderForm = ref<OrderForm>({
//         customer_id: null,
//         payment_method: 'cash',
//         line_items: [],
//     })
//     const loading = ref(false)
//     const cartTotal = computed(() => {
//         if (isEmpty(items.value)) {
//             return
//         }
//         const total = items.value.reduce(
//             (total, item) => total + (item.sale_price || item.price) * item.quantity,
//             0
//         )
//         return formatAmount(total, getCurrency())
//     })

//     function getCurrency() {
//         return items.value[0]?.currency || 'USD'
//     }

//     const setActiveCustomer = (customer: Customer) => {
//         activeCustomer.value = customer
//     }

//     const cartItemsCount = computed(() => {
//         const total = items.value.reduce(
//             (count, item) => count + item.quantity,
//             0
//         )
//         if (total) {
//             return formatAmount(total, getCurrency())
//         }

//         return 'No'
//     })

//     function addToCart(product: Product) {
//         const existingItem = find(items.value, { id: product.id })

//         if (existingItem) {
//             existingItem.quantity = (existingItem.quantity || 0) + 1
//         } else {
//             product.quantity = 1
//             items.value.push(product)
//         }
//     }

//     //
//     const submitOrder = async () => {
//         try {
//             loading.value = true
//             orderForm.value.line_items = items.value.map((item) => ({
//                 product_id: item.id,
//                 quantity: item.quantity,
//             }))
//             const response = await post('orders', orderForm.value, {auth: userStore.userCredentials})
//             const { success, message } = response

//             toast[success ? 'success' : 'error'](message)

//         } catch (error: any) {
//             const errorResponse = error?.response?.data
//             const errorMessage = errorResponse?.message || (error instanceof Error ? error.message : 'Failed to submit order')
//             const errorDetails = errorResponse?.details || 'Please try again or contact support if the issue persists'

//             toast.error(errorMessage, {
//                 description: errorDetails,
//             })
//         } finally {

//             loading.value = false
//         }
//     }

//     function decreaseQuantity(product: Product) {
//         const item = find(items.value, { id: product.id })
//         if (!item) return
//         item.quantity = item.quantity - 1
//         if (item.quantity === 0) {
//             removeFromCart(product.id)
//         }
//     }

//     function removeFromCart(productId: number) {
//         const index = findIndex(items.value, { id: productId })
//         if (index > -1) items.value.splice(index, 1)
//     }

//     const clearCart = () => {
//         items.value.forEach((item) => {
//             item.quantity = 0
//         })
//         items.value = []
//         orderForm.value = {
//             customer_id: null,
//             payment_method: 'cash',
//             line_items: [],
//         }
//     }

//     const handleAddPaymentMethod = () => {
//         try {
//             if (orderForm.value.split_payments) {
//                 orderForm.value.split_payments.push({ method: 'cash', amount: '' })
//             } else {
//                 const currentMethod = orderForm.value.payment_method || 'cash'
//                 console.log(currentMethod, 'current method')
//                 orderForm.value.split_payments = [{ method: currentMethod, amount: '' }]
//                 delete orderForm.value.payment_method
//             }
//         } catch (error) {
//             toast.error('Failed to add payment method')
//         }
//     }

//     const handleRemovePaymentMethod = (index: number) => {
//         try {
//             if(!orderForm.value.split_payments) return
//             if (orderForm.value.split_payments.length < 2) {
//                 const preservedMethod = orderForm.value.split_payments[0]?.method || 'cash'
//                 delete orderForm.value.split_payments
//                 orderForm.value.payment_method = preservedMethod
//             } else {
//                 orderForm.value.split_payments.splice(index, 1)
//             }
//         } catch (error) {
//             toast.error('Failed to remove payment method')
//         }
//     }

//     return {
//         items,
//         cartTotal,
//         orderForm,
//         loading,
//         cartItemsCount,
//         addToCart,
//         removeFromCart,
//         decreaseQuantity,
//         submitOrder,
//         clearCart,
//         handleAddPaymentMethod,
//         handleRemovePaymentMethod,
//         paymentMethods,
//         setActiveCustomer,
//         activeCustomer,
//         showCustomerForm
//     }
// }
