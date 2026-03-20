import type { ToastType, ToastItem, ToastState } from './state'

export type { ToastType, ToastState }

export type ToastContext = Record<string, never>

export type ToastNotifyPayload = {
    type: ToastType
    message: string
    title?: string | null
    details?: string | null
    durationMs?: number
}

export const TOAST_EVENT = 'app:toast:notify'

type Subscriber = (state: ToastState) => void

export class ToastService {
    private items: ToastItem[] = []
    private subscribers = new Set<Subscriber>()
    private sequence = 0
    private removalTimers = new Map<number, number>()

    getState(): ToastState {
        return { items: this.items.slice() }
    }

    subscribe(callback: Subscriber): () => void {
        this.subscribers.add(callback)
        callback(this.getState())
        return () => this.subscribers.delete(callback)
    }

    notify(payload: ToastNotifyPayload): void {
        const id = ++this.sequence
        this.items = [
            ...this.items,
            {
                id,
                type: payload.type,
                title: payload.title ?? null,
                message: payload.message,
                details: payload.details ?? null,
            },
        ]
        this.emit()

        const durationMs = payload.durationMs ?? 5000
        if (durationMs > 0) {
            const timerId = window.setTimeout(() => this.dismiss(id), durationMs)
            this.removalTimers.set(id, timerId)
        }
    }

    dismiss(id: number): void {
        const timerId = this.removalTimers.get(id)
        if (timerId !== undefined) {
            window.clearTimeout(timerId)
            this.removalTimers.delete(id)
        }

        const prev = this.items.length
        this.items = this.items.filter((item) => item.id !== id)
        if (this.items.length !== prev) {
            this.emit()
        }
    }

    private emit(): void {
        const state = this.getState()
        this.subscribers.forEach((sub) => sub(state))
    }
}

const service = new ToastService()

export const init = (): Promise<void> => Promise.resolve()
export const notify = (payload: ToastNotifyPayload): void => service.notify(payload)
export const dismiss = (id: number): void => service.dismiss(id)
export const subscribeToUpdates = (callback: Subscriber): (() => void) => service.subscribe(callback)
export const emitNotify = (payload: ToastNotifyPayload): void => {
    window.dispatchEvent(new CustomEvent<ToastNotifyPayload>(TOAST_EVENT, { detail: payload }))
}
