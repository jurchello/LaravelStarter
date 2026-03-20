import { ensureHttpReady } from '@/shared/http/bootstrap'
import * as i18n from '@/modules/i18n/init'
import * as toast from '@/modules/toast/init'

type ModuleSpec = {
    id: string
    dependsOn?: string[]
    init: () => void | Promise<void>
    integrate: () => void | Promise<void>
    defer?: () => void | Promise<void>
}

type ConnectContext = {
    [key: string]: unknown
}

let connectPromise: Promise<void> | null = null

async function runModules(modules: ModuleSpec[]): Promise<void> {
    const byId = new Map(modules.map((m) => [m.id, m]))
    const done = new Map<string, Promise<void>>()

    const run = (id: string): Promise<void> => {
        const cached = done.get(id)
        if (cached) {
            return cached
        }

        const module = byId.get(id)
        if (!module) {
            return Promise.reject(new Error(`[connect] Unknown module: ${id}`))
        }

        const deps = module.dependsOn ?? []
        const promise = Promise.all(deps.map(run))
            .then(() => module.init())
            .then(() => module.integrate())

        done.set(id, promise)
        return promise
    }

    const results = await Promise.allSettled(modules.map((m) => run(m.id)))
    const deferred = modules.filter((m): m is ModuleSpec & { defer: () => void | Promise<void> } =>
        typeof m.defer === 'function'
    )
    await Promise.allSettled(deferred.map((m) => Promise.resolve(m.defer())))

    const failed = results.find((r): r is PromiseRejectedResult => r.status === 'rejected')
    if (failed) {
        throw failed.reason
    }
}

export function connectModules(_context: ConnectContext = {}): Promise<void> {
    if (connectPromise) {
        return connectPromise
    }

    connectPromise = (async () => {
        ensureHttpReady()

        await runModules([
            {
                id: 'i18n',
                init: () => i18n.init({}),
                integrate: () => i18n.integrate(),
            },
            {
                id: 'toast',
                dependsOn: ['i18n'],
                init: () => toast.init({}),
                integrate: () => toast.integrate(),
            },
        ])
    })()

    return connectPromise
}