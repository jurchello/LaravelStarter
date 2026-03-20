import { emitNotify } from '@/modules/toast/service'
import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'
import {
    AdminRolesService,
    type AdminRoleRecord,
    type AdminRolesQueryState,
    type AdminRolesSortDirection,
    type AdminRolesSortKey,
} from '@/modules/admin-roles/service'
import {
    setPageEmpty,
    setPageError,
    setPageLoading,
    setPageReady,
} from '@/shared/runtime-state/module'

const service = new AdminRolesService()
const SEARCH_DEBOUNCE_MS = 250

export function connectAdminRolesPage(root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('admin-roles', () => bootstrapAdminRolesPage(root)),
    ])
}

async function bootstrapAdminRolesPage(root: HTMLElement): Promise<void> {
    const endpoint = root.dataset.rolesEndpoint
    const suggestionsEndpoint = root.dataset.rolesSuggestionsEndpoint
    const permissionUpdateBase = root.dataset.rolesPermissionUpdateBase
    const currentSort = (root.dataset.rolesSort as AdminRolesSortKey | undefined) ?? 'name'
    const currentDirection = (root.dataset.rolesDirection as AdminRolesSortDirection | undefined) ?? 'asc'
    const tableBody = root.querySelector<HTMLElement>('[data-roles-table-body]')
    const pagination = root.querySelector<HTMLElement>('[data-roles-pagination]')
    const summary = root.querySelector<HTMLElement>('[data-roles-summary]')
    const empty = root.querySelector<HTMLElement>('[data-roles-empty]')
    const sortButtons = root.querySelectorAll<HTMLButtonElement>('[data-roles-sort-trigger]')
    const form = root.querySelector<HTMLFormElement>('[data-roles-form]')
    const formId = root.querySelector<HTMLInputElement>('[data-roles-form-id]')
    const formName = root.querySelector<HTMLInputElement>('[data-roles-form-name]')
    const formCancel = root.querySelector<HTMLButtonElement>('[data-roles-form-cancel]')
    const formSubmit = root.querySelector<HTMLButtonElement>('[data-roles-form-submit]')
    const searchInput = root.querySelector<HTMLInputElement>('#roles-search')
    const suggestionsList = root.querySelector<HTMLDataListElement>('[data-roles-search-suggestions]')
    const permissionsModal = root.querySelector<HTMLElement>('[data-roles-permissions-modal]')
    const permissionsForm = root.querySelector<HTMLFormElement>('[data-roles-permissions-form]')
    const permissionsFormId = root.querySelector<HTMLInputElement>('[data-roles-permissions-form-id]')
    const permissionsSelect = root.querySelector<HTMLSelectElement>('[data-roles-permissions-select]')
    const permissionsCancel = root.querySelector<HTMLButtonElement>('[data-roles-permissions-cancel]')

    if (
        !endpoint ||
        !suggestionsEndpoint ||
        !permissionUpdateBase ||
        !tableBody ||
        !pagination ||
        !summary ||
        !empty ||
        !form ||
        !formId ||
        !formName ||
        !formCancel ||
        !formSubmit ||
        !searchInput ||
        !suggestionsList ||
        !permissionsModal ||
        !permissionsForm ||
        !permissionsFormId ||
        !permissionsSelect ||
        !permissionsCancel
    ) {
        return
    }

    let state = getInitialState(currentSort, currentDirection, searchInput.value)
    let searchTimer: number | null = null
    let suggestionRequestId = 0
    let currentItems: AdminRoleRecord[] = []
    let availablePermissions: string[] = []

    const resetForm = (): void => {
        formId.value = ''
        formName.value = ''
        formSubmit.textContent = 'Save role'
    }

    syncSortButtons(sortButtons, state)
    syncRootState(root, tableBody, empty)

    const hydrateCurrentPageData = async (): Promise<void> => {
        if (currentItems.length > 0 || emptyIsVisible(empty)) {
            return
        }

        const result = await service.load(endpoint, state)

        currentItems = result.items
        availablePermissions = result.availablePermissions
    }

    const refresh = async (): Promise<void> => {
        setPageLoading(root)

        try {
            const result = await service.load(endpoint, state)

            applyRolesResult(result, {
                root,
                tableBody,
                pagination,
                summary,
                empty,
                sortButtons,
            }, state)
            currentItems = result.items
            availablePermissions = result.availablePermissions
        } catch {
            setPageError(root)
            emitNotify({
                type: 'error',
                message: 'Unable to refresh roles right now.',
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
            const sortKey = button.getAttribute('sort-key') as AdminRolesSortKey | null

            if (!sortKey) {
                return
            }

            void (async () => {
                const nextDirection: AdminRolesSortDirection =
                    state.sort === sortKey && state.direction === 'asc' ? 'desc' : 'asc'

                state = { ...state, sort: sortKey, direction: nextDirection, page: 1 }
                await refresh()
            })()
        })
    })

    form.addEventListener('submit', (event) => {
        event.preventDefault()

        void (async () => {
            const name = formName.value.trim()

            if (name === '') {
                emitNotify({
                    type: 'danger',
                    message: 'Role name is required.',
                })

                return
            }

            if (formId.value === '') {
                await service.create(endpoint, name)
                emitNotify({
                    type: 'success',
                    message: 'Role created.',
                })
            } else {
                await service.update(endpoint, Number(formId.value), name)
                emitNotify({
                    type: 'success',
                    message: 'Role updated.',
                })
            }

            resetForm()
            await refresh()
        })()
    })

    formCancel.addEventListener('click', () => {
        resetForm()
    })

    permissionsCancel.addEventListener('click', () => {
        closePermissionsModal(permissionsModal)
    })

    permissionsModal.addEventListener('click', (event) => {
        const target = event.target as HTMLElement | null

        if (target?.classList.contains('admin-modal__backdrop')) {
            closePermissionsModal(permissionsModal)
        }
    })

    searchInput.addEventListener('input', () => {
        if (searchTimer) {
            window.clearTimeout(searchTimer)
        }

        const query = searchInput.value.trim()
        const requestId = ++suggestionRequestId

        if (query.length >= 2) {
            void service.loadSuggestions(suggestionsEndpoint, query).then((suggestions) => {
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
            state = { ...state, search: query, page: 1 }
            void refresh()
        }, SEARCH_DEBOUNCE_MS)
    })

    tableBody.addEventListener('click', (event) => {
        const target = (event.target as HTMLElement | null)?.closest<HTMLButtonElement>('button')

        if (!target) {
            return
        }

        void (async () => {
            const roleEditId = Number(target.dataset.roleEdit)

            if (roleEditId > 0) {
                await hydrateCurrentPageData()

                const role = currentItems.find((item) => item.id === roleEditId)

                if (!role) {
                    emitNotify({
                        type: 'error',
                        message: 'Unable to load the selected role.',
                    })

                    return
                }

                formId.value = String(role.id)
                formName.value = role.name
                formSubmit.textContent = 'Update role'
                formName.focus()

                return
            }

            const roleDeleteId = Number(target.dataset.roleDelete)

            if (roleDeleteId > 0) {
                await service.delete(endpoint, roleDeleteId)
                emitNotify({
                    type: 'success',
                    message: 'Role deleted.',
                })
                await refresh()

                return
            }

            const editPermissionsId = Number(target.dataset.roleEditPermissions)

            if (editPermissionsId > 0) {
                await hydrateCurrentPageData()

                const role = currentItems.find((item) => item.id === editPermissionsId)

                if (!role) {
                    emitNotify({
                        type: 'error',
                        message: 'Unable to load role permissions right now.',
                    })

                    return
                }

                permissionsFormId.value = String(role.id)
                permissionsSelect.innerHTML = availablePermissions
                    .map((permission) => {
                        const selected = role.permissions.includes(permission) ? ' selected' : ''

                        return `<option value="${escapeHtml(permission)}"${selected}>${escapeHtml(permission)}</option>`
                    })
                    .join('')
                openPermissionsModal(permissionsModal)
            }
        })()
    })

    permissionsForm.addEventListener('submit', (event) => {
        event.preventDefault()

        void (async () => {
            const roleId = Number(permissionsFormId.value)

            if (!roleId) {
                return
            }

            const permissions = Array.from(permissionsSelect.selectedOptions).map((option) => option.value)

            await service.updatePermissions(permissionUpdateBase, roleId, permissions)
            closePermissionsModal(permissionsModal)
            emitNotify({
                type: 'success',
                message: 'Role permissions updated.',
            })
            await refresh()
        })()
    })
}

function applyRolesResult(
    result: Awaited<ReturnType<AdminRolesService['load']>>,
    elements: {
        root: HTMLElement
        tableBody: HTMLElement
        pagination: HTMLElement
        summary: HTMLElement
        empty: HTMLElement
        sortButtons: NodeListOf<HTMLButtonElement>
    },
    state: AdminRolesQueryState,
): void {
    elements.tableBody.innerHTML = result.items.map(renderRow).join('')
    elements.empty.classList.toggle('is-hidden', result.items.length > 0)
    elements.summary.textContent = `${result.total} total roles`
    elements.pagination.innerHTML = renderPagination(result.page, result.totalPages)
    syncSortButtons(elements.sortButtons, state)
    syncUrl(state)
    syncRootState(elements.root, elements.tableBody, elements.empty)
}

function renderRow(role: AdminRoleRecord): string {
    return `
        <tr data-testid="roles-table-row">
            <td>#${role.id}</td>
            <td>${escapeHtml(role.name)}</td>
            <td>${role.usersCount}</td>
            <td>${renderPermissions(role.permissions)}</td>
            <td>
                <div class="admin-row-actions">
                    <button class="admin-button" type="button" data-role-edit="${role.id}">Edit</button>
                    <button class="admin-button" type="button" data-role-edit-permissions="${role.id}">Permissions</button>
                    <button class="admin-button" type="button" data-role-delete="${role.id}">Delete</button>
                </div>
            </td>
        </tr>
    `
}

function renderPermissions(permissions: string[]): string {
    if (permissions.length === 0) {
        return '<span class="admin-badge admin-badge--muted">No permissions</span>'
    }

    return `<span class="admin-badge admin-badge--accent">${permissions.length} assigned</span>`
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
    sort: AdminRolesSortKey,
    direction: AdminRolesSortDirection,
    search: string,
): AdminRolesQueryState {
    const url = new URL(window.location.href)
    const page = Number(url.searchParams.get('page') ?? '1')

    return {
        page: Number.isNaN(page) ? 1 : page,
        sort,
        direction,
        search: url.searchParams.get('search') ?? search ?? '',
    }
}

function syncUrl(state: AdminRolesQueryState): void {
    const url = new URL(window.location.href)

    url.searchParams.set('page', String(state.page))
    url.searchParams.set('sort', state.sort)
    url.searchParams.set('direction', state.direction)

    if (state.search !== '') {
        url.searchParams.set('search', state.search)
    } else {
        url.searchParams.delete('search')
    }

    window.history.replaceState({}, '', url)
}

function syncSortButtons(buttons: NodeListOf<HTMLButtonElement>, state: AdminRolesQueryState): void {
    buttons.forEach((button) => {
        const sortKey = button.getAttribute('sort-key') as AdminRolesSortKey | null
        const isActive = sortKey === state.sort

        button.classList.toggle('is-active', isActive)
        button.classList.toggle('is-asc', isActive && state.direction === 'asc')
        button.classList.toggle('is-desc', isActive && state.direction === 'desc')
    })
}

function syncRootState(root: HTMLElement, tableBody: HTMLElement, empty: HTMLElement): void {
    if (tableBody.querySelector('[data-testid="roles-table-row"]')) {
        setPageReady(root)

        return
    }

    if (!empty.classList.contains('is-hidden')) {
        setPageEmpty(root)
    }
}

function emptyIsVisible(empty: HTMLElement): boolean {
    return !empty.classList.contains('is-hidden')
}

function escapeHtml(value: string): string {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
}

function openPermissionsModal(modal: HTMLElement): void {
    modal.hidden = false
}

function closePermissionsModal(modal: HTMLElement): void {
    modal.hidden = true
}
