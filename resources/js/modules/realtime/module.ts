// Thin public API facade — re-exports from service.ts only.
// No logic here.

export {
    getSocketId,
    init,
    subscribeToPrivateEvent,
} from './service'

export type {
    RealtimeConfig,
    RealtimeEventHandler,
} from './service'
