import * as z from 'zod'

export const businessTypes = [
    'retail',
    'wholesale',
    'manufacturing',
    'service',
] as const
export const inventoryRanges = [
    'small',
    'medium',
    'large',
    'enterprise',
] as const

export const salesChannels = [
    { id: 'physical_store', label: 'Physical Store' },
    { id: 'online_marketplace', label: 'Online Marketplace' },
    { id: 'social_media', label: 'Social Media' },
    { id: 'website', label: 'Website' },
] as const

export const companySizes = ['small', 'medium', 'large'] as const
export const revenueRanges = [
    { value: '0-10000', label: '$0 - $10,000' },
    { value: '10000-50000', label: '$10,000 - $50,000' },
    { value: '50000-100000', label: '$50,000 - $100,000' },
    { value: '100000+', label: '$100,000+' },
] as const

export const targetMarkets = ['local', 'national', 'international'] as const

export const setupSchema = z.object({
    business_type: z.enum(businessTypes),
    inventory_range: z.enum(inventoryRanges),
    company_name: z
        .string()
        .min(2, 'Company name must be at least 2 characters'),
    industry_sector: z.string().min(2, 'Industry sector is required'),
    has_outlet: z.boolean().optional(),
    company_size: z.enum(companySizes).optional(),
    monthly_revenue: z.string().optional(),
    sales_channel: z
        .array(z.string())
        .refine((value) => value.some((item) => item), {
            message: 'You have to select at least one item.',
        }),
    target_market: z.enum(targetMarkets).optional(),
    additional_notes: z.string().optional(),
})
