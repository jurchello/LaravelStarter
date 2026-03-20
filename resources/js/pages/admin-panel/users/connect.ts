import {
    type AdminUsersQueryState,
    AdminUsersService,
    type AdminUserRecord,
    type AdminUsersSortDirection,
    type AdminUsersSortKey,
} from '@/modules/admin-users/service'
import { emitNotify } from '@/modules/toast/service'

const service = new AdminUsersService()
const SEARCH_DEBOUNCE_MS = 250

export async function initAdminUsersPage(root: HTMLElement): Promise<void> {
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

    const render = async (): Promise<void> => {
        const result = await service.load(endpoint, state)
        currentItems = result.items
        assignableRoles = result.assignableRoles

        tableBody.innerHTML = result.items.map((user) => renderRow(user, impersonateBase)).join('')
        bindImpersonationActions(tableBody, impersonateBase)
        empty.classList.toggle('is-hidden', result.items.length > 0)
        summary.textContent = `${result.total} total users`
        syncRoleOptions(roleSelect, result.availableRoles, state.role)
        pagination.innerHTML = renderPagination(result.page, result.totalPages)
        syncSortButtons(sortButtons, state)
        syncUrl(state)
        bindRoleEditors(tableBody, currentItems, assignableRoles, roleModal, roleFormId, roleMultiSelect)
    }

    await render()

    bindPagination(pagination, () => state, async (page) => {
        state = { ...state, page }
        await render()
    })

    bindSorting(sortButtons, () => state, async (sort, direction) => {
        state = { ...state, sort, direction, page: 1 }
        await render()
    })

    roleSelect.addEventListener('change', async () => {
        state = {
            ...state,
            role: roleSelect.value,
            page: 1,
        }
        await render()
    })

    searchInput.addEventListener('input', async () => {
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

        searchTimer = window.setTimeout(async () => {
            state = {
                ...state,
                search: query,
                page: 1,
            }
            await render()
        }, SEARCH_DEBOUNCE_MS)
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

    roleForm.addEventListener('submit', async (event) => {
        event.preventDefault()

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
        await render()
    })
}

function renderRow(user: AdminUserRecord, impersonateBase: string): string {
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

function bindPagination(
    root: HTMLElement,
    getState: () => AdminUsersQueryState,
    onNavigate: (page: number) => Promise<void>,
): void {
    root.querySelectorAll<HTMLButtonElement>('[data-page-nav]').forEach((button) => {
        button.addEventListener('click', async () => {
            const currentPage = getState().page
            const direction = button.dataset.pageNav === 'next' ? 1 : -1
            await onNavigate(Math.max(1, currentPage + direction))
        })
    })
}

function bindSorting(
    buttons: NodeListOf<HTMLButtonElement>,
    getState: () => AdminUsersQueryState,
    onSort: (sort: AdminUsersSortKey, direction: AdminUsersSortDirection) => Promise<void>,
): void {
    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            const sortKey = button.getAttribute('sort-key') as AdminUsersSortKey | null

            if (!sortKey) {
                return
            }

            const currentState = getState()
            const currentSort = currentState.sort
            const currentDirection = currentState.direction
            const nextDirection: AdminUsersSortDirection = currentSort === sortKey && currentDirection === 'asc' ? 'desc' : 'asc'

            await onSort(sortKey, nextDirection)
        })
    })
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

function syncRoleOptions(select: HTMLSelectElement, availableRoles: string[], activeRole: string): void {
    const options = ['all', ...availableRoles]
    const labels = new Map<string, string>([
        ['all', 'All roles'],
    ])

    select.innerHTML = options
        .map((value) => {
            const label = labels.get(value) ?? value
            const selected = value === activeRole ? ' selected' : ''

            return `<option value="${escapeHtml(value)}"${selected}>${escapeHtml(label)}</option>`
        })
        .join('')
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

function bindRoleEditors(
    tableBody: HTMLElement,
    users: AdminUserRecord[],
    assignableRoles: string[],
    modal: HTMLElement,
    formId: HTMLInputElement,
    multiSelect: HTMLSelectElement,
): void {
    tableBody.querySelectorAll<HTMLButtonElement>('[data-user-edit-access]').forEach((button) => {
        button.addEventListener('click', () => {
            const userId = Number(button.dataset.userEditAccess)
            const user = users.find((item) => item.id === userId)

            if (!user) {
                return
            }

            formId.value = String(user.id)
            multiSelect.innerHTML = assignableRoles
                .map((role) => {
                    const selected = user.roles.includes(role) ? ' selected' : ''

                    return `<option value="${escapeHtml(role)}"${selected}>${escapeHtml(role)}</option>`
                })
                .join('')
            openRoleModal(modal)
        })
    })
}

function bindImpersonationActions(tableBody: HTMLElement, impersonateBase: string): void {
    tableBody.querySelectorAll<HTMLButtonElement>('[data-user-impersonate]').forEach((button) => {
        button.addEventListener('click', async () => {
            const userId = Number(button.dataset.userImpersonate)

            if (!userId) {
                return
            }

            const redirectTo = await service.impersonate(impersonateBase, userId)
            window.location.assign(redirectTo)
        })
    })
}

function openRoleModal(modal: HTMLElement): void {
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
