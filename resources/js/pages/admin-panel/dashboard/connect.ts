import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'

export function connectAdminDashboardPage(root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('admin-dashboard', () => bootstrapAdminDashboardPage(root)),
    ])
}

async function bootstrapAdminDashboardPage(root: HTMLElement): Promise<void> {
    const target = root.querySelector<HTMLElement>('[data-dashboard-stats]')

    if (!target) {
        return
    }
}
