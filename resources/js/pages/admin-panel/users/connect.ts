import {
    type AdminUserRecord,
    type AdminUserRoleFilterOption,
    type AdminUsersQueryState,
    type AdminUsersResult,
    type AdminUsersSortDirection,
    type AdminUsersSortKey,
    AdminUsersService,
} from '@/modules/admin-users/service'
import { emitNotify } from '@/modules/toast/service'
import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'
import {
    setPageEmpty,
    setPageError,
    setPageLoading,
    setPageReady,
} from '@/shared/runtime-state/module'

const service = new AdminUsersService()
const SEARCH_DEBOUNCE_MS = 250

export function connectAdminUsersPage(root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('admin-users', () => bootstrapAdminUsersPage(root)),
    ])
}

async function bootstrapAdminUsersPage(root: HTMLElement): Promise<void> {
    const endpoint = root.dataset.usersEndpoint
    const suggestionsEndpoint = root.dataset.usersSuggestionsEndpoint
    const impersonateBase = root.dataset.usersImpersonateBase
    const roleUpdateBase = root.dataset.usersRoleUpdateBase
    const currentSort = (root.dataset.usersSort as AdminUsersSortKey | undefined) ?? 'registeredAt'
    const currentDirection = (root.dataset.usersDirection as AdminUsersSortDirection | undefined) ?? 'desc'
    const tableBody = root.querySelector<HTMLElement>('[data-users-table-body]')
    const pagination = root.querySelector<HTMLElement>('[data-users-pagination]')
    const summary = root.querySelector<HTMLElement>('[data-users-summary]')
    const empty = root.querySelector<HTMLElement>('[data-users-empty]')
    const sortButtons = root.querySelectorAll<HTMLButtonElement>('[data-users-sort-trigger]')
    const searchInput = root.querySelector<HTMLInputElement>('#users-search')
    const roleSelect = root.querySelector<HTMLSelectElement>('[data-users-role-filter]')
    const suggestionsList = root.querySelector<HTMLDataListElement>('[data-users-search-suggestions]')
    const roleModal = root.querySelector<HTMLElement>('[data-users-role-modal]')
    const roleForm = root.querySelector<HTMLFormElement>('[data-users-role-form]')
    const roleFormId = root.querySelector<HTMLInputElement>('[data-users-role-form-id]')
    const roleMultiSelect = root.querySelector<HTMLSelectElement>('[data-users-role-select]')
    const roleCancel = root.querySelector<HTMLButtonElement>('[data-users-role-cancel]')

    if (
        !endpoint ||
        !suggestionsEndpoint ||
        !roleUpdateBase ||
        !impersonateBase ||
        !tableBody ||
        !pagination ||
        !summary ||
        !empty ||
        !searchInput ||
        !roleSelect ||
        !suggestionsList ||
        !roleModal ||
        !roleForm ||
        !roleFormId ||
        !roleMultiSelect ||
        !roleCancel
    ) {
        return
    }

    let state = getInitialState(currentSort, currentDirection, searchInput.value, roleSelect.value)
    let searchTimer: number | null = null
    let suggestionRequestId = 0
    let currentItems: AdminUserRecord[] = []
    let assignableRoles: string[] = []

    syncSortButtons(sortButtons, state)
    syncRootState(root, tableBody, empty)

    const hydrateCurrentPageData = async (): Promise<void> => {
        if (currentItems.length > 0 || emptyIsVisible(empty)) {
            return
        }

        const result = await service.load(endpoint, state)

        currentItems = result.items
        assignableRoles = result.assignableRoles
    }

    const refresh = async (): Promise<void> => {
        setPageLoading(root)

        try {
            const result = await service.load(endpoint, state)

            applyUsersResult(result, {
                root,
                tableBody,
                pagination,
                summary,
                empty,
                roleSelect,
                sortButtons,
            }, state)
            currentItems = result.items
            assignableRoles = result.assignableRoles
        } catch {
            setPageError(root)
            emitNotify({
                type: 'error',
                message: 'Unable to refresh users right now.',
            })
        }
    }

    pagination.addEventListener('click', (event) => {
        const target = (event.target as HTMLElement | null)?.closest<HTMLButtonElement>('[data-page-nav]')

        if (!target) {
            return
        }

        void (async () => {
            const currentPage = state.page
            const direction = target.dataset.pageNav === 'next' ? 1 : -1

            state = { ...state, page: Math.max(1, currentPage + direction) }
            await refresh()
        })()
    })

    sortButtons.forEach((button) => {
        button.addEventListener('click', () => {
            const sortKey = button.getAttribute('sort-key') as AdminUsersSortKey | null

            if (!sortKey) {
                return
            }

            void (async () => {
                const nextDirection: AdminUsersSortDirection = state.sort === sortKey && state.direction === 'asc' ? 'desc' : 'asc'

                state = {
                    ...state,
                    sort: sortKey,
                    direction: nextDirection,
                    page: 1,
                }
                await refresh()
            })()
        })
    })

    roleSelect.addEventListener('change', () => {
        void (async () => {
            state = {
                ...state,
                role: roleSelect.value,
                page: 1,
            }
            await refresh()
        })()
    })

    searchInput.addEventListener('input', () => {
        if (searchTimer) {
            window.clearTimeout(searchTimer)
        }

        const query = searchInput.value.trim()
        const requestId = ++suggestionRequestId

        if (query.length >= 2) {
            void loadSuggestions(suggestionsEndpoint, query).then((suggestions) => {
                if (requestId !== suggestionRequestId) {
                    return
                }

                suggestionsList.innerHTML = suggestions
                    .map((item) => `<option value="${escapeHtml(item)}"></option>`)
                    .join('')
            })
        } else {
            suggestionsList.innerHTML = ''
        }

        searchTimer = window.setTimeout(() => {
            state = {
                ...state,
                search: query,
                page: 1,
            }
            void refresh()
        }, SEARCH_DEBOUNCE_MS)
    })

    tableBody.addEventListener('click', (event) => {
        const target = (event.target as HTMLElement | null)?.closest<HTMLButtonElement>('button')

        if (!target) {
            return
        }

        void (async () => {
            const impersonateId = Number(target.dataset.userImpersonate)

            if (impersonateId > 0) {
                const redirectTo = await service.impersonate(impersonateBase, impersonateId)
                window.location.assign(redirectTo)

                return
            }

            const editAccessId = Number(target.dataset.userEditAccess)

            if (editAccessId > 0) {
                await hydrateCurrentPageData()

                const user = currentItems.find((item) => item.id === editAccessId)

                if (!user) {
                    emitNotify({
                        type: 'error',
                        message: 'Unable to load access details for this user.',
                    })

                    return
                }

                openRoleModal(roleModal, roleFormId, roleMultiSelect, user, assignableRoles)
            }
        })()
    })

    roleCancel.addEventListener('click', () => {
        closeRoleModal(roleModal)
    })

    roleModal.addEventListener('click', (event) => {
        const target = event.target as HTMLElement | null

        if (target?.classList.contains('admin-modal__backdrop')) {
            closeRoleModal(roleModal)
        }
    })

    roleForm.addEventListener('submit', (event) => {
        event.preventDefault()

        void (async () => {
            const userId = Number(roleFormId.value)

            if (!userId) {
                return
            }

            const roles = Array.from(roleMultiSelect.selectedOptions).map((option) => option.value)

            await service.updateRoles(roleUpdateBase, userId, roles)
            closeRoleModal(roleModal)
            emitNotify({
                type: 'success',
                message: 'User access updated.',
            })
            await refresh()
        })()
    })
}

