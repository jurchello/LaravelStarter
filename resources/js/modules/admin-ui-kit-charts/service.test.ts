import { describe, expect, it } from 'vitest'

import { buildUiKitChartConfig } from './service'

describe('buildUiKitChartConfig', () => {
    it('builds line chart configuration', () => {
        const config = buildUiKitChartConfig('line')

        expect(config.type).toBe('line')
        expect(config.data?.datasets[0]?.data).toHaveLength(7)
        expect(config.options?.plugins?.legend?.display).toBe(false)
    })

    it('builds donut chart configuration', () => {
        const config = buildUiKitChartConfig('donut')

        expect(config.type).toBe('doughnut')
        expect(config.data?.labels).toEqual(['Control', 'Variant A', 'Variant B'])
    })

    it('builds compact sparkline configuration', () => {
        const config = buildUiKitChartConfig('sparkline')

        expect(config.type).toBe('line')
        expect(config.options?.scales).toEqual({})
        expect(config.data?.datasets[0]?.pointRadius).toBe(0)
    })
})
