import { describe, expect, it, vi } from 'vitest'

vi.mock('@/shared/http/clients', () => ({
    webClient: {
        get: vi.fn(),
        patch: vi.fn(),
        post: vi.fn(),
    },
}))

import { webClient } from '@/shared/http/clients'
import { AdminUsersService } from './service'

describe('AdminUsersService', () => {
    it('returns paginated users from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValue({
            data: {
                data: {
                    items: [
                        {
                            id: 7,
                            name: 'Admin',
                            email: 'admin@example.com',
                            isAdmin: true,
                            isSuperadmin: true,
                            roles: [],
                            registeredAt: '2026-03-19T07:00:00+00:00',
                        },
                    ],
                    roleFilters: [
                        { value: 'all', label: 'All roles' },
                        { value: 'superadmin', label: 'Superadmin' },
                        { value: 'Admin', label: 'Admin' },
                        { value: 'Manager', label: 'Manager' },
                    ],
                    assignableRoles: ['Admin', 'Manager'],
                },
                meta: {
                    page: 2,
                    perPage: 50,
                    total: 80,
                    totalPages: 2,
                },
            },
        })

        const service = new AdminUsersService()

        await expect(service.load('/management/api/users', {
            page: 2,
            sort: 'name',
            direction: 'asc',
            search: 'adm',
            role: 'Manager',
        })).resolves.toEqual({
            items: [
                {
                    id: 7,
                    name: 'Admin',
                    email: 'admin@example.com',
                    isAdmin: true,
                    isSuperadmin: true,
                    roles: [],
                    registeredAt: '2026-03-19T07:00:00+00:00',
                },
            ],
            roleFilters: [
                { value: 'all', label: 'All roles' },
                { value: 'superadmin', label: 'Superadmin' },
                { value: 'Admin', label: 'Admin' },
                { value: 'Manager', label: 'Manager' },
            ],
            assignableRoles: ['Admin', 'Manager'],
            page: 2,
            perPage: 50,
            total: 80,
            totalPages: 2,
        })

        expect(webClient.get).toHaveBeenCalledWith('/management/api/users', {
            params: {
                page: 2,
                sort: 'name',
                direction: 'asc',
                search: 'adm',
                role: 'Manager',
            },
        })
    })

    it('returns autocomplete suggestions from the admin api envelope', async () => {
        vi.mocked(webClient.get).mockResolvedValueOnce({
            data: {
                data: {
                    items: ['Alice Admin', 'alice@example.com'],
                },
            },
        })

        const service = new AdminUsersService()

        await expect(service.loadSuggestions('/management/api/users/suggestions', 'ali')).resolves.toEqual([
            'Alice Admin',
            'alice@example.com',
        ])

        expect(webClient.get).toHaveBeenCalledWith('/management/api/users/suggestions', {
            params: {
                query: 'ali',
            },
        })
    })

    it('updates user roles through the admin api envelope', async () => {
        vi.mocked(webClient.patch).mockResolvedValueOnce({
            data: {
                data: {
                    user: {
                        id: 7,
                        name: 'Admin',
                        email: 'admin@example.com',
                        isAdmin: false,
                        isSuperadmin: false,
                        roles: ['Developer', 'Manager'],
                        registeredAt: '2026-03-19T07:00:00+00:00',
                    },
                },
            },
        })

        const service = new AdminUsersService()

        await expect(service.updateRoles('/management/api/users', 7, ['Manager', 'Developer'])).resolves.toEqual({
            id: 7,
            name: 'Admin',
            email: 'admin@example.com',
            isAdmin: false,
            isSuperadmin: false,
            roles: ['Developer', 'Manager'],
            registeredAt: '2026-03-19T07:00:00+00:00',
        })
    })

    it('starts impersonation through the admin api envelope', async () => {
        vi.mocked(webClient.post).mockResolvedValueOnce({
            data: {
                data: {
                    redirect: {
                        redirectTo: '/dashboard',
                    },
                },
            },
        })

        const service = new AdminUsersService()

        await expect(service.impersonate('/management/api/users', 7)).resolves.toBe('/dashboard')

        expect(webClient.post).toHaveBeenCalledWith('/management/api/users/7/impersonation')
    })
})
