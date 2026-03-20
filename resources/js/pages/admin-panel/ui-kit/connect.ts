import { Chart } from 'chart.js/auto'

import { buildUiKitChartConfig } from '@/modules/admin-ui-kit-charts/service'
import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'

type UiKitChartKind = 'line' | 'area' | 'bar' | 'histogram' | 'pie' | 'donut' | 'sparkline'

const kinds: UiKitChartKind[] = ['line', 'area', 'bar', 'histogram', 'pie', 'donut', 'sparkline']

export function connectAdminUiKitPage(root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('admin-ui-kit', () => bootstrapAdminUiKitPage(root)),
    ])
}

async function bootstrapAdminUiKitPage(root: HTMLElement): Promise<void> {
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
