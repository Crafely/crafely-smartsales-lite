import { Customer } from '@/types'
import { format, parseISO } from 'date-fns'

export * from './table'
export * from './network'

export const getFullName = (
    customer: Customer,
    defaultValue: string = ''
): string => {
    const first = customer.first_name?.trim() || ''
    const last = customer.last_name?.trim() || ''

    if (!first && !last) return defaultValue
    if (!last) return first
    if (!first) return last

    return `${first} ${last}`
}

export const formatDate = (
    date: string | Date,
    formatStr: string = 'PP'
): string => {
    try {
        const dateObj = typeof date === 'string' ? parseISO(date) : date
        return format(dateObj, formatStr)
    } catch (error) {
        return ''
    }
}

export const formatDateTime = (date: string | Date): string => {
    return formatDate(date, 'PP p')
}

export const formatShortDate = (date: string | Date): string => {
    return formatDate(date, 'P')
}

export const getQueryString = (query: Record<string, any>): string | null => {
    const params = Object.entries(query)
        .filter(
            ([_, value]) =>
                value !== undefined && value !== null && value !== ''
        )
        .map(
            ([key, value]) =>
                `${encodeURIComponent(key)}=${encodeURIComponent(value)}`
        )
        .join('&')

    return params ? `?${params}` : null
}

export function extractInitials(text, length = 2) {
    if (!text) return 'NA'
    const name = text.trim()
    if (name.length < length) return name.charAt(0).toUpperCase()
    return name.slice(0, length).toUpperCase()
}

export function toggleItems(state, item, items) {
    return state ? [...items, item] : items.filter((id) => id !== item)
}

export const getPublicPath = (path: string) => {
    const isProd = import.meta.env.PROD
    return isProd ? `/wp-content/plugins/smartsales-lite/dist${path}` : path
}
