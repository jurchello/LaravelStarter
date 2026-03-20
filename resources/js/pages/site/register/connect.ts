import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'

export function connectSiteRegisterPage(_root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('site-register', () => {}),
    ])
}
