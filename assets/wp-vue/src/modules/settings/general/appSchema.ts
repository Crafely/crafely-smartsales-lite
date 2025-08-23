import * as z from 'zod'

export const currencies = ['USD', 'EURO', 'BDT'] as const

export const storeSchema = z.object({
    site_name: z.string().optional(),
    store_country: z.string().optional(),
    store_city: z.string().optional(),
    store_postcode: z.string().optional(),
    store_address: z.string().optional(),
    store_address_2: z.string().optional(),
    currency: z.enum(currencies).optional(),
})
