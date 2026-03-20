import { webClient } from '@/shared/http/clients'

export type AdminFeatureFlagRecord = {
    id: number
    key: string
    name: string
    description: string | null
    enabled: boolean
    rolloutPercent: number
}

export type AdminFeatureFlagsSortKey = 'id' | 'key' | 'name' | 'enabled' | 'rolloutPercent'
export type AdminFeatureFlagsSortDirection = 'asc' | 'desc'
export type AdminFeatureFlagsQueryState = {
    page: number
    sort: AdminFeatureFlagsSortKey
    direction: AdminFeatureFlagsSortDirection
    search: string
    status: string
}

export type AdminFeatureFlagsResult = {
    items: AdminFeatureFlagRecord[]
    page: number
    perPage: number
    total: number
    totalPages: number
}

type AdminFeatureFlagsResponse = {
    data: {
        items: AdminFeatureFlagRecord[]
    }
    meta: {
        page: number
        perPage: number
        total: number
        totalPages: number
    }
}

type AdminFeatureFlagMutationResponse = {
    data: {
        flag: AdminFeatureFlagRecord
    }
}

type AdminFeatureFlagDeleteResponse = {
    data: {
        deleted: boolean
    }
}

type AdminFeatureFlagSuggestionsResponse = {
    data: {
        items: string[]
    }
}

export type AdminFeatureFlagPayload = {
    key: string
    name: string
    description: string | null
    enabled: boolean
    rolloutPercent: number
}

export class AdminFeatureFlagsService {
    async load(endpoint: string, state: AdminFeatureFlagsQueryState): Promise<AdminFeatureFlagsResult> {
        const response = await webClient.get<AdminFeatureFlagsResponse>(endpoint, { params: state })

        return {
            items: response.data.data.items,
            page: response.data.meta.page,
            perPage: response.data.meta.perPage,
            total: response.data.meta.total,
            totalPages: response.data.meta.totalPages,
        }
    }

    async loadSuggestions(endpoint: string, query: string): Promise<string[]> {
        const response = await webClient.get<AdminFeatureFlagSuggestionsResponse>(endpoint, {
            params: { query },
        })

        return response.data.data.items
    }

    async create(endpoint: string, payload: AdminFeatureFlagPayload): Promise<AdminFeatureFlagRecord> {
        const response = await webClient.post<AdminFeatureFlagMutationResponse>(endpoint, payload)

        return response.data.data.flag
    }

    async update(endpoint: string, id: number, payload: AdminFeatureFlagPayload): Promise<AdminFeatureFlagRecord> {
        const response = await webClient.put<AdminFeatureFlagMutationResponse>(`${endpoint}/${id}`, payload)

        return response.data.data.flag
    }

    async delete(endpoint: string, id: number): Promise<boolean> {
        const response = await webClient.delete<AdminFeatureFlagDeleteResponse>(`${endpoint}/${id}`)

        return response.data.data.deleted
    }
}
