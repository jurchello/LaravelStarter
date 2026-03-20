import { describe, it, expect, beforeEach } from 'vitest'
import { ToastService } from './service'

describe('toast service', () => {
    let service: ToastService

    beforeEach(() => {
        service = new ToastService()
    })

    it('notify adds toast to state', () => {
        service.notify({ type: 'success', message: 'ok' })

        const state = service.getState()
        expect(state.items).toHaveLength(1)
        expect(state.items[0].type).toBe('success')
        expect(state.items[0].message).toBe('ok')
    })

    it('dismiss removes toast from state', () => {
        service.notify({ type: 'success', message: 'ok' })

        const { items } = service.getState()
        expect(items).toHaveLength(1)

        service.dismiss(items[0].id)

        expect(service.getState().items).toHaveLength(0)
    })
})