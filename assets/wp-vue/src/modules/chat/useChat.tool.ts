export const get_dashboard_summary = () => {
    return {
        name: 'get_dashboard_summary',
        description:
            'Provide dashboard summary by date range, summary means total order, total customer registration etc',
        args: {
            start_date: {
                type: 'string',
                description: 'start date ex, 01-01-2025',
            },
            end_date: {
                type: 'string',
                description: 'end date ex, 01-01-2025',
            },
        },
    }
}
