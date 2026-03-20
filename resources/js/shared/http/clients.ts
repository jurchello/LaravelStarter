import axios from 'axios'
import type { AxiosInstance } from 'axios'

const apiClient: AxiosInstance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
})

function applyCsrfToken(client: AxiosInstance): void {
    const meta = document.head.querySelector<HTMLMetaElement>('meta[name="csrf-token"]')
    if (meta?.content) {
        client.defaults.headers.common['X-CSRF-TOKEN'] = meta.content
    }
}

export function initializeHttpClients(): void {
    axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
    applyCsrfToken(axios)
}

export function setApiClientToken(token: string | null): void {
    if (typeof token === 'string' && token.trim() !== '') {
        apiClient.defaults.headers.common.Authorization = `Bearer ${token}`
        return
    }

    delete apiClient.defaults.headers.common.Authorization
}

export { axios as webClient, apiClient }