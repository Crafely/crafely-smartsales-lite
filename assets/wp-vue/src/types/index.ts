export * from './ProductType'
export * from './PaymentType'
export * from './CustomerType'
export * from './OrderType'
export * from './OutletType'
export * from './UserType'
export * from './CategoryType'
export * from './AssistantType'
export * from './AppType'
export * from './DashboardType'
export * from './InvoiceType'
export * from './WizardType'
export * from './PackageType'

export type Pagination = {
    pageIndex: number
    pageSize: number
    pageCount: number
}

export type Tabs = 'create' | 'edit' | 'details' | 'assistant'
