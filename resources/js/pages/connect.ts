type PageRoot = HTMLElement
type PageConnector = (root: PageRoot) => Promise<void> | void
type PageConnectorLoader = () => Promise<PageConnector>

const pageConnectors: Record<string, PageConnectorLoader> = {
    'admin:ab-test-analytics': () => import('@/pages/admin-panel/ab-tests/connect').then((module) => module.connectAdminAbTestAnalyticsPage),
    'admin:ab-test-assignments': () => import('@/pages/admin-panel/ab-tests/connect').then((module) => module.connectAdminAbTestAssignmentsPage),
    'admin:ab-test-create': () => import('@/pages/admin-panel/ab-tests/connect').then((module) => module.connectAdminAbTestCreatePage),
    'admin:ab-test-events': () => import('@/pages/admin-panel/ab-tests/connect').then((module) => module.connectAdminAbTestEventsPage),
    'admin:ab-test-management': () => import('@/pages/admin-panel/ab-tests/connect').then((module) => module.connectAdminAbTestManagementPage),
    'admin:ab-tests': () => import('@/pages/admin-panel/ab-tests/connect').then((module) => module.connectAdminAbTestsPage),
    'admin:dashboard': () => import('@/pages/admin-panel/dashboard/connect').then((module) => module.connectAdminDashboardPage),
    'admin:feature-flags': () => import('@/pages/admin-panel/feature-flags/connect').then((module) => module.connectAdminFeatureFlagsPage),
    'admin:roles': () => import('@/pages/admin-panel/roles/connect').then((module) => module.connectAdminRolesPage),
    'admin:ui-kit': () => import('@/pages/admin-panel/ui-kit/connect').then((module) => module.connectAdminUiKitPage),
    'admin:users': () => import('@/pages/admin-panel/users/connect').then((module) => module.connectAdminUsersPage),
    'site:dashboard': () => import('@/pages/site/dashboard/connect').then((module) => module.connectSiteDashboardPage),
    'site:forgot-password': () => import('@/pages/site/forgot-password/connect').then((module) => module.connectSiteForgotPasswordPage),
    'site:login': () => import('@/pages/site/login/connect').then((module) => module.connectSiteLoginPage),
    'site:register': () => import('@/pages/site/register/connect').then((module) => module.connectSiteRegisterPage),
    'site:reset-password': () => import('@/pages/site/reset-password/connect').then((module) => module.connectSiteResetPasswordPage),
    'site:verify-email': () => import('@/pages/site/verify-email/connect').then((module) => module.connectSiteVerifyEmailPage),
    'site:welcome': () => import('@/pages/site/welcome/connect').then((module) => module.connectSiteWelcomePage),
}

function getCurrentPage(): { key: string, root: PageRoot } | null {
    const adminRoot = document.querySelector<PageRoot>('[data-admin-page]')

    if (adminRoot) {
        const pageId = adminRoot.dataset.adminPage

        if (!pageId) {
            return null
        }

        return {
            key: `admin:${pageId}`,
            root: adminRoot,
        }
    }

    const siteRoot = document.querySelector<PageRoot>('[data-site-page]')

    if (!siteRoot) {
        return null
    }

    const pageId = siteRoot.dataset.sitePage

    if (!pageId) {
        return null
    }

    return {
        key: `site:${pageId}`,
        root: siteRoot,
    }
}

export async function connectCurrentPage(): Promise<void> {
    const currentPage = getCurrentPage()

    if (!currentPage) {
        return
    }

    const loadConnector = pageConnectors[currentPage.key]

    if (!loadConnector) {
        return
    }

    const connectPage = await loadConnector()

    await connectPage(currentPage.root)
}
