import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'

export function connectSiteWelcomePage(_root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('site-welcome', () => {}),
    ])
}