function applyUsersResult(
    result: AdminUsersResult,
    elements: {
        root: HTMLElement
        tableBody: HTMLElement
        pagination: HTMLElement
        summary: HTMLElement
        empty: HTMLElement
        roleSelect: HTMLSelectElement
        sortButtons: NodeListOf<HTMLButtonElement>
    },
    state: AdminUsersQueryState,
): void {
    elements.tableBody.innerHTML = result.items.map((user) => renderRow(user)).join('')
    elements.empty.classList.toggle('is-hidden', result.items.length > 0)
    elements.summary.textContent = `${result.total} total users`
    elements.pagination.innerHTML = renderPagination(result.page, result.totalPages)
    syncRoleOptions(elements.roleSelect, result.roleFilters, state.role)
    syncSortButtons(elements.sortButtons, state)
    syncUrl(state)
    syncRootState(elements.root, elements.tableBody, elements.empty)
}

function renderRow(user: AdminUserRecord): string {
    const formattedDate = new Date(user.registeredAt).toLocaleDateString('en-CA')
    const accessBadges = renderAccessBadges(user)

    return `
        <tr data-testid="users-table-row">
            <td>#${user.id}</td>
            <td>${escapeHtml(user.name)}</td>
            <td>${escapeHtml(user.email)}</td>
            <td>${accessBadges}</td>
            <td>${formattedDate}</td>
            <td>
                <div class="admin-row-actions">
                    <button class="admin-button" type="button" data-user-impersonate="${user.id}">Login as</button>
                    <button class="admin-button" type="button" data-user-edit-access="${user.id}">Edit access</button>
                </div>
            </td>
        </tr>
    `
}

