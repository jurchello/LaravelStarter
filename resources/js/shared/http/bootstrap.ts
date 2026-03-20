import { initializeHttpClients } from './clients'

let ready = false

export function ensureHttpReady(): void {
    if (ready) {
        return
    }

    ready = true
    initializeHttpClients()
}