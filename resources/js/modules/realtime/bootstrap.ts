import { getSocketId } from './service'
import { setSocketIdResolver } from '@/shared/http/clients'

// External wiring only — DOM / transport integration that calls module.ts.
// No business logic here.

let attached = false

export const initIntegrations = (): void => {
    if (attached) {
        return
    }

    attached = true
    setSocketIdResolver(() => getSocketId())
}
