import { Order } from '@/types'

export type CustomerOrder = Order & {
    order_id: number
    date: Date
}

export type Customer = {
    id: number
    first_name: string
    last_name: string
    full_name: string
    name: string
    email: string
    phone: string
    profile_image: string
    billing: Address
    shipping: Address
    total_spent: number
    orders: CustomerOrder[]
}

export type Address = {
    first_name: string
    last_name: string
    company?: string
    address_1: string
    address_2?: string
    city: string
    state: string
    postcode: string
    country: string
    email: string
    phone: string
}
