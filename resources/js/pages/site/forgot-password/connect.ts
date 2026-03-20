import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'

export function connectSiteForgotPasswordPage(_root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('site-forgot-password', () => {}),
    ])
}
