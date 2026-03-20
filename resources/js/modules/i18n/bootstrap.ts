// External wiring only — DOM event listeners that call module.ts.
// No business logic here.

let attached = false

export const initIntegrations = (): void => {
    if (attached) {
        return
    }

    attached = true
}
