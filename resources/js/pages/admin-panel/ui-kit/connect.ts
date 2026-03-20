import { Chart } from 'chart.js/auto'

import { buildUiKitChartConfig } from '@/modules/admin-ui-kit-charts/service'

type UiKitChartKind = 'line' | 'area' | 'bar' | 'histogram' | 'pie' | 'donut' | 'sparkline'

const kinds: UiKitChartKind[] = ['line', 'area', 'bar', 'histogram', 'pie', 'donut', 'sparkline']

export async function initAdminUiKitPage(root: HTMLElement): Promise<void> {
    const canvases = root.querySelectorAll<HTMLCanvasElement>('[data-admin-chart]')

    canvases.forEach((canvas) => {
        const kind = canvas.dataset.adminChart as UiKitChartKind | undefined

        if (!kind || !kinds.includes(kind)) {
            return
        }

        const context = canvas.getContext('2d')

        if (!context) {
            return
        }

        const existingChart = Chart.getChart(canvas)

        existingChart?.destroy()

        new Chart(context, buildUiKitChartConfig(kind))
    })
}
