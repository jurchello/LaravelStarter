import { webClient } from '@/shared/http/clients'

export type AdminUserRecord = {
    id: number
    name: string
    email: string
    isAdmin: boolean
    isSuperadmin: boolean
    roles: string[]
    registeredAt: string
}

export type AdminUsersResult = {
    items: AdminUserRecord[]
    roleFilters: AdminUserRoleFilterOption[]
    assignableRoles: string[]
    page: number
    perPage: number
    total: number
    totalPages: number
}

export type AdminUserRoleFilterOption = {
    value: string
    label: string
}

export type AdminUsersSortKey = 'id' | 'name' | 'email' | 'role' | 'registeredAt'
export type AdminUsersSortDirection = 'asc' | 'desc'
export type AdminUsersQueryState = {
    page: number
    sort: AdminUsersSortKey
    direction: AdminUsersSortDirection
    search: string
    role: string
}

type AdminUsersResponse = {
    data: {
        items: AdminUserRecord[]
        roleFilters: AdminUserRoleFilterOption[]
        assignableRoles: string[]
    }
    meta: {
        page: number
        perPage: number
        total: number
        totalPages: number
    }
}

type AdminUserSuggestionsResponse = {
    data: {
        items: string[]
    }
}

type AdminUserRoleMutationResponse = {
    data: {
        user: AdminUserRecord
    }
}

type AdminUserImpersonationResponse = {
    data: {
        redirect: {
            redirectTo: string
        }
    }
}

export class AdminUsersService {
    async load(endpoint: string, state: AdminUsersQueryState): Promise<AdminUsersResult> {
        const response = await webClient.get<AdminUsersResponse>(endpoint, {
            params: state,
        })

        return {
            items: response.data.data.items,
            roleFilters: response.data.data.roleFilters,
            assignableRoles: response.data.data.assignableRoles,
            page: response.data.meta.page,
            perPage: response.data.meta.perPage,
            total: response.data.meta.total,
            totalPages: response.data.meta.totalPages,
        }
    }

    async loadSuggestions(endpoint: string, query: string): Promise<string[]> {
        const response = await webClient.get<AdminUserSuggestionsResponse>(endpoint, {
            params: { query },
        })

        return response.data.data.items
    }

    async updateRoles(endpointBase: string, userId: number, roles: string[]): Promise<AdminUserRecord> {
        const response = await webClient.patch<AdminUserRoleMutationResponse>(
            `${endpointBase}/${userId}/roles`,
            { roles },
        )

        return response.data.data.user
    }

    async impersonate(endpointBase: string, userId: number): Promise<string> {
        const response = await webClient.post<AdminUserImpersonationResponse>(
            `${endpointBase}/${userId}/impersonation`,
        )

        return response.data.data.redirect.redirectTo
    }
}
