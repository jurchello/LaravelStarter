import { webClient } from '@/shared/http/clients'
import type { Variant } from './state'

export class AbTestingService {
    private assignments: Record<string, Variant> = {}

    async getVariant(testSlug: string): Promise<Variant> {
        if (testSlug in this.assignments) {
            return this.assignments[testSlug]
        }

        const response = await webClient.get<{ variant: Variant }>(`/api/ab/assign/${testSlug}`)
        this.assignments[testSlug] = response.data.variant
        return this.assignments[testSlug]
    }

    async trackEvent(testSlug: string, event: string): Promise<void> {
        await webClient.post('/api/ab/event', { test: testSlug, event })
    }
}

const service = new AbTestingService()

export const getVariant = (testSlug: string): Promise<Variant> => service.getVariant(testSlug)
export const trackEvent = (testSlug: string, event: string): Promise<void> =>
    service.trackEvent(testSlug, event)