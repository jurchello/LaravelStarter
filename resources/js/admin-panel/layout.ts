import { ensureHttpReady } from '@/shared/http/bootstrap'
import { connectModules } from '@/core/connect'
import {
    initAdminAbTestAnalyticsPage,
    initAdminAbTestAssignmentsPage,
    initAdminAbTestCreatePage,
    initAdminAbTestEventsPage,
    initAdminAbTestManagementPage,
    initAdminAbTestsPage,
} from '@/pages/admin-panel/ab-tests/connect'
import { initAdminDashboardPage } from '@/pages/admin-panel/dashboard/connect'
import { initAdminFeatureFlagsPage } from '@/pages/admin-panel/feature-flags/connect'
import { initAdminRolesPage } from '@/pages/admin-panel/roles/connect'
import { initAdminUsersPage } from '@/pages/admin-panel/users/connect'

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

document.addEventListener('DOMContentLoaded', () => {
    ensureHttpReady()
    void connectModules()
    initAdminSidebar()

    const page = document.querySelector<HTMLElement>('[data-admin-page]')

    switch (page?.dataset.adminPage) {
        case 'ab-test-create':
            void initAdminAbTestCreatePage(page)
            break
        case 'ab-test-assignments':
            void initAdminAbTestAssignmentsPage(page)
            break
        case 'ab-test-events':
            void initAdminAbTestEventsPage(page)
            break
        case 'ab-test-analytics':
            void initAdminAbTestAnalyticsPage(page)
            break
        case 'ab-test-management':
            void initAdminAbTestManagementPage(page)
            break
        case 'ab-tests':
            void initAdminAbTestsPage(page)
            break
        case 'dashboard':
            void initAdminDashboardPage(page)
            break
        case 'feature-flags':
            void initAdminFeatureFlagsPage(page)
            break
        case 'roles':
            void initAdminRolesPage(page)
            break
        case 'users':
            void initAdminUsersPage(page)
            break
        case 'ui-kit':
            void import('@/pages/admin-panel/ui-kit/connect').then(({ initAdminUiKitPage }) => initAdminUiKitPage(page))
            break
        default:
            break
    }
})
