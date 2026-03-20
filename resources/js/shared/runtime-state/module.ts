// Thin public API facade — re-exports from service.ts only.
// No logic here.

export {
    init,
    markAppBooting,
    markAppBasicReady,
    markAppFinalReady,
    getAppState,
    isAppBasicReady,
    isAppFinalReady,
    setPageIdle,
    setPageLoading,
    setPageReady,
    setPageEmpty,
    setPageError,
    getPageState,
    isPageReady,
    subscribe,
} from './service'

export type {
    RuntimeStateContext,
    RuntimeStateSubscriber,
} from './service'
export type {
    AppState,
    PageState,
    RuntimeStateState,
} from './state'
export {
    APP_STATE_ATTRIBUTE,
    PAGE_STATE_ATTRIBUTE,
    APP_STATE_BOOTING,
    APP_STATE_BASIC_READY,
    APP_STATE_FINAL_READY,
    PAGE_STATE_IDLE,
    PAGE_STATE_LOADING,
    PAGE_STATE_READY,
    PAGE_STATE_EMPTY,
    PAGE_STATE_ERROR,
} from './state'
