import { Customer, Product, PaymentMethod } from '@/types'
export type LineItem = {
    product_id: number
    name: string
    quantity: number
    price: number
    total: number
}

export type Order = {
    id: number
    status: string
    total: string
    customer_id: number
    created_at: string
    updated_at: string
    line_items: LineItem[]
    currency: string
    payment_method?: string
    split_payments?: PaymentMethod[]
    discount_total: number
    customer: Customer
    // We have to update with the correct type
    payment_details: {
        payment_method: string
        split_payments: any[]
    }
}

export type OrderProduct = {
    product_id: number
    quantity: number
}

export type OrderForm = {
    customer_id: number | null
    payment_method?: string
    line_items: OrderProduct[]
    split_payments?: PaymentMethod[]
}
