import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'

export function connectSiteResetPasswordPage(_root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('site-reset-password', () => {}),
    ])
}
