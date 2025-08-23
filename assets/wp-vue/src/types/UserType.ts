import { Outlet } from '@/types'

type UserPermissions = {
    manage_users: boolean
    manage_outlets: boolean
    manage_inventory: boolean
    manage_settings: boolean
    view_reports: boolean
    process_sales: boolean
    manage_customers: boolean
    manage_discounts: boolean
}

type Roles =
    | 'csmsl_pos_shop_manager'
    | 'csmsl_pos_outlet_manager'
    | 'csmsl_pos_cashier'
    | 'customer'
    | 'administrator'

type UserStats = {
    total_sales: number
    total_orders: number
    last_activity: string
    session_status: 'active' | 'inactive'
    last_order: any | null
}

export type User = {
    id: number
    username: string
    name: string
    first_name: string
    last_name: string
    email: string
    roles: Roles[]
    created_at: string
    last_login: string
    status: 'active' | 'inactive'
    avatar: string
    permissions: UserPermissions
    orders: any[]
    stats: UserStats
    outlet: Outlet | null
}