function renderPagination(page: number, totalPages: number): string {
    return `
        <div class="admin-toolbar-meta">Page ${page} of ${totalPages}</div>
        <div class="admin-pagination__controls">
            <button class="admin-button" data-page-nav="prev" ${page <= 1 ? 'disabled' : ''}>Previous</button>
            <button class="admin-button admin-button--primary" data-page-nav="next" ${page >= totalPages ? 'disabled' : ''}>Next</button>
        </div>
    `
}

function getInitialState(
    sort: AdminUsersSortKey,
    direction: AdminUsersSortDirection,
    search: string,
    role: string,
): AdminUsersQueryState {
    const url = new URL(window.location.href)
    const page = Number(url.searchParams.get('page') ?? '1')

    return {
        page: Number.isNaN(page) ? 1 : page,
        sort,
        direction,
        search: url.searchParams.get('search') ?? search ?? '',
        role: url.searchParams.get('role') ?? role ?? 'all',
    }
}

function syncUrl(state: AdminUsersQueryState): void {
    const url = new URL(window.location.href)

    url.searchParams.set('page', String(state.page))
    url.searchParams.set('sort', state.sort)
    url.searchParams.set('direction', state.direction)

    if (state.search !== '') {
        url.searchParams.set('search', state.search)
    } else {
        url.searchParams.delete('search')
    }

    if (state.role !== 'all') {
        url.searchParams.set('role', state.role)
    } else {
        url.searchParams.delete('role')
    }

    window.history.replaceState({}, '', url)
}

function syncSortButtons(buttons: NodeListOf<HTMLButtonElement>, state: AdminUsersQueryState): void {
    buttons.forEach((button) => {
        const sortKey = button.getAttribute('sort-key') as AdminUsersSortKey | null
        const isActive = sortKey === state.sort

        button.classList.toggle('is-active', isActive)
        button.classList.toggle('is-asc', isActive && state.direction === 'asc')
        button.classList.toggle('is-desc', isActive && state.direction === 'desc')
    })
}

function syncRoleOptions(
    select: HTMLSelectElement,
    roleFilters: AdminUserRoleFilterOption[],
    activeRole: string,
): void {
    select.innerHTML = roleFilters
        .map((roleFilter) => {
            const selected = roleFilter.value === activeRole ? ' selected' : ''

            return `<option value="${escapeHtml(roleFilter.value)}"${selected}>${escapeHtml(roleFilter.label)}</option>`
        })
        .join('')
}

function syncRootState(root: HTMLElement, tableBody: HTMLElement, empty: HTMLElement): void {
    if (tableHasRows(tableBody)) {
        setPageReady(root)

        return
    }

    if (emptyIsVisible(empty)) {
        setPageEmpty(root)
    }
}

function tableHasRows(tableBody: HTMLElement): boolean {
    return tableBody.querySelector('[data-testid="users-table-row"]') !== null
}

function emptyIsVisible(empty: HTMLElement): boolean {
    return !empty.classList.contains('is-hidden')
}

function renderAccessBadges(user: AdminUserRecord): string {
    const badges: string[] = []

    if (user.isSuperadmin) {
        badges.push('<span class="admin-badge admin-badge--accent">Superadmin</span>')
    }

    user.roles.forEach((role) => {
        badges.push(`<span class="admin-badge admin-badge--muted">${escapeHtml(role)}</span>`)
    })

    if (badges.length === 0) {
        return '<span class="admin-badge admin-badge--muted">No roles</span>'
    }

    return `<div class="admin-applied-filters">${badges.join('')}</div>`
}

function openRoleModal(
    modal: HTMLElement,
    formId: HTMLInputElement,
    multiSelect: HTMLSelectElement,
    user: AdminUserRecord,
    assignableRoles: string[],
): void {
    formId.value = String(user.id)
    multiSelect.innerHTML = assignableRoles
        .map((role) => {
            const selected = user.roles.includes(role) ? ' selected' : ''

            return `<option value="${escapeHtml(role)}"${selected}>${escapeHtml(role)}</option>`
        })
        .join('')
    modal.hidden = false
}

function closeRoleModal(modal: HTMLElement): void {
    modal.hidden = true
}

async function loadSuggestions(endpoint: string, query: string): Promise<string[]> {
    return service.loadSuggestions(endpoint, query)
}

function escapeHtml(value: string): string {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
}
