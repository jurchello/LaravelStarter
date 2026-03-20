import { AdminDashboardService } from '@/modules/admin-dashboard/service'

const service = new AdminDashboardService()

export async function initAdminDashboardPage(root: HTMLElement): Promise<void> {
    const endpoint = root.dataset.dashboardEndpoint
    const target = root.querySelector<HTMLElement>('[data-dashboard-stats]')

    if (!endpoint || !target) {
        return
    }

    const stats = await service.load(endpoint)

    target.innerHTML = stats.map((stat) => `
        <article class="admin-stat-card" data-tone="${stat.tone}">
            <p class="admin-stat-card__label">${escapeHtml(stat.label)}</p>
            <p class="admin-stat-card__value">${stat.value}</p>
        </article>
    `).join('')
}

function escapeHtml(value: string): string {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
}
