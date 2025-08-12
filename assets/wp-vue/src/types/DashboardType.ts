import { PaymentMethod } from '@/types'

export type DateRange = {
    start: string
    end: string
}

export type SalesByChannel = {
    channel: string
    total: number
}

export type Sales = {
    total: number
    average_order_value: number
    total_orders: number
    total_items_sold: number
    by_channel: SalesByChannel[]
    payment_methods: PaymentMethod[]
}

export type Inventory = {
    total_products: number
    low_stock: number
    out_of_stock: number
    total_value: number
}

export type Customers = {
    total: number
}

export type Outlets = {
    total: number
}

export type DashboardSummary = {
    date_range: DateRange
    sales: Sales
    inventory: Inventory
    customers: Customers
    outlets: Outlets
}

export type BestSellingProduct = {
    id: number
    name: string
    quantity: number
    total: number
}

export type SalesAnalytics = {
    date_range: DateRange
    sales_by_date: { [key: string]: number }
    sales_by_hour: number[]
    sales_by_outlet: any[]
    sales_by_payment: { [key: string]: number }
    best_selling_products: BestSellingProduct[]
}

export type TopCustomer = {
    id: number
    name: string
    email: string
    orders: number
    total_spent: number
}

export type CustomerSegments = {
    vip: number
    loyal: number
    potential: number
    new: number
    dormant: number
}

export type CustomerAnalytics = {
    date_range: DateRange
    total_customers: number
    new_customers: number
    average_order_value: number
    top_customers: TopCustomer[]
    customer_segments: CustomerSegments
}

export type InventoryStats = {
    total_products: number
    low_stock: number
    out_of_stock: number
    in_stock: number
    total_value: number
}

export type TopProduct = {
    id: number
    name: string
    quantity: number
    revenue: number
}

export type ProductAnalytics = {
    date_range: DateRange
    inventory_stats: InventoryStats
    category_stats: { [key: string]: number }
    price_ranges: { [key: string]: number }
    top_products: TopProduct[]
}

export type OutletStat = {
    id: number
    name: string
    total_sales: number
    total_orders: number
    average_order_value: number
    counters: number
    staff: number
}

export type OutletAnalytics = {
    date_range: DateRange
    total_outlets: number
    outlet_stats: OutletStat[]
}

export type RecentActivity = {
    type: string
    id: number
    title: string
    amount?: string
    status: string
    timestamp: number
    date: string
    email?: string
}

export type RecentActivities = {
    success: boolean
    message: string
    data: RecentActivity[]
}