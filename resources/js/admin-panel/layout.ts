import { connectModules } from '@/core/connect'
import { connectCurrentPage } from '@/pages/connect'
import {
    markAppBooting,
    markAppFinalReady,
} from '@/shared/runtime-state/module'

function initAdminSidebar(): void {
    const nodes = document.querySelectorAll<HTMLElement>('[data-admin-menu-node]')

    nodes.forEach((node) => {
        const toggle = node.querySelector<HTMLButtonElement>('[data-admin-menu-toggle]')
        const submenu = node.querySelector<HTMLElement>('.admin-submenu')

        if (!toggle || !submenu) {
            return
        }

        toggle.addEventListener('click', () => {
            const isOpen = node.classList.toggle('is-open')

            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false')
            submenu.hidden = !isOpen
        })
    })
}

async function bootstrapAdmin(): Promise<void> {
    markAppBooting()
    await connectModules()
    initAdminSidebar()
    await connectCurrentPage()

    markAppFinalReady()
}

document.addEventListener('DOMContentLoaded', () => {
    void bootstrapAdmin()
})
