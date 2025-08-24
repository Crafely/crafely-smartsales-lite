export type InvoiceProduct = {
    product_id: number
    quantity: number
    original_name?: string
    original_price?: number
    original_description?: string
    custom_name?: string
    custom_price?: number
    custom_description?: string
    line_total?: number
}

export type BullingFrom = {
    site_name: string
    store_email: string | null
    store_address: string | null
}

export type Invoice = {
    id: number
    customer_id: number
    outlet_id: number
    channel: string
    billing_from: BullingFrom
    line_items: InvoiceProduct[]
    vat: number | null
    subtotal: number
    issue_date: string
    due_date: string
    status: string
    created_at: string
    updated_at: string
}

export type InvoiceForm = {
    customer_id: number | null
    outlet_id: number | null
    line_items: InvoiceProduct[]
    vat: number | null
    issue_date: string
    due_date: string
}
