export type Outlet = {
    id: number
    name: string
    slug: string
    address: string
    phone: string
    email: string
    counters: Counter[]
    operating_hours?: string
    manager_name?: string
    status: 'active' | 'inactive'
    created_at: Date
    updated_at?: Date
}

export type Counter = {
    id: number
    name: string
    description: string
    position: string
    status: 'active' | 'inactive'
    created_at: Date
}
