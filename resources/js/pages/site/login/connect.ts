import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'

export function connectSiteLoginPage(_root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('site-login', () => {}),
    ])
}
