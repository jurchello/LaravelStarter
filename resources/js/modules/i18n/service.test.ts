import { describe, it, expect, vi } from 'vitest'
import { I18nService } from './service'

vi.mock('@/shared/http/clients', () => ({
    webClient: {
        get: vi.fn(),
    },
}))

describe('i18n service', () => {
    it('trans returns key as fallback when dictionary is empty', () => {
        const service = new I18nService()
        expect(service.trans('hello')).toBe('hello')
    })

    it('trans returns translated value', () => {
        const service = new I18nService('uk', { hello: 'Привіт' })
        expect(service.trans('hello')).toBe('Привіт')
    })

    it('trans returns custom fallback', () => {
        const service = new I18nService()
        expect(service.trans('missing', 'default')).toBe('default')
    })
})