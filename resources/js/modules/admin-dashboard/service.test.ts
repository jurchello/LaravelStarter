import { describe, expect, it, vi } from 'vitest'

vi.mock('@/shared/http/clients', () => ({
    webClient: {
        get: vi.fn(),
    },
}))

import { webClient } from '@/shared/http/clients'
import { AdminDashboardService } from './service'

describe('AdminDashboardService', () => {
    it('returns stats from the admin dashboard envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValue({
            data: {
                data: {
                    stats: [
                        { label: 'Total users', value: 12, tone: 'neutral' },
                    ],
                },
            },
        })

        const service = new AdminDashboardService()

        await expect(service.load('/management/api/dashboard')).resolves.toEqual([
            { label: 'Total users', value: 12, tone: 'neutral' },
        ])
    })
})
