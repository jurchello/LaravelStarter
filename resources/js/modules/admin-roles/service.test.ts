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
import { AdminRolesService } from './service'

describe('AdminRolesService', () => {
    it('returns paginated roles from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValue({
            data: {
                data: {
                    items: [
                        {
                            id: 3,
                            name: 'Manager',
                            usersCount: 4,
                            permissions: ['admin.users.index'],
                        },
                    ],
                    availableNames: ['Admin', 'Developer', 'Manager'],
                    availablePermissions: ['admin.users.index', 'admin.roles.index'],
                },
                meta: {
                    page: 1,
                    perPage: 50,
                    total: 1,
                    totalPages: 1,
                },
            },
        })

        const service = new AdminRolesService()

        await expect(service.load('/management/api/roles', {
            page: 1,
            sort: 'name',
            direction: 'asc',
            search: 'man',
        })).resolves.toEqual({
            items: [
                {
                    id: 3,
                    name: 'Manager',
                    usersCount: 4,
                    permissions: ['admin.users.index'],
                },
            ],
            availableNames: ['Admin', 'Developer', 'Manager'],
            availablePermissions: ['admin.users.index', 'admin.roles.index'],
            page: 1,
            perPage: 50,
            total: 1,
            totalPages: 1,
        })
    })

    it('creates, updates, deletes, and suggests roles via the admin api envelope', async () => {
        vi.mocked(webClient.post).mockResolvedValueOnce({
            data: {
                data: {
                    role: {
                        id: 5,
                        name: 'Developer',
                        usersCount: 0,
                        permissions: [],
                    },
                },
            },
        })
        vi.mocked(webClient.put).mockResolvedValueOnce({
            data: {
                data: {
                    role: {
                        id: 5,
                        name: 'Engineer',
                        usersCount: 0,
                        permissions: [],
                    },
                },
            },
        })
        vi.mocked(webClient.patch).mockResolvedValueOnce({
            data: {
                data: {
                    role: {
                        id: 5,
                        name: 'Engineer',
                        usersCount: 0,
                        permissions: ['admin.users.index'],
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
                    items: ['Developer'],
                },
            },
        })

        const service = new AdminRolesService()

        await expect(service.create('/management/api/roles', 'Developer')).resolves.toEqual({
            id: 5,
            name: 'Developer',
            usersCount: 0,
            permissions: [],
        })
        await expect(service.update('/management/api/roles', 5, 'Engineer')).resolves.toEqual({
            id: 5,
            name: 'Engineer',
            usersCount: 0,
            permissions: [],
        })
        await expect(service.updatePermissions('/management/api/roles', 5, ['admin.users.index'])).resolves.toEqual({
            id: 5,
            name: 'Engineer',
            usersCount: 0,
            permissions: ['admin.users.index'],
        })
        await expect(service.delete('/management/api/roles', 5)).resolves.toBe(true)
        await expect(service.loadSuggestions('/management/api/roles/suggestions', 'dev')).resolves.toEqual(['Developer'])
    })
})
