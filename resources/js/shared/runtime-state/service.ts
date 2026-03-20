import {
    APP_STATE_ATTRIBUTE,
    APP_STATE_BASIC_READY,
    APP_STATE_BOOTING,
    APP_STATE_FINAL_READY,
    PAGE_STATE_ATTRIBUTE,
    PAGE_STATE_EMPTY,
    PAGE_STATE_ERROR,
    PAGE_STATE_IDLE,
    PAGE_STATE_LOADING,
    PAGE_STATE_READY,
    type AppState,
    type PageState,
    type RuntimeStateState,
} from './state'

export type RuntimeStateContext = {
    appRoot?: HTMLElement
}

export type RuntimeStateSubscriber = (state: RuntimeStateState) => void

const APP_BASIC_READY_EVENT = 'app:basic-ready'
const APP_FINAL_READY_EVENT = 'app:final-ready'
const PAGE_READY_EVENT = 'page:ready'
const PAGE_EMPTY_EVENT = 'page:empty'
const PAGE_ERROR_EVENT = 'page:error'

export class RuntimeStateService {
    private appRoot: HTMLElement = document.documentElement

    private appState: AppState = APP_STATE_BOOTING

    private readonly subscribers = new Set<RuntimeStateSubscriber>()

    getState(): RuntimeStateState {
        return {
            appState: this.appState,
        }
    }

    subscribe(callback: RuntimeStateSubscriber): () => void {
        this.subscribers.add(callback)
        callback(this.getState())

        return () => {
            this.subscribers.delete(callback)
        }
    }

    init(context: RuntimeStateContext = {}): Promise<void> {
        if (context.appRoot) {
            this.appRoot = context.appRoot
        }

        this.syncAppState(APP_STATE_BOOTING)

        return Promise.resolve()
    }

    markAppBooting(): void {
        this.syncAppState(APP_STATE_BOOTING)
    }

    markAppBasicReady(): void {
        this.syncAppState(APP_STATE_BASIC_READY)
        document.dispatchEvent(new CustomEvent(APP_BASIC_READY_EVENT))
    }

    markAppFinalReady(): void {
        this.syncAppState(APP_STATE_FINAL_READY)
        document.dispatchEvent(new CustomEvent(APP_FINAL_READY_EVENT))
    }

    getAppState(): AppState {
        return this.readAppState()
    }

    isAppBasicReady(): boolean {
        const state = this.readAppState()

        return state === APP_STATE_BASIC_READY || state === APP_STATE_FINAL_READY
    }

    isAppFinalReady(): boolean {
        return this.readAppState() === APP_STATE_FINAL_READY
    }

    setPageIdle(root: HTMLElement): void {
        this.syncPageState(root, PAGE_STATE_IDLE)
    }

    setPageLoading(root: HTMLElement): void {
        this.syncPageState(root, PAGE_STATE_LOADING)
    }

    setPageReady(root: HTMLElement): void {
        this.syncPageState(root, PAGE_STATE_READY)
        root.dispatchEvent(new CustomEvent(PAGE_READY_EVENT, { bubbles: true }))
    }

    setPageEmpty(root: HTMLElement): void {
        this.syncPageState(root, PAGE_STATE_EMPTY)
        root.dispatchEvent(new CustomEvent(PAGE_EMPTY_EVENT, { bubbles: true }))
    }

    setPageError(root: HTMLElement): void {
        this.syncPageState(root, PAGE_STATE_ERROR)
        root.dispatchEvent(new CustomEvent(PAGE_ERROR_EVENT, { bubbles: true }))
    }

    getPageState(root: HTMLElement): PageState {
        return this.readPageState(root)
    }

    isPageReady(root: HTMLElement): boolean {
        const state = this.readPageState(root)

        return state === PAGE_STATE_READY || state === PAGE_STATE_EMPTY
    }

    private syncAppState(state: AppState): void {
        this.appState = state
        this.appRoot.setAttribute(APP_STATE_ATTRIBUTE, state)
        this.emit()
    }

    private readAppState(): AppState {
        const attribute = this.appRoot.getAttribute(APP_STATE_ATTRIBUTE)

        switch (attribute) {
            case APP_STATE_BASIC_READY:
            case APP_STATE_FINAL_READY:
                return attribute
            default:
                return APP_STATE_BOOTING
        }
    }

    private syncPageState(root: HTMLElement, state: PageState): void {
        root.setAttribute(PAGE_STATE_ATTRIBUTE, state)
        root.setAttribute('aria-busy', state === PAGE_STATE_LOADING ? 'true' : 'false')
    }

    private readPageState(root: HTMLElement): PageState {
        const attribute = root.getAttribute(PAGE_STATE_ATTRIBUTE)

        switch (attribute) {
            case PAGE_STATE_LOADING:
            case PAGE_STATE_READY:
            case PAGE_STATE_EMPTY:
            case PAGE_STATE_ERROR:
                return attribute
            default:
                return PAGE_STATE_IDLE
        }
    }

    private emit(): void {
        const snapshot = this.getState()

        this.subscribers.forEach((callback) => {
            callback(snapshot)
        })
    }
}

const service = new RuntimeStateService()

export const init = (context?: RuntimeStateContext): Promise<void> => service.init(context)
export const markAppBooting = (): void => service.markAppBooting()
export const markAppBasicReady = (): void => service.markAppBasicReady()
export const markAppFinalReady = (): void => service.markAppFinalReady()
export const getAppState = (): AppState => service.getAppState()
export const isAppBasicReady = (): boolean => service.isAppBasicReady()
export const isAppFinalReady = (): boolean => service.isAppFinalReady()
export const setPageIdle = (root: HTMLElement): void => service.setPageIdle(root)
export const setPageLoading = (root: HTMLElement): void => service.setPageLoading(root)
export const setPageReady = (root: HTMLElement): void => service.setPageReady(root)
export const setPageEmpty = (root: HTMLElement): void => service.setPageEmpty(root)
export const setPageError = (root: HTMLElement): void => service.setPageError(root)
export const getPageState = (root: HTMLElement): PageState => service.getPageState(root)
export const isPageReady = (root: HTMLElement): boolean => service.isPageReady(root)
export const subscribe = (callback: RuntimeStateSubscriber): (() => void) => service.subscribe(callback)
