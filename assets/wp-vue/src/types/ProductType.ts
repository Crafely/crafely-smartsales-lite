export type Product = {
    id: number
    name: string
    price: number
    sale_price?: number
    currency: string
    stock: number | null
    sku: string
    short_description: string
    description: string
    status: 'publish' | 'draft' | 'pending' | 'private'
    categories: number[]
    tags: string[]
    attributes: never[]
    variations: Variation[]
    image_id: string
    image_url: string
    gallery_image_ids: string[]
    quantity: number
    cart?: { [cartId: number]: number }
}
type Attributes = {
    [key: string]: string
}

export type Variation = {
    id: number
    name: string
    price: string
    stock: number
    sku: string
    attributes: Attributes
    image_url: string
}

export type ProductQueryParams = {
    q?: string
    per_page?: number
    current_page?: number
}
