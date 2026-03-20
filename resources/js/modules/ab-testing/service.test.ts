import { describe, it, expect, vi, beforeEach } from 'vitest'
import { AbTestingService } from './service'
import { webClient } from '@/shared/http/clients'

vi.mock('@/shared/http/clients', () => ({
    webClient: {
        get: vi.fn(),
        post: vi.fn(),
    },
}))

describe('ab-testing service', () => {
    let service: AbTestingService

    beforeEach(() => {
        vi.clearAllMocks()
        service = new AbTestingService()
    })

    it('getVariant fetches from API and caches result', async () => {
        vi.mocked(webClient.get).mockResolvedValueOnce({ data: { variant: 'control' } })

        const first = await service.getVariant('my-test')
        const second = await service.getVariant('my-test')

        expect(first).toBe('control')
        expect(second).toBe('control')
        expect(webClient.get).toHaveBeenCalledTimes(1)
        expect(webClient.get).toHaveBeenCalledWith('/api/ab/assign/my-test')
    })

    it('getVariant returns cached null variant', async () => {
        vi.mocked(webClient.get).mockResolvedValueOnce({ data: { variant: null } })

        const first = await service.getVariant('my-test')
        const second = await service.getVariant('my-test')

        expect(first).toBeNull()
        expect(second).toBeNull()
        expect(webClient.get).toHaveBeenCalledTimes(1)
    })

    it('trackEvent posts to API', async () => {
        vi.mocked(webClient.post).mockResolvedValueOnce({})

        await service.trackEvent('my-test', 'signup')

        expect(webClient.post).toHaveBeenCalledTimes(1)
        expect(webClient.post).toHaveBeenCalledWith('/api/ab/event', {
            test: 'my-test',
            event: 'signup',
        })
    })
})