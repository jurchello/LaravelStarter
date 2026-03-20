import { webClient } from '@/shared/http/clients'
import type { I18nState } from './state'

export type { I18nState }

export type I18nContext = Record<string, never>

type Subscriber = (state: I18nState) => void

export class I18nService {
    private locale: string
    private dictionary: Record<string, string>
    private subscribers = new Set<Subscriber>()
    private initPromise: Promise<void> | null = null

    constructor(locale: string = 'en', dictionary: Record<string, string> = {}) {
        this.locale = locale
        this.dictionary = dictionary
    }

    getState(): I18nState {
        return { locale: this.locale, dictionary: { ...this.dictionary } }
    }

    subscribe(callback: Subscriber): () => void {
        this.subscribers.add(callback)
        callback(this.getState())
        return () => this.subscribers.delete(callback)
    }

    init(_context: I18nContext = {} as I18nContext): Promise<void> {
        if (this.initPromise) {
            return this.initPromise
        }

        this.initPromise = webClient
            .get<{ locale: string; dictionary: Record<string, string> }>('/api/i18n')
            .then((response) => {
                this.locale = response.data.locale
                this.dictionary = { ...response.data.dictionary }
                this.emit()
            })
            .catch((error) => {
                console.warn('[i18n] Failed to load translations, using empty dictionary', error)
                this.initPromise = null
            })

        return this.initPromise!
    }

    trans(key: string, fallback: string = key): string {
        return this.dictionary[key] ?? fallback
    }

    private emit(): void {
        const state = this.getState()
        this.subscribers.forEach((sub) => sub(state))
    }
}

const service = new I18nService(
    typeof document !== 'undefined' ? document.documentElement.lang || 'en' : 'en',
)

export const init = (context?: I18nContext): Promise<void> => service.init(context)
export const trans = (key: string, fallback?: string): string => service.trans(key, fallback)