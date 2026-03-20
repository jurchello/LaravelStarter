import { webClient } from '@/shared/http/clients'

export type DashboardStat = {
    label: string
    value: number
    tone: 'neutral' | 'success' | 'accent' | 'warning'
}

type DashboardResponse = {
    data: {
        stats: DashboardStat[]
    }
}

export class AdminDashboardService {
    async load(endpoint: string): Promise<DashboardStat[]> {
        const response = await webClient.get<DashboardResponse>(endpoint)
        return response.data.data.stats
    }
}
