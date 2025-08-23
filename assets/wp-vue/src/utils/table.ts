export const formatAmount = (
    amount: number | string,
    currency: string = 'USD'
) => {
    let price: number
    if (typeof amount === 'string') {
        price = parseFloat(amount)
    } else {
        price = amount
    }
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency,
    }).format(price || 0)
}

export const modifyQuery = (query) => {
    if (query.q) {
        return { q: query.q }
    }
    return {
        current_page: (query.pageIndex ?? 0) + 1,
        per_page: query.pageSize ?? 10,
    }
}
