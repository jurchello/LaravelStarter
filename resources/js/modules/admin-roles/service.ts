import { webClient } from '@/shared/http/clients'

export type AdminRoleRecord = {
    id: number
    name: string
    usersCount: number
    permissions: string[]
}

export type AdminRolesSortKey = 'id' | 'name' | 'usersCount'
export type AdminRolesSortDirection = 'asc' | 'desc'
export type AdminRolesQueryState = {
    page: number
    sort: AdminRolesSortKey
    direction: AdminRolesSortDirection
    search: string
}

export type AdminRolesResult = {
    items: AdminRoleRecord[]
    availableNames: string[]
    availablePermissions: string[]
    page: number
    perPage: number
    total: number
    totalPages: number
}

type AdminRolesResponse = {
    data: {
        items: AdminRoleRecord[]
        availableNames: string[]
        availablePermissions: string[]
    }
    meta: {
        page: number
        perPage: number
        total: number
        totalPages: number
    }
}

type AdminRoleMutationResponse = {
    data: {
        role: AdminRoleRecord
    }
}

type AdminRoleDeleteResponse = {
    data: {
        deleted: boolean
    }
}

type AdminRoleSuggestionsResponse = {
    data: {
        items: string[]
    }
}

export class AdminRolesService {
    async load(endpoint: string, state: AdminRolesQueryState): Promise<AdminRolesResult> {
        const response = await webClient.get<AdminRolesResponse>(endpoint, { params: state })

        return {
            items: response.data.data.items,
            availableNames: response.data.data.availableNames,
            availablePermissions: response.data.data.availablePermissions,
            page: response.data.meta.page,
            perPage: response.data.meta.perPage,
            total: response.data.meta.total,
            totalPages: response.data.meta.totalPages,
        }
    }

    async loadSuggestions(endpoint: string, query: string): Promise<string[]> {
        const response = await webClient.get<AdminRoleSuggestionsResponse>(endpoint, {
            params: { query },
        })

        return response.data.data.items
    }

    async create(endpoint: string, name: string): Promise<AdminRoleRecord> {
        const response = await webClient.post<AdminRoleMutationResponse>(endpoint, { name })

        return response.data.data.role
    }

    async update(endpoint: string, roleId: number, name: string): Promise<AdminRoleRecord> {
        const response = await webClient.put<AdminRoleMutationResponse>(`${endpoint}/${roleId}`, { name })

        return response.data.data.role
    }

    async delete(endpoint: string, roleId: number): Promise<boolean> {
        const response = await webClient.delete<AdminRoleDeleteResponse>(`${endpoint}/${roleId}`)

        return response.data.data.deleted
    }

    async updatePermissions(endpoint: string, roleId: number, permissions: string[]): Promise<AdminRoleRecord> {
        const response = await webClient.patch<AdminRoleMutationResponse>(`${endpoint}/${roleId}/permissions`, {
            permissions,
        })

        return response.data.data.role
    }
}
