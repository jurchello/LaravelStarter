import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'

export function connectSiteVerifyEmailPage(_root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('site-verify-email', () => {}),
    ])
}
