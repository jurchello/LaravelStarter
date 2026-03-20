import { ensureHttpReady } from '@/shared/http/bootstrap'
import * as runtimeState from '@/shared/runtime-state/init'
import { markAppBasicReady } from '@/shared/runtime-state/module'
import * as i18n from '@/modules/i18n/init'
import * as toast from '@/modules/toast/init'
import { connectModuleSpecs, type ModuleSpec } from '@/core/module-connect'

type ConnectContext = {
    [key: string]: unknown
}

const systemModules: ModuleSpec[] = [
    {
        id: 'runtime-state',
        init: () => runtimeState.init(),
        integrate: () => runtimeState.integrate(),
    },
    {
        id: 'i18n',
        dependsOn: ['runtime-state'],
        init: () => i18n.init(),
        integrate: () => i18n.integrate(),
    },
    {
        id: 'toast',
        dependsOn: ['i18n'],
        init: () => toast.init(),
        integrate: () => toast.integrate(),
    },
]

let connectPromise: Promise<void> | null = null

export function connectModules(_context: ConnectContext = {}): Promise<void> {
    if (connectPromise) {
        return connectPromise
    }

    connectPromise = (async () => {
        ensureHttpReady()

        await connectModuleSpecs(systemModules)

        markAppBasicReady()
    })()

    return connectPromise
}
