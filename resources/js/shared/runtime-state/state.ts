export const APP_STATE_ATTRIBUTE = 'data-app-state'
export const PAGE_STATE_ATTRIBUTE = 'data-page-state'

export const APP_STATE_BOOTING = 'booting'
export const APP_STATE_BASIC_READY = 'basic-ready'
export const APP_STATE_FINAL_READY = 'final-ready'

export const PAGE_STATE_IDLE = 'idle'
export const PAGE_STATE_LOADING = 'loading'
export const PAGE_STATE_READY = 'ready'
export const PAGE_STATE_EMPTY = 'empty'
export const PAGE_STATE_ERROR = 'error'

export type AppState =
    | typeof APP_STATE_BOOTING
    | typeof APP_STATE_BASIC_READY
    | typeof APP_STATE_FINAL_READY

export type PageState =
    | typeof PAGE_STATE_IDLE
    | typeof PAGE_STATE_LOADING
    | typeof PAGE_STATE_READY
    | typeof PAGE_STATE_EMPTY
    | typeof PAGE_STATE_ERROR

export type RuntimeStateState = {
    appState: AppState
}
