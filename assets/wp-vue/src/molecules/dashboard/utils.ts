import type { SalesAnalytics, CustomerAnalytics } from '@/types'

export const customerDataToChartConfig = (customerData: CustomerAnalytics) => {
    const defaultData = {
        labels: [],
        datasets: [
            {
                label: 'Total Spent',
                data: [],
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
                yAxisID: 'y'
            },
            {
                label: 'Number of Orders',
                data: [],
                backgroundColor: 'rgba(246, 59, 130, 0.5)',
                borderColor: 'rgb(246, 59, 130)',
                borderWidth: 1,
                yAxisID: 'y1'
            }
        ]
    }

    if (!customerData.top_customers || customerData.top_customers.length === 0) {
        return {
            type: 'bar',
            data: defaultData,
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        position: 'left',
                        beginAtZero: true,
                        ticks: {
                            callback: (value: number) => `$${value}`
                        }
                    },
                    y1: {
                        type: 'linear',
                        position: 'right',
                        beginAtZero: true,
                        grid: {
                            drawOnChartArea: false
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top' as const
                    },
                    tooltip: {
                        callbacks: {
                            label: (context: any) => {
                                const label = context.dataset.label || '';
                                if (label === 'Total Spent') {
                                    return `${label}: $${context.parsed.y}`;
                                }
                                return `${label}: ${context.parsed.y}`;
                            }
                        }
                    }
                }
            }
        }
    }

    const data = {
        labels: customerData.top_customers.map(customer => customer.name),
        datasets: [
            {
                label: 'Total Spent',
                data: customerData.top_customers.map(customer => customer.total_spent),
                backgroundColor: 'rgba(59, 130, 246, 0.5)',
                borderColor: 'rgb(59, 130, 246)',
                borderWidth: 1,
                yAxisID: 'y'
            },
            {
                label: 'Number of Orders',
                data: customerData.top_customers.map(customer => customer.orders),
                backgroundColor: 'rgba(246, 59, 130, 0.5)',
                borderColor: 'rgb(246, 59, 130)',
                borderWidth: 1,
                yAxisID: 'y1'
            }
        ]
    }

    return {
        type: 'bar',
        data,
        options: {
            responsive: true,
            scales: {
                y: {
                    type: 'linear',
                    position: 'left',
                    beginAtZero: true,
                    ticks: {
                        callback: (value: number) => `$${value}`
                    }
                },
                y1: {
                    type: 'linear',
                    position: 'right',
                    beginAtZero: true,
                    grid: {
                        drawOnChartArea: false
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top' as const
                },
                tooltip: {
                    callbacks: {
                        label: (context: any) => {
                            const label = context.dataset.label || '';
                            if (label === 'Total Spent') {
                                return `${label}: $${context.parsed.y}`;
                            }
                            return `${label}: ${context.parsed.y}`;
                        }
                    }
                }
            }
        }
    }
}

export const salesDataToChartConfig = (salesData: SalesAnalytics) => {
    const dates = Object.keys(salesData.sales_by_date)
    const salesValues = Object.values(salesData.sales_by_date)

    return {
        type: 'bar',
        data: {
            labels: dates,
            datasets: [
                {
                    label: 'Daily Sales',
                    data: salesValues,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: (value: number) => `$${value}`
                    }
                }
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top' as const
                },
                tooltip: {
                    callbacks: {
                        label: (context: any) => `Sales: $${context.parsed.y}`
                    }
                }
            }
        }
    }
}

