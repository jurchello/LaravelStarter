import Echo from 'laravel-echo'
import Pusher from 'pusher-js'
import type { RealtimeConfig } from './state'

// Core logic for the realtime module.
// No DOM, no page wiring, no direct Blade assumptions.
//
// State lives inside the class — never in module-level variables.
// Tests create a new instance (new RealtimeService()) to get fresh isolated state.

export type { RealtimeConfig }

export type RealtimeEventHandler<TPayload = unknown> = (payload: TPayload) => void

type RealtimeEnv = {
    VITE_REVERB_APP_KEY?: string
    VITE_REVERB_HOST?: string
    VITE_REVERB_PORT?: string
    VITE_REVERB_SCHEME?: string
}

type EchoChannel = {
    listen: (event: string, callback: RealtimeEventHandler) => void
    stopListening: (event: string) => void
}

type EchoInstance = {
    private: (channel: string) => EchoChannel
    socketId: () => string | undefined
}

type EchoFactory = (config: RealtimeConfig) => EchoInstance

const CSRF_TOKEN_SELECTOR = 'meta[name="csrf-token"]'

function readCsrfToken(): string | null {
    return document.head.querySelector<HTMLMetaElement>(CSRF_TOKEN_SELECTOR)?.content ?? null
}

export function resolveRealtimeConfig(env: RealtimeEnv = import.meta.env): RealtimeConfig | null {
    const appKey = env.VITE_REVERB_APP_KEY?.trim()

    if (!appKey) {
        return null
    }

    const configuredHost = env.VITE_REVERB_HOST?.trim()
    const host = configuredHost && configuredHost !== '' ? configuredHost : window.location.hostname
    const scheme = env.VITE_REVERB_SCHEME === 'https' ? 'https' : 'http'
    const defaultPort = scheme === 'https' ? 443 : 80
    const port = Number(env.VITE_REVERB_PORT ?? defaultPort)

    return {
        appKey,
        host,
        port: Number.isNaN(port) ? defaultPort : port,
        scheme,
        authEndpoint: '/broadcasting/auth',
    }
}

function createEcho(config: RealtimeConfig): EchoInstance {
    window.Pusher = Pusher

    const csrfToken = readCsrfToken()

    return new Echo({
        broadcaster: 'reverb',
        key: config.appKey,
        wsHost: config.host,
        wsPort: config.port,
        wssPort: config.port,
        forceTLS: config.scheme === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: config.authEndpoint,
        auth: csrfToken
            ? {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                },
            }
            : undefined,
    }) as EchoInstance
}

function normalizeEventName(event: string): string {
    return event.startsWith('.') ? event : `.${event}`
}

export class RealtimeService
{
    private echo: EchoInstance | null = null

    public constructor(
        private readonly configResolver: () => RealtimeConfig | null = () => resolveRealtimeConfig(),
        private readonly echoFactory: EchoFactory = createEcho,
    ) {}

    public init(): void
    {
        // Realtime stays lazy until a page explicitly subscribes to a channel.
    }

    public getSocketId(): string | null
    {
        return this.echo?.socketId() ?? null
    }

    public subscribeToPrivateEvent<TPayload = unknown>(
        channelName: string,
        eventName: string,
        callback: RealtimeEventHandler<TPayload>,
    ): () => void {
        const echo = this.ensureEcho()

        if (!echo) {
            return () => {}
        }

        const channel = echo.private(channelName)
        const normalizedEventName = normalizeEventName(eventName)

        channel.listen(normalizedEventName, callback as RealtimeEventHandler)

        return () => {
            channel.stopListening(normalizedEventName)
        }
    }

    private ensureEcho(): EchoInstance | null
    {
        if (this.echo) {
            return this.echo
        }

        const config = this.configResolver()

        if (!config) {
            return null
        }

        this.echo = this.echoFactory(config)

        return this.echo
    }
}

const service = new RealtimeService()

// Production singleton — one shared instance for the running app.
// Public API — thin wrappers so callers import functions, not the class instance.
export const init = (): void => service.init()
export const getSocketId = (): string | null => service.getSocketId()
export const subscribeToPrivateEvent = <TPayload = unknown>(
    channelName: string,
    eventName: string,
    callback: RealtimeEventHandler<TPayload>,
): (() => void) => service.subscribeToPrivateEvent(channelName, eventName, callback)
