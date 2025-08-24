import * as z from 'zod'
import { name } from '@/schema'
export const outletCreateSchema = z.object({
    name,
})
