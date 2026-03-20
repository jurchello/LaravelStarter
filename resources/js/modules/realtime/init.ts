import { init as initModule } from './module'
import { initIntegrations } from './bootstrap'

const init = (): void | Promise<void> => {
    return initModule()
}

const integrate = (): void => {
    initIntegrations()
}

// const defer = (): void | Promise<void> => {
//     // optional: non-critical lazy work after all modules are ready
// }

export { init, integrate }
