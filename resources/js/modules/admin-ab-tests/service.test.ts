import { describe, expect, it, vi } from 'vitest'

vi.mock('@/shared/http/clients', () => ({
    webClient: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        patch: vi.fn(),
        delete: vi.fn(),
    },
}))

import { webClient } from '@/shared/http/clients'
import { AdminAbTestsService } from './service'

describe('AdminAbTestsService', () => {
    it('returns paginated ab tests from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValue({
            data: {
                data: {
                    items: [
                        {
                            id: 3,
                            name: 'Homepage Hero',
                            slug: 'homepage-hero',
                            status: 'active',
                            trafficPercent: 100,
                            variantsCount: 2,
                        },
                    ],
                },
                meta: {
                    page: 1,
                    perPage: 50,
                    total: 1,
                    totalPages: 1,
                },
            },
        })

        const service = new AdminAbTestsService()

        await expect(service.load('/management/api/ab-tests', {
            page: 1,
            sort: 'name',
            direction: 'asc',
            search: 'home',
            status: 'active',
        })).resolves.toEqual({
            items: [
                {
                    id: 3,
                    name: 'Homepage Hero',
                    slug: 'homepage-hero',
                    status: 'active',
                    trafficPercent: 100,
                    variantsCount: 2,
                },
            ],
            page: 1,
            perPage: 50,
            total: 1,
            totalPages: 1,
        })
    })

    it('returns autocomplete suggestions from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValueOnce({
            data: {
                data: {
                    items: ['Homepage Hero', 'homepage-hero'],
                },
            },
        })

        const service = new AdminAbTestsService()

        await expect(service.loadSuggestions('/management/api/ab-tests/suggestions', 'home')).resolves.toEqual([
            'Homepage Hero',
            'homepage-hero',
        ])
    })

    it('returns management detail from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValueOnce({
            data: {
                data: {
                    id: 5,
                    name: 'Homepage Hero',
                    slug: 'homepage-hero',
                    status: 'draft',
                    trafficPercent: 100,
                    distributionMode: 'manual',
                    variants: [],
                    analytics: {
                        assignmentsCount: 0,
                        identifiedAssignmentsCount: 0,
                        eventsByName: {},
                    },
                    recentAssignments: [],
                    recentEvents: [],
                },
            },
        })

        const service = new AdminAbTestsService()

        await expect(service.loadDetail('/management/api/ab-tests/5')).resolves.toMatchObject({
            id: 5,
            slug: 'homepage-hero',
        })
    })

    it('creates a test through the admin api envelope', async () => {
        vi.mocked(webClient.post).mockResolvedValueOnce({
            data: {
                data: {
                    id: 6,
                    name: 'Checkout Flow',
                    slug: 'checkout-flow',
                    status: 'draft',
                    trafficPercent: 80,
                    distributionMode: 'manual',
                    variants: [],
                    analytics: {
                        assignmentsCount: 0,
                        identifiedAssignmentsCount: 0,
                        eventsByName: {},
                    },
                    recentAssignments: [],
                    recentEvents: [],
                },
            },
        })

        const service = new AdminAbTestsService()

        await expect(service.create('/management/api/ab-tests', {
            name: 'Checkout Flow',
            slug: 'checkout-flow',
            trafficPercent: 80,
            distributionMode: 'manual',
        })).resolves.toMatchObject({
            id: 6,
            slug: 'checkout-flow',
        })
    })

    it('returns audience estimate from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValueOnce({
            data: {
                data: {
                    audienceSize: 120,
                    trafficPercent: 30,
                    estimatedPeople: 36,
                },
            },
        })

        const service = new AdminAbTestsService()

        await expect(service.loadAudienceEstimate('/management/api/ab-tests/audience-estimate', 30)).resolves.toEqual({
            audienceSize: 120,
            trafficPercent: 30,
            estimatedPeople: 36,
        })
    })

    it('returns paginated assignments from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValueOnce({
            data: {
                data: {
                    items: [
                        {
                            id: 11,
                            visitorId: 'visitor-001',
                            userId: null,
                            variantName: 'Control',
                            variantSlug: 'control',
                            createdAt: '2026-03-19T10:00:00+00:00',
                        },
                    ],
                },
                meta: {
                    page: 2,
                    perPage: 50,
                    total: 51,
                    totalPages: 2,
                },
            },
        })

        const service = new AdminAbTestsService()

        await expect(service.loadAssignments('/management/api/ab-tests/5/assignments', 2)).resolves.toMatchObject({
            page: 2,
            totalPages: 2,
            items: [
                {
                    visitorId: 'visitor-001',
                },
            ],
        })
    })

    it('returns paginated events from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValueOnce({
            data: {
                data: {
                    items: [
                        {
                            id: 12,
                            event: 'purchase',
                            variantName: 'Control',
                            variantSlug: 'control',
                            visitorId: 'visitor-001',
                            createdAt: '2026-03-19T10:00:00+00:00',
                        },
                    ],
                },
                meta: {
                    page: 1,
                    perPage: 50,
                    total: 1,
                    totalPages: 1,
                },
            },
        })

        const service = new AdminAbTestsService()

        await expect(service.loadEvents('/management/api/ab-tests/5/events', 1)).resolves.toMatchObject({
            total: 1,
            items: [
                {
                    event: 'purchase',
                },
            ],
        })
    })
})
