import {
    connectModuleSpecs,
    type ModuleSpec,
} from '@/core/module-connect'

export type PageModuleSpec = ModuleSpec

export function definePageModule(id: string, connect: () => void | Promise<void>): PageModuleSpec {
    return {
        id,
        init: connect,
        integrate: () => {},
    }
}

export function connectPageModules(modules: PageModuleSpec[]): Promise<void> {
    return connectModuleSpecs(modules)
}
