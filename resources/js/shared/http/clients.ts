import axios from 'axios'
import type { AxiosInstance } from 'axios'

const apiClient: AxiosInstance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
})

let socketIdResolver: (() => string | null) | null = null

function applyCsrfToken(client: AxiosInstance): void {
    const meta = document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
    if (meta?.content) {
        client.defaults.headers.common['X-CSRF-TOKEN'] = meta.content
    }
}

function applySocketId(client: AxiosInstance): void {
    client.interceptors.request.use((config) => {
        const socketId = socketIdResolver?.()
        const headers = config.headers ?? {}

        if (typeof headers.set === 'function' && typeof headers.delete === 'function') {
            if (socketId) {
                headers.set('X-Socket-ID', socketId)
            } else {
                headers.delete('X-Socket-ID')
            }
        } else {
            if (socketId) {
                headers['X-Socket-ID'] = socketId
            } else {
                delete headers['X-Socket-ID']
            }
        }

        config.headers = headers

        return config
    })
}

export function initializeHttpClients(): void {
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
    applyCsrfToken(axios)
    applySocketId(axios)
    applySocketId(apiClient)
}

export function setApiClientToken(token: string | null): void {
    if (typeof token === 'string' && token.trim() !== '') {
        apiClient.defaults.headers.common.Authorization = `Bearer ${token}`
        return
    }

    delete apiClient.defaults.headers.common.Authorization
}

export function setSocketIdResolver(resolver: (() => string | null) | null): void {
    socketIdResolver = resolver
}

export { axios as webClient, apiClient }
