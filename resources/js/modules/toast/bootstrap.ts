import { dismiss, notify, subscribeToUpdates, TOAST_EVENT } from './service'
import type { ToastNotifyPayload } from './service'
import type { ToastItem, ToastType } from './state'

let attached = false

const escapeHtml = (value: string): string =>
    value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')

const normalizeType = (type: ToastType): ToastType => type

const renderAdminToast = (item: ToastItem): string => {
    const title = item.title ? `<strong class="admin-toast__title">${escapeHtml(item.title)}</strong>` : ''
    const details = item.details ? `<div class="admin-toast__details">${escapeHtml(item.details)}</div>` : ''

    return `<article class="admin-toast admin-toast--${normalizeType(item.type)}" data-toast-id="${item.id}">${title}<div class="admin-toast__body">${escapeHtml(item.message)}</div>${details}</article>`
}

const renderSiteToast = (item: ToastItem): string => {
    const title = item.title ? `<strong class="d-block">${escapeHtml(item.title)}</strong>` : ''
    const details = item.details ? `<div class="small text-body-secondary">${escapeHtml(item.details)}</div>` : ''
    const variant = siteVariant(item.type)

    return `<article class="alert ${variant} shadow-sm mb-0" data-toast-id="${item.id}">${title}<div>${escapeHtml(item.message)}</div>${details}</article>`
}

const renderToContainers = (items: ToastItem[]): void => {
    const adminContainer = document.querySelector<HTMLElement>('[data-admin-toast-container]')
    const siteContainer = document.querySelector<HTMLElement>('[data-site-toast-container]')

    if (adminContainer) {
        adminContainer.innerHTML = items.map(renderAdminToast).join('')
    }

    if (siteContainer) {
        siteContainer.innerHTML = items.map(renderSiteToast).join('')
    }
}

const siteVariant = (type: ToastType): string => {
    switch (type) {
        case 'success':
            return 'alert-success'
        case 'warning':
            return 'alert-warning'
        case 'danger':
            return 'alert-danger'
        default:
            return 'alert-info'
    }
}

const hydrateFlashedToasts = (): void => {
    const nodes = document.querySelectorAll<HTMLElement>('[data-toast-payloads]')

    nodes.forEach((node) => {
        const content = node.dataset.toastPayloads?.trim()

        if (!content) {
            node.remove()
            return
        }

        try {
            const payloads = JSON.parse(content) as ToastNotifyPayload[]

            payloads.forEach((payload) => notify(payload))
        } finally {
            node.remove()
        }
    })
}

export const initIntegrations = (): void => {
    if (attached) {
        return
    }

    attached = true

    subscribeToUpdates((state) => {
        renderToContainers(state.items)
    })

    window.addEventListener(TOAST_EVENT, (event) => {
        const payload = (event as CustomEvent<ToastNotifyPayload>).detail
        notify(payload)
    })

    document.addEventListener('click', (event) => {
        const target = event.target as HTMLElement | null
        const toast = target?.closest<HTMLElement>('[data-toast-id]')

        if (!toast) {
            return
        }

        const id = Number(toast.dataset.toastId)

        if (!Number.isNaN(id)) {
            dismiss(id)
        }
    })

    hydrateFlashedToasts()
}
