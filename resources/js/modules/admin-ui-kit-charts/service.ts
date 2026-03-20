import type { ChartConfiguration } from 'chart.js'

type UiKitChartKind = 'line' | 'area' | 'bar' | 'histogram' | 'pie' | 'donut' | 'sparkline'

type Dataset = number[]

const labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
const trendSeries = [24, 30, 28, 42, 38, 52, 58]
const histogramSeries = [4, 7, 11, 15, 12, 8, 5]
const categoricalSeries = [18, 24, 14, 29, 21, 26, 19]
const pieSeries = [42, 31, 27]

const palette = {
    teal: 'rgba(15, 118, 110, 0.9)',
    tealSoft: 'rgba(15, 118, 110, 0.18)',
    blue: 'rgba(59, 130, 246, 0.86)',
    blueSoft: 'rgba(59, 130, 246, 0.18)',
    amber: 'rgba(245, 158, 11, 0.82)',
    amberSoft: 'rgba(245, 158, 11, 0.22)',
    muted: 'rgba(93, 105, 95, 0.2)',
} as const

const baseOptions = (compact = false): ChartConfiguration['options'] => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    plugins: {
        legend: {
            display: false,
        },
        tooltip: {
            enabled: true,
            intersect: false,
            mode: 'index',
        },
    },
    scales: compact
        ? {}
        : {
              x: {
                  grid: {
                      display: false,
                  },
                  ticks: {
                      color: 'rgba(61, 68, 62, 0.72)',
                  },
              },
              y: {
                  beginAtZero: true,
                  grid: {
                      color: palette.muted,
                  },
                  ticks: {
                      color: 'rgba(61, 68, 62, 0.72)',
                  },
              },
          },
})

const buildLineLikeConfig = (
    type: 'line' | 'bar',
    data: Dataset,
    options: {
        fill: boolean
        compact?: boolean
        backgroundColor?: string
        borderColor?: string
        borderWidth?: number
        tension?: number
        pointRadius?: number
        barThickness?: number
    },
): ChartConfiguration => ({
    type,
    data: {
        labels,
        datasets: [
            {
                data,
                fill: options.fill,
                backgroundColor: options.backgroundColor ?? palette.tealSoft,
                borderColor: options.borderColor ?? palette.teal,
                borderWidth: options.borderWidth ?? 2,
                tension: options.tension ?? 0.35,
                pointRadius: options.pointRadius ?? (options.compact ? 0 : 3),
                pointHoverRadius: options.compact ? 0 : 4,
                barThickness: options.barThickness,
            },
        ],
    },
    options: baseOptions(options.compact),
})

const pieOptions = (): ChartConfiguration['options'] => ({
    responsive: true,
    maintainAspectRatio: false,
    animation: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                usePointStyle: true,
                boxWidth: 8,
                color: 'rgba(61, 68, 62, 0.8)',
            },
        },
    },
})

export const buildUiKitChartConfig = (kind: UiKitChartKind): ChartConfiguration => {
    switch (kind) {
        case 'line':
            return buildLineLikeConfig('line', trendSeries, {
                fill: false,
                backgroundColor: palette.tealSoft,
                borderColor: palette.teal,
            })
        case 'area':
            return buildLineLikeConfig('line', trendSeries, {
                fill: true,
                backgroundColor: palette.tealSoft,
                borderColor: palette.teal,
            })
        case 'bar':
            return buildLineLikeConfig('bar', categoricalSeries, {
                fill: false,
                backgroundColor: palette.blueSoft,
                borderColor: palette.blue,
                borderWidth: 1,
                barThickness: 18,
            })
        case 'histogram':
            return buildLineLikeConfig('bar', histogramSeries, {
                fill: false,
                compact: false,
                backgroundColor: palette.amberSoft,
                borderColor: palette.amber,
                borderWidth: 1,
                barThickness: 22,
            })
        case 'pie':
            return {
                type: 'pie',
                data: {
                    labels: ['Control', 'Variant A', 'Variant B'],
                    datasets: [
                        {
                            data: pieSeries,
                            backgroundColor: [palette.teal, palette.blue, palette.amber],
                            borderWidth: 0,
                        },
                    ],
                },
                options: pieOptions(),
            }
        case 'donut':
            return {
                type: 'doughnut',
                data: {
                    labels: ['Control', 'Variant A', 'Variant B'],
                    datasets: [
                        {
                            data: pieSeries,
                            backgroundColor: [palette.teal, palette.blue, palette.amber],
                            borderWidth: 0,
                        },
                    ],
                },
                options: pieOptions(),
            }
        case 'sparkline':
            return buildLineLikeConfig('line', trendSeries, {
                fill: false,
                compact: true,
                backgroundColor: palette.tealSoft,
                borderColor: palette.teal,
                pointRadius: 0,
                borderWidth: 2,
                tension: 0.4,
            })
    }
}
