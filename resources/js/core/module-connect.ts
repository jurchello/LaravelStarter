export type ModuleSpec = {
    id: string
    dependsOn?: string[]
    init: () => void | Promise<void>
    integrate: () => void | Promise<void>
    defer?: () => void | Promise<void>
}

export async function connectModuleSpecs(modules: ModuleSpec[]): Promise<void> {
    const byId = new Map(modules.map((module) => [module.id, module]))
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

    const results = await Promise.allSettled(modules.map((module) => run(module.id)))
    const deferred = modules.filter((module): module is ModuleSpec & { defer: () => void | Promise<void> } =>
        typeof module.defer === 'function'
    )

    await Promise.allSettled(deferred.map((module) => Promise.resolve(module.defer())))

    const failed = results.find((result): result is PromiseRejectedResult => result.status === 'rejected')

    if (failed) {
        throw failed.reason
    }
}
