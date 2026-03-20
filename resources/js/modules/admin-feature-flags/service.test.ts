import { describe, expect, it, vi } from 'vitest'

vi.mock('@/shared/http/clients', () => ({
    webClient: {
        get: vi.fn(),
        post: vi.fn(),
        put: vi.fn(),
        delete: vi.fn(),
    },
}))

import { webClient } from '@/shared/http/clients'
import { AdminFeatureFlagsService } from './service'

describe('AdminFeatureFlagsService', () => {
    it('returns paginated feature flags from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValue({
            data: {
                data: {
                    items: [
                        {
                            id: 3,
                            key: 'new-dashboard',
                            name: 'New Dashboard',
                            description: 'Gradual rollout.',
                            enabled: true,
                            rolloutPercent: 25,
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

        const service = new AdminFeatureFlagsService()

        await expect(service.load('/management/api/feature-flags', {
            page: 1,
            sort: 'name',
            direction: 'asc',
            search: 'new',
            status: 'enabled',
        })).resolves.toEqual({
            items: [
                {
                    id: 3,
                    key: 'new-dashboard',
                    name: 'New Dashboard',
                    description: 'Gradual rollout.',
                    enabled: true,
                    rolloutPercent: 25,
                },
            ],
            page: 1,
            perPage: 50,
            total: 1,
            totalPages: 1,
        })
    })

    it('creates, updates, deletes, and suggests feature flags via the admin api envelope', async () => {
        vi.mocked(webClient.post).mockResolvedValueOnce({
            data: {
                data: {
                    flag: {
                        id: 4,
                        key: 'beta-export',
                        name: 'Beta Export',
                        description: null,
                        enabled: false,
                        rolloutPercent: 0,
                    },
                },
            },
        })
        vi.mocked(webClient.put).mockResolvedValueOnce({
            data: {
                data: {
                    flag: {
                        id: 4,
                        key: 'beta-export',
                        name: 'Beta Export',
                        description: 'Enabled for beta users.',
                        enabled: true,
                        rolloutPercent: 20,
                    },
                },
            },
        })
        vi.mocked(webClient.delete).mockResolvedValueOnce({
            data: {
                data: {
                    deleted: true,
                },
            },
        })
        vi.mocked(webClient.get).mockResolvedValueOnce({
            data: {
                data: {
                    items: ['beta-export'],
                },
            },
        })

        const service = new AdminFeatureFlagsService()

        await expect(service.create('/management/api/feature-flags', {
            key: 'beta-export',
            name: 'Beta Export',
            description: null,
            enabled: false,
            rolloutPercent: 0,
        })).resolves.toEqual({
            id: 4,
            key: 'beta-export',
            name: 'Beta Export',
            description: null,
            enabled: false,
            rolloutPercent: 0,
        })
        await expect(service.update('/management/api/feature-flags', 4, {
            key: 'beta-export',
            name: 'Beta Export',
            description: 'Enabled for beta users.',
            enabled: true,
            rolloutPercent: 20,
        })).resolves.toEqual({
            id: 4,
            key: 'beta-export',
            name: 'Beta Export',
            description: 'Enabled for beta users.',
            enabled: true,
            rolloutPercent: 20,
        })
        await expect(service.delete('/management/api/feature-flags', 4)).resolves.toBe(true)
        await expect(service.loadSuggestions('/management/api/feature-flags/suggestions', 'beta')).resolves.toEqual(['beta-export'])
    })
})
