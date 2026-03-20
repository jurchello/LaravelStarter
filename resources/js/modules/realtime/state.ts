// Types for the realtime module state and configuration.
// No variables, no functions — runtime state lives in the service class.

export type RealtimeConfig = {
    appKey: string
    host: string
    port: number
    scheme: 'http' | 'https'
    authEndpoint: string
}
