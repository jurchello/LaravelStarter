import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'

export function connectSiteDashboardPage(_root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('site-dashboard', () => {}),
    ])
}
