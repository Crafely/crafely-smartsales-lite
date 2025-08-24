import { z } from 'zod'
export const taskSchema = z.object({
    id: z.string(),
    name: z.string(),
    status: z.string(),
    label: z.string(),
    priority: z.string(),
})
