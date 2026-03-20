import { emitNotify } from '@/modules/toast/service'
import {
    connectPageModules,
    definePageModule,
} from '@/pages/module-connect'
import {
    AdminFeatureFlagsService,
    type AdminFeatureFlagPayload,
    type AdminFeatureFlagRecord,
    type AdminFeatureFlagsQueryState,
    type AdminFeatureFlagsSortDirection,
    type AdminFeatureFlagsSortKey,
} from '@/modules/admin-feature-flags/service'
import {
    setPageEmpty,
    setPageError,
    setPageLoading,
    setPageReady,
} from '@/shared/runtime-state/module'

const service = new AdminFeatureFlagsService()
const SEARCH_DEBOUNCE_MS = 250

export function connectAdminFeatureFlagsPage(root: HTMLElement): Promise<void> {
    return connectPageModules([
        definePageModule('admin-feature-flags', () => bootstrapAdminFeatureFlagsPage(root)),
    ])
}

async function bootstrapAdminFeatureFlagsPage(root: HTMLElement): Promise<void> {
    const endpoint = root.dataset.featureFlagsEndpoint
    const suggestionsEndpoint = root.dataset.featureFlagsSuggestionsEndpoint
    const currentSort = (root.dataset.featureFlagsSort as AdminFeatureFlagsSortKey | undefined) ?? 'name'
    const currentDirection = (root.dataset.featureFlagsDirection as AdminFeatureFlagsSortDirection | undefined) ?? 'asc'
    const tableBody = root.querySelector<HTMLElement>('[data-feature-flags-table-body]')
    const pagination = root.querySelector<HTMLElement>('[data-feature-flags-pagination]')
    const summary = root.querySelector<HTMLElement>('[data-feature-flags-summary]')
    const empty = root.querySelector<HTMLElement>('[data-feature-flags-empty]')
    const sortButtons = root.querySelectorAll<HTMLButtonElement>('[data-feature-flags-sort-trigger]')
    const form = root.querySelector<HTMLFormElement>('[data-feature-flags-form]')
    const formId = root.querySelector<HTMLInputElement>('[data-feature-flags-form-id]')
    const formKey = root.querySelector<HTMLInputElement>('[data-feature-flags-form-key]')
    const formName = root.querySelector<HTMLInputElement>('[data-feature-flags-form-name]')
    const formDescription = root.querySelector<HTMLTextAreaElement>('[data-feature-flags-form-description]')
    const formEnabled = root.querySelector<HTMLSelectElement>('[data-feature-flags-form-enabled]')
    const formRollout = root.querySelector<HTMLInputElement>('[data-feature-flags-form-rollout]')
    const formCancel = root.querySelector<HTMLButtonElement>('[data-feature-flags-form-cancel]')
    const formSubmit = root.querySelector<HTMLButtonElement>('[data-feature-flags-form-submit]')
    const searchInput = root.querySelector<HTMLInputElement>('#feature-flags-search')
    const statusSelect = root.querySelector<HTMLSelectElement>('[data-feature-flags-status-filter]')
    const suggestionsList = root.querySelector<HTMLDataListElement>('[data-feature-flags-search-suggestions]')

    if (
        !endpoint ||
        !suggestionsEndpoint ||
        !tableBody ||
        !pagination ||
        !summary ||
        !empty ||
        !form ||
        !formId ||
        !formKey ||
        !formName ||
        !formDescription ||
        !formEnabled ||
        !formRollout ||
        !formCancel ||
        !formSubmit ||
        !searchInput ||
        !statusSelect ||
        !suggestionsList
    ) {
        return
    }

    let state = getInitialState(currentSort, currentDirection, searchInput.value, statusSelect.value)
    let searchTimer: number | null = null
    let suggestionRequestId = 0
    let currentItems: AdminFeatureFlagRecord[] = []

    const resetForm = (): void => {
        formId.value = ''
        formKey.value = ''
        formName.value = ''
        formDescription.value = ''
        formEnabled.value = '0'
        formRollout.value = '0'
        formSubmit.textContent = 'Save flag'
    }

    syncSortButtons(sortButtons, state)
    syncRootState(root, tableBody, empty)

    const hydrateCurrentPageData = async (): Promise<void> => {
        if (currentItems.length > 0 || emptyIsVisible(empty)) {
            return
        }

        const result = await service.load(endpoint, state)

        currentItems = result.items
    }

    const refresh = async (): Promise<void> => {
        setPageLoading(root)

        try {
            const result = await service.load(endpoint, state)

            applyFeatureFlagsResult(result, {
                root,
                tableBody,
                pagination,
                summary,
                empty,
                sortButtons,
            }, state)
            currentItems = result.items
        } catch {
            setPageError(root)
            emitNotify({
                type: 'error',
                message: 'Unable to refresh feature flags right now.',
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
            const sortKey = button.getAttribute('sort-key') as AdminFeatureFlagsSortKey | null

            if (!sortKey) {
                return
            }

            void (async () => {
                const nextDirection: AdminFeatureFlagsSortDirection =
                    state.sort === sortKey && state.direction === 'asc' ? 'desc' : 'asc'

                state = { ...state, sort: sortKey, direction: nextDirection, page: 1 }
                await refresh()
            })()
        })
    })

    form.addEventListener('submit', (event) => {
        event.preventDefault()

        void (async () => {
            const payload = readPayload(formKey, formName, formDescription, formEnabled, formRollout)

            if (formId.value === '') {
                await service.create(endpoint, payload)
                emitNotify({ type: 'success', message: 'Feature flag created.' })
            } else {
                await service.update(endpoint, Number(formId.value), payload)
                emitNotify({ type: 'success', message: 'Feature flag updated.' })
            }

            resetForm()
            await refresh()
        })()
    })

    formCancel.addEventListener('click', () => {
        resetForm()
    })

    statusSelect.addEventListener('change', () => {
        void (async () => {
            state = { ...state, status: statusSelect.value, page: 1 }
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
            const editId = Number(target.dataset.featureFlagEdit)

            if (editId > 0) {
                await hydrateCurrentPageData()

                const flag = currentItems.find((item) => item.id === editId)

                if (!flag) {
                    emitNotify({
                        type: 'error',
                        message: 'Unable to load the selected feature flag.',
                    })

                    return
                }

                formId.value = String(flag.id)
                formKey.value = flag.key
                formName.value = flag.name
                formDescription.value = flag.description ?? ''
                formEnabled.value = flag.enabled ? '1' : '0'
                formRollout.value = String(flag.rolloutPercent)
                formSubmit.textContent = 'Update flag'
                formKey.focus()

                return
            }

            const deleteId = Number(target.dataset.featureFlagDelete)

            if (deleteId > 0) {
                await service.delete(endpoint, deleteId)
                emitNotify({ type: 'success', message: 'Feature flag deleted.' })
                await refresh()
            }
        })()
    })
}

