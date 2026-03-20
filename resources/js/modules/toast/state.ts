export type ToastType = 'success' | 'info' | 'warning' | 'danger'

export type ToastItem = {
    id: number
    type: ToastType
    title: string | null
    message: string
    details: string | null
}

export type ToastState = {
    items: ToastItem[]
}
