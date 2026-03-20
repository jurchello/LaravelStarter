import { describe, expect, it, vi } from 'vitest'
import { RealtimeService, resolveRealtimeConfig } from './service'

describe('resolveRealtimeConfig', () => {
    it('returns null when the Reverb app key is missing', () => {
        expect(resolveRealtimeConfig({})).toBeNull()
    })

    it('normalizes configured Reverb connection values', () => {
        expect(resolveRealtimeConfig({
            VITE_REVERB_APP_KEY: 'app-key',
            VITE_REVERB_HOST: '127.0.0.1',
            VITE_REVERB_PORT: '8080',
            VITE_REVERB_SCHEME: 'http',
        })).toEqual({
            appKey: 'app-key',
            host: '127.0.0.1',
            port: 8080,
            scheme: 'http',
            authEndpoint: '/broadcasting/auth',
        })
    })
})

describe('RealtimeService', () => {
    it('subscribes to a private event and exposes the socket id', () => {
        const channel = {
            listen: vi.fn(),
            stopListening: vi.fn(),
        }
        const echo = {
            private: vi.fn(() => channel),
            socketId: vi.fn(() => '1234.5678'),
        }
        const service = new RealtimeService(
            () => ({
                appKey: 'app-key',
                host: '127.0.0.1',
                port: 8080,
                scheme: 'http',
                authEndpoint: '/broadcasting/auth',
            }),
            () => echo,
        )
        const handler = vi.fn()

        const unsubscribe = service.subscribeToPrivateEvent('admin.feature-flags', 'admin.feature-flags.changed', handler)

        expect(echo.private).toHaveBeenCalledWith('admin.feature-flags')
        expect(channel.listen).toHaveBeenCalledWith('.admin.feature-flags.changed', handler)
        expect(service.getSocketId()).toBe('1234.5678')

        unsubscribe()

        expect(channel.stopListening).toHaveBeenCalledWith('.admin.feature-flags.changed')
    })

    it('becomes a no-op when realtime is not configured', () => {
        const service = new RealtimeService(() => null)
        const unsubscribe = service.subscribeToPrivateEvent('admin.feature-flags', 'admin.feature-flags.changed', vi.fn())

        expect(service.getSocketId()).toBeNull()
        expect(unsubscribe).not.toThrow()
    })
})
