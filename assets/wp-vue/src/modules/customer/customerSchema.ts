import * as z from 'zod'

// Reusable address schema
const addressSchema = z.object({
    first_name: z.string().optional(),
    last_name: z.string().optional(),
    company: z.string().optional(),
    address_1: z.string().optional(),
    address_2: z.string().optional(),
    city: z.string().optional(),
    state: z.string().optional(),
    postcode: z.string().optional(),
    country: z.string().optional(),
})

// Extended billing schema with email and phone
const billingSchema = addressSchema.extend({
    email: z.string().optional(),
    phone: z.string().optional(),
})

export const schema = z.object({
    username: z
        .string({
            required_error: 'Username is required.',
        })
        .min(2, {
            message: 'Username must be at least 2 characters.',
        }),
    email: z.string().email().optional(),
    first_name: z.string().min(1, 'First name is required'),
    last_name: z.string().min(1, 'Last name is required'),
    phone: z.string().min(10, 'Valid phone number is required'),
    sameAddress: z.boolean().default(true).optional(),
    billing: billingSchema.optional(),
    shipping: addressSchema,
})
