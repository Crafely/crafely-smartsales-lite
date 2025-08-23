import * as z from 'zod'
export const userSchema = z.object({
    // Required fields
    username: z
        .string({
            required_error: 'Username is required',
        })
        .min(3, 'Username must be at least 3 characters'),
    email: z
        .string({
            required_error: 'Email is required',
        })
        .email('Please enter a valid email address'),
    first_name: z.string().optional(),
    last_name: z.string().optional(),
    display_name: z.string().optional(),
})