function readPayload(
    formKey: HTMLInputElement,
    formName: HTMLInputElement,
    formDescription: HTMLTextAreaElement,
    formEnabled: HTMLSelectElement,
    formRollout: HTMLInputElement,
): AdminFeatureFlagPayload {
    return {
        key: formKey.value.trim(),
        name: formName.value.trim(),
        description: formDescription.value.trim() || null,
        enabled: formEnabled.value === '1',
        rolloutPercent: Number(formRollout.value || '0'),
    }
}

function applyFeatureFlagsResult(
    result: Awaited<ReturnType<AdminFeatureFlagsService['load']>>,
    elements: {
        root: HTMLElement
        tableBody: HTMLElement
        pagination: HTMLElement
        summary: HTMLElement
        empty: HTMLElement
        sortButtons: NodeListOf<HTMLButtonElement>
    },
    state: AdminFeatureFlagsQueryState,
): void {
    elements.tableBody.innerHTML = result.items.map(renderRow).join('')
    elements.empty.classList.toggle('is-hidden', result.items.length > 0)
    elements.summary.textContent = `${result.total} total flags`
    elements.pagination.innerHTML = renderPagination(result.page, result.totalPages)
    syncSortButtons(elements.sortButtons, state)
    syncUrl(state)
    syncRootState(elements.root, elements.tableBody, elements.empty)
}

function renderRow(flag: AdminFeatureFlagRecord): string {
    return `
        <tr data-testid="feature-flags-table-row">
            <td>#${flag.id}</td>
            <td>${escapeHtml(flag.key)}</td>
            <td>${escapeHtml(flag.name)}</td>
            <td>${flag.enabled
                ? '<span class="admin-badge admin-badge--accent">Enabled</span>'
                : '<span class="admin-badge admin-badge--muted">Disabled</span>'}</td>
            <td>${flag.rolloutPercent}%</td>
            <td>
                <div class="admin-row-actions">
                    <button class="admin-button" type="button" data-feature-flag-edit="${flag.id}">Edit</button>
                    <button class="admin-button" type="button" data-feature-flag-delete="${flag.id}">Delete</button>
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
    sort: AdminFeatureFlagsSortKey,
    direction: AdminFeatureFlagsSortDirection,
    search: string,
    status: string,
): AdminFeatureFlagsQueryState {
    const url = new URL(window.location.href)
    const page = Number(url.searchParams.get('page') ?? '1')

    return {
        page: Number.isNaN(page) ? 1 : page,
        sort,
        direction,
        search: url.searchParams.get('search') ?? search ?? '',
        status: url.searchParams.get('status') ?? status ?? 'all',
    }
}

function syncUrl(state: AdminFeatureFlagsQueryState): void {
    const url = new URL(window.location.href)

    url.searchParams.set('page', String(state.page))
    url.searchParams.set('sort', state.sort)
    url.searchParams.set('direction', state.direction)

    if (state.search !== '') {
        url.searchParams.set('search', state.search)
    } else {
        url.searchParams.delete('search')
    }

    if (state.status !== 'all') {
        url.searchParams.set('status', state.status)
    } else {
        url.searchParams.delete('status')
    }

    window.history.replaceState({}, '', url)
}

function syncSortButtons(buttons: NodeListOf<HTMLButtonElement>, state: AdminFeatureFlagsQueryState): void {
    buttons.forEach((button) => {
        const sortKey = button.getAttribute('sort-key') as AdminFeatureFlagsSortKey | null
        const isActive = sortKey === state.sort

        button.classList.toggle('is-active', isActive)
        button.classList.toggle('is-asc', isActive && state.direction === 'asc')
        button.classList.toggle('is-desc', isActive && state.direction === 'desc')
    })
}

function syncRootState(root: HTMLElement, tableBody: HTMLElement, empty: HTMLElement): void {
    if (tableBody.querySelector('[data-testid="feature-flags-table-row"]')) {
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
