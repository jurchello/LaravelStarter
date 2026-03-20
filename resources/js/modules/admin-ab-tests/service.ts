import { webClient } from '@/shared/http/clients'

export type AdminAbTestRecord = {
    id: number
    name: string
    slug: string
    status: 'draft' | 'active' | 'paused' | 'finished'
    trafficPercent: number
    variantsCount: number
}

export type AdminAbTestVariantRecord = {
    id: number
    name: string
    slug: string
    weight: number
    assignmentsCount: number
}

export type AdminAbTestRecentAssignment = {
    id: number
    visitorId: string
    userId: number | null
    variantName: string
    variantSlug: string
    createdAt: string
}

export type AdminAbTestRecentEvent = {
    id: number
    event: string
    variantName: string
    variantSlug: string
    visitorId: string
    createdAt: string
}

export type AdminAbTestManagementRecord = {
    id: number
    name: string
    slug: string
    status: 'draft' | 'active' | 'paused' | 'finished'
    trafficPercent: number
    distributionMode: 'manual' | 'equal'
    variants: AdminAbTestVariantRecord[]
    analytics: {
        assignmentsCount: number
        identifiedAssignmentsCount: number
        eventsByName: Record<string, number>
    }
    recentAssignments: AdminAbTestRecentAssignment[]
    recentEvents: AdminAbTestRecentEvent[]
}

export type AdminAbTestMutationPayload = {
    name: string
    slug?: string
    trafficPercent: number
    distributionMode: 'manual' | 'equal'
}

export type AdminAbTestAudienceEstimate = {
    audienceSize: number
    trafficPercent: number
    estimatedPeople: number
}

export type AdminAbTestVariantPayload = {
    name: string
    slug?: string
    weight: number
}

export type AdminAbTestsSortKey = 'name' | 'slug' | 'status' | 'trafficPercent' | 'variantsCount'
export type AdminAbTestsSortDirection = 'asc' | 'desc'
export type AdminAbTestsStatusFilter = 'all' | 'draft' | 'active' | 'paused' | 'finished'

export type AdminAbTestsQueryState = {
    page: number
    sort: AdminAbTestsSortKey
    direction: AdminAbTestsSortDirection
    search: string
    status: AdminAbTestsStatusFilter
}

export type AdminAbTestsResult = {
    items: AdminAbTestRecord[]
    page: number
    perPage: number
    total: number
    totalPages: number
}

export type AdminAbTestAssignmentsResult = {
    items: AdminAbTestRecentAssignment[]
    page: number
    perPage: number
    total: number
    totalPages: number
}

export type AdminAbTestEventsResult = {
    items: AdminAbTestRecentEvent[]
    page: number
    perPage: number
    total: number
    totalPages: number
}

type AdminAbTestsResponse = {
    data: {
        items: AdminAbTestRecord[]
    }
    meta: {
        page: number
        perPage: number
        total: number
        totalPages: number
    }
}

type AdminAbTestSuggestionsResponse = {
    data: {
        items: string[]
    }
}

type AdminAbTestManagementResponse = {
    data: AdminAbTestManagementRecord
}

type AdminAbTestAudienceEstimateResponse = {
    data: AdminAbTestAudienceEstimate
}

type AdminAbTestAssignmentsResponse = {
    data: {
        items: AdminAbTestRecentAssignment[]
    }
    meta: {
        page: number
        perPage: number
        total: number
        totalPages: number
    }
}

type AdminAbTestEventsResponse = {
    data: {
        items: AdminAbTestRecentEvent[]
    }
    meta: {
        page: number
        perPage: number
        total: number
        totalPages: number
    }
}

export class AdminAbTestsService {
    async load(endpoint: string, state: AdminAbTestsQueryState): Promise<AdminAbTestsResult> {
        const response = await webClient.get<AdminAbTestsResponse>(endpoint, {
            params: state,
        })

        return {
            items: response.data.data.items,
            page: response.data.meta.page,
            perPage: response.data.meta.perPage,
            total: response.data.meta.total,
            totalPages: response.data.meta.totalPages,
        }
    }

    async loadSuggestions(endpoint: string, query: string): Promise<string[]> {
        const response = await webClient.get<AdminAbTestSuggestionsResponse>(endpoint, {
            params: { query },
        })

        return response.data.data.items
    }

    async loadDetail(endpoint: string): Promise<AdminAbTestManagementRecord> {
        const response = await webClient.get<AdminAbTestManagementResponse>(endpoint)

        return response.data.data
    }

    async loadAudienceEstimate(endpoint: string, trafficPercent: number): Promise<AdminAbTestAudienceEstimate> {
        const response = await webClient.get<AdminAbTestAudienceEstimateResponse>(endpoint, {
            params: { trafficPercent },
        })

        return response.data.data
    }

    async create(endpoint: string, payload: AdminAbTestMutationPayload): Promise<AdminAbTestManagementRecord> {
        const response = await webClient.post<AdminAbTestManagementResponse>(endpoint, payload)

        return response.data.data
    }

    async update(endpoint: string, payload: AdminAbTestMutationPayload): Promise<AdminAbTestManagementRecord> {
        const response = await webClient.put<AdminAbTestManagementResponse>(endpoint, payload)

        return response.data.data
    }

    async remove(endpoint: string): Promise<void> {
        await webClient.delete(endpoint)
    }

    async updateStatus(endpoint: string, status: AdminAbTestRecord['status']): Promise<AdminAbTestManagementRecord> {
        const response = await webClient.patch<AdminAbTestManagementResponse>(endpoint, { status })

        return response.data.data
    }

    async createVariant(endpoint: string, payload: AdminAbTestVariantPayload): Promise<AdminAbTestManagementRecord> {
        const response = await webClient.post<AdminAbTestManagementResponse>(endpoint, payload)

        return response.data.data
    }

    async updateVariant(endpoint: string, payload: AdminAbTestVariantPayload): Promise<AdminAbTestManagementRecord> {
        const response = await webClient.put<AdminAbTestManagementResponse>(endpoint, payload)

        return response.data.data
    }

    async removeVariant(endpoint: string): Promise<AdminAbTestManagementRecord> {
        const response = await webClient.delete<AdminAbTestManagementResponse>(endpoint)

        return response.data.data
    }

    async loadAssignments(endpoint: string, page = 1): Promise<AdminAbTestAssignmentsResult> {
        const response = await webClient.get<AdminAbTestAssignmentsResponse>(endpoint, {
            params: { page },
        })

        return {
            items: response.data.data.items,
            page: response.data.meta.page,
            perPage: response.data.meta.perPage,
            total: response.data.meta.total,
            totalPages: response.data.meta.totalPages,
        }
    }

    async loadEvents(endpoint: string, page = 1): Promise<AdminAbTestEventsResult> {
        const response = await webClient.get<AdminAbTestEventsResponse>(endpoint, {
            params: { page },
        })

        return {
            items: response.data.data.items,
            page: response.data.meta.page,
            perPage: response.data.meta.perPage,
            total: response.data.meta.total,
            totalPages: response.data.meta.totalPages,
        }
    }
}
