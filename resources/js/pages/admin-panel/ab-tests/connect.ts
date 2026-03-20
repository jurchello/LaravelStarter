import {
    type AdminAbTestManagementRecord,
    AdminAbTestsService,
    type AdminAbTestRecord,
    type AdminAbTestsQueryState,
    type AdminAbTestsSortDirection,
    type AdminAbTestsSortKey,
    type AdminAbTestsStatusFilter,
} from '@/modules/admin-ab-tests/service'

const service = new AdminAbTestsService()
const SEARCH_DEBOUNCE_MS = 250

export async function initAdminAbTestsPage(root: HTMLElement): Promise<void> {
    const endpoint = root.dataset.abTestsEndpoint
    const suggestionsEndpoint = root.dataset.abTestsSuggestionsEndpoint
    const manageBase = root.dataset.abTestsBaseRoute ?? '/management/ab-tests'
    const currentSort = (root.dataset.abTestsSort as AdminAbTestsSortKey | undefined) ?? 'name'
    const currentDirection = (root.dataset.abTestsDirection as AdminAbTestsSortDirection | undefined) ?? 'asc'
    const tableBody = root.querySelector<HTMLElement>('[data-ab-tests-table-body]')
    const pagination = root.querySelector<HTMLElement>('[data-ab-tests-pagination]')
    const summary = root.querySelector<HTMLElement>('[data-ab-tests-summary]')
    const empty = root.querySelector<HTMLElement>('[data-ab-tests-empty]')
    const sortButtons = root.querySelectorAll<HTMLButtonElement>('[data-ab-tests-sort-trigger]')
    const searchInput = root.querySelector<HTMLInputElement>('#ab-tests-search')
    const statusSelect = root.querySelector<HTMLSelectElement>('[data-ab-tests-status-filter]')
    const suggestionsList = root.querySelector<HTMLDataListElement>('[data-ab-tests-search-suggestions]')

    if (!endpoint || !suggestionsEndpoint || !tableBody || !pagination || !summary || !empty || !searchInput || !statusSelect || !suggestionsList) {
        return
    }

    let state = getInitialState(currentSort, currentDirection, searchInput.value, statusSelect.value)
    let searchTimer: number | null = null
    let suggestionRequestId = 0

    const render = async (): Promise<void> => {
        const result = await service.load(endpoint, state)

        tableBody.innerHTML = result.items.map((test) => renderRow(test, manageBase)).join('')
        empty.classList.toggle('is-hidden', result.items.length > 0)
        summary.textContent = `${result.total} total tests`
        pagination.innerHTML = renderPagination(result.page, result.totalPages)
        syncSortButtons(sortButtons, state)
        syncUrl(state)
        bindPagination(pagination, () => state, async (page) => {
            state = { ...state, page }
            await render()
        })
    }

    await render()

    bindSorting(sortButtons, () => state, async (sort, direction) => {
        state = { ...state, sort, direction, page: 1 }
        await render()
    })

    statusSelect.addEventListener('change', async () => {
        state = {
            ...state,
            status: statusSelect.value as AdminAbTestsStatusFilter,
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

        searchTimer = window.setTimeout(async () => {
            state = {
                ...state,
                search: query,
                page: 1,
            }
            await render()
        }, SEARCH_DEBOUNCE_MS)
    })
}

export async function initAdminAbTestCreatePage(root: HTMLElement): Promise<void> {
    const endpoint = root.dataset.abTestCreateEndpoint
    const audienceEstimateEndpoint = root.dataset.abTestAudienceEstimateEndpoint
    const baseRoute = root.dataset.abTestsBaseRoute ?? '/management/ab-tests'
    const form = root.querySelector<HTMLFormElement>('[data-ab-test-create-form]')
    const nameInput = root.querySelector<HTMLInputElement>('[data-ab-test-input="name"]')
    const slugInput = root.querySelector<HTMLInputElement>('[data-ab-test-input="slug"]')
    const trafficInput = root.querySelector<HTMLInputElement>('[data-ab-test-input="traffic"]')
    const splitEvenlyInput = root.querySelector<HTMLInputElement>('[data-ab-test-input="split-evenly"]')
    const trafficEstimate = root.querySelector<HTMLElement>('[data-ab-test-traffic-estimate]')

    if (!endpoint || !audienceEstimateEndpoint || !form || !nameInput || !slugInput || !trafficInput || !splitEvenlyInput || !trafficEstimate) {
        return
    }

    bindSlugSync(nameInput, slugInput)
    bindTrafficEstimate(trafficInput, trafficEstimate, audienceEstimateEndpoint)
    void updateTrafficEstimate(trafficInput, trafficEstimate, audienceEstimateEndpoint)

    form.addEventListener('submit', async (event) => {
        event.preventDefault()

        try {
            const test = await service.create(endpoint, {
                name: nameInput.value.trim(),
                slug: slugInput.value.trim(),
                trafficPercent: Number(trafficInput.value || '0'),
                distributionMode: splitEvenlyInput.checked ? 'equal' : 'manual',
            })

            dispatchToast('success', 'AB test created.')
            window.location.assign(`${baseRoute}/${test.id}`)
        } catch (error) {
            dispatchHttpError(error, 'Unable to create the AB test.')
        }
    })
}

export async function initAdminAbTestManagementPage(root: HTMLElement): Promise<void> {
    const detailEndpoint = root.dataset.abTestEndpoint
    const audienceEstimateEndpoint = root.dataset.abTestAudienceEstimateEndpoint
    const updateEndpoint = root.dataset.abTestUpdateEndpoint
    const deleteEndpoint = root.dataset.abTestDeleteEndpoint
    const statusEndpoint = root.dataset.abTestStatusEndpoint
    const variantsEndpoint = root.dataset.abTestVariantsEndpoint
    const baseRoute = root.dataset.abTestsBaseRoute ?? '/management/ab-tests'
    const configForm = root.querySelector<HTMLFormElement>('[data-ab-test-config-form]')
    const variantForm = root.querySelector<HTMLFormElement>('[data-ab-test-variant-form]')
    const nameInput = root.querySelector<HTMLInputElement>('[data-ab-test-input="name"]')
    const slugInput = root.querySelector<HTMLInputElement>('[data-ab-test-input="slug"]')
    const trafficInput = root.querySelector<HTMLInputElement>('[data-ab-test-input="traffic"]')
    const splitEvenlyInput = root.querySelector<HTMLInputElement>('[data-ab-test-input="split-evenly"]')
    const trafficEstimate = root.querySelector<HTMLElement>('[data-ab-test-traffic-estimate]')
    const variantNameInput = root.querySelector<HTMLInputElement>('[data-ab-test-variant-input="name"]')
    const variantSlugInput = root.querySelector<HTMLInputElement>('[data-ab-test-variant-input="slug"]')
    const variantWeightInput = root.querySelector<HTMLInputElement>('[data-ab-test-variant-input="weight"]')
    const variantsBody = root.querySelector<HTMLElement>('[data-ab-test-variants-body]')
    const statusPill = root.querySelector<HTMLElement>('[data-ab-test-status-pill]')
    const headerStatus = root.querySelector<HTMLElement>('[data-ab-test-header-status]')
    const eventsSummary = root.querySelector<HTMLElement>('[data-ab-test-events-summary]')
    const stats = root.querySelectorAll<HTMLElement>('[data-ab-test-stat]')
    const statusButtons = root.querySelectorAll<HTMLButtonElement>('[data-ab-test-status-action]')
    const deleteTrigger = root.querySelector<HTMLButtonElement>('[data-ab-test-delete-trigger]')
    const variantSubmit = root.querySelector<HTMLButtonElement>('[data-ab-test-variant-submit]')
    const variantReset = root.querySelector<HTMLButtonElement>('[data-ab-test-variant-reset]')

    if (
        !detailEndpoint ||
        !updateEndpoint ||
        !deleteEndpoint ||
        !statusEndpoint ||
        !variantsEndpoint ||
        !configForm ||
        !variantForm ||
        !nameInput ||
        !slugInput ||
        !trafficInput ||
        !splitEvenlyInput ||
        !audienceEstimateEndpoint ||
        !trafficEstimate ||
        !variantNameInput ||
        !variantSlugInput ||
        !variantWeightInput ||
        !variantsBody ||
        !statusPill ||
        !headerStatus ||
        !eventsSummary ||
        stats.length === 0 ||
        !deleteTrigger ||
        !variantSubmit ||
        !variantReset
    ) {
        return
    }

    bindSlugSync(variantNameInput, variantSlugInput)

    let current: AdminAbTestManagementRecord | null = null

    const syncVariantWeightState = (): void => {
        const isEqualMode = splitEvenlyInput.checked

        variantWeightInput.disabled = isEqualMode

        if (isEqualMode) {
            variantWeightInput.value = '100'
        }
    }

    const render = (test: AdminAbTestManagementRecord): void => {
        current = test
        nameInput.value = test.name
        slugInput.value = test.slug
        trafficInput.value = String(test.trafficPercent)
        splitEvenlyInput.checked = test.distributionMode === 'equal'
        void updateTrafficEstimate(trafficInput, trafficEstimate, audienceEstimateEndpoint)
        statusPill.className = `admin-status-pill admin-status-pill--${statusVariant(test.status)}`
        statusPill.textContent = test.status
        headerStatus.textContent = `${test.name} · ${test.slug} · ${test.trafficPercent}% traffic · ${test.distributionMode === 'equal' ? 'equal split' : 'manual weights'}`
        variantsBody.innerHTML = test.variants.map(renderVariantRow).join('') || renderEmptyRow('No variants yet.', 5)
        eventsSummary.innerHTML = renderEventsSummary(test.analytics.eventsByName)
        syncStats(stats, test)
        syncStatusButtons(statusButtons, test.status)
        syncVariantWeightState()
        bindVariantActions(root, variantsBody, variantsEndpoint, async (view) => {
            render(view)
        })
    }

    const load = async (): Promise<void> => {
        try {
            render(await service.loadDetail(detailEndpoint))
        } catch (error) {
            dispatchHttpError(error, 'Unable to load the AB test.')
        }
    }

    await load()

    splitEvenlyInput.addEventListener('change', () => {
        syncVariantWeightState()
    })
    bindTrafficEstimate(trafficInput, trafficEstimate, audienceEstimateEndpoint)

    configForm.addEventListener('submit', async (event) => {
        event.preventDefault()

        try {
            render(await service.update(updateEndpoint, {
                name: nameInput.value.trim(),
                slug: slugInput.value.trim(),
                trafficPercent: Number(trafficInput.value || '0'),
                distributionMode: splitEvenlyInput.checked ? 'equal' : 'manual',
            }))
            dispatchToast('success', 'AB test updated.')
        } catch (error) {
            dispatchHttpError(error, 'Unable to update the AB test.')
        }
    })

    variantForm.addEventListener('submit', async (event) => {
        event.preventDefault()

        const payload = {
            name: variantNameInput.value.trim(),
            slug: variantSlugInput.value.trim(),
            weight: splitEvenlyInput.checked ? 100 : Number(variantWeightInput.value || '0'),
        }
        const editingVariantId = variantForm.dataset.abTestVariantEditing

        try {
            const view = editingVariantId
                ? await service.updateVariant(`${variantsEndpoint}/${editingVariantId}`, payload)
                : await service.createVariant(variantsEndpoint, payload)

            render(view)
            resetVariantForm(variantForm, variantNameInput, variantSlugInput, variantWeightInput, variantSubmit, variantReset)
            dispatchToast('success', editingVariantId ? 'Variant updated.' : 'Variant created.')
        } catch (error) {
            dispatchHttpError(error, editingVariantId ? 'Unable to update the variant.' : 'Unable to create the variant.')
        }
    })

    variantReset.addEventListener('click', () => {
        resetVariantForm(variantForm, variantNameInput, variantSlugInput, variantWeightInput, variantSubmit, variantReset)
    })

    statusButtons.forEach((button) => {
        button.addEventListener('click', async () => {
            const nextStatus = button.dataset.abTestStatusAction as AdminAbTestRecord['status'] | undefined

            if (!nextStatus) {
                return
            }

            try {
                render(await service.updateStatus(statusEndpoint, nextStatus))
                dispatchToast('success', `Status updated to ${nextStatus}.`)
            } catch (error) {
                dispatchHttpError(error, 'Unable to update the AB test status.')
            }
        })
    })

    deleteTrigger.addEventListener('click', async () => {
        const shouldDelete = window.confirm('Delete this AB test?')

        if (!shouldDelete) {
            return
        }

        try {
            await service.remove(deleteEndpoint)
            dispatchToast('success', 'AB test deleted.')
            window.location.assign(baseRoute)
        } catch (error) {
            dispatchHttpError(error, 'Unable to delete the AB test.')
        }
    })
}

export async function initAdminAbTestAssignmentsPage(root: HTMLElement): Promise<void> {
    const endpoint = root.dataset.abTestAssignmentsEndpoint
    const body = root.querySelector<HTMLElement>('[data-ab-test-assignments-body]')
    const summary = root.querySelector<HTMLElement>('[data-ab-test-assignments-summary]')
    const empty = root.querySelector<HTMLElement>('[data-ab-test-assignments-empty]')
    const pagination = root.querySelector<HTMLElement>('[data-ab-test-assignments-pagination]')

    if (!endpoint || !body || !summary || !empty || !pagination) {
        return
    }

    let page = Number(new URL(window.location.href).searchParams.get('page') ?? '1')

    const render = async (): Promise<void> => {
        const result = await service.loadAssignments(endpoint, page)

        body.innerHTML = result.items.map(renderAssignmentRow).join('')
        empty.classList.toggle('is-hidden', result.items.length > 0)
        summary.textContent = `${result.total} total assignments`
        pagination.innerHTML = renderPagination(result.page, result.totalPages)
        bindSimplePagination(pagination, result.page, async (nextPage) => {
            page = nextPage
            syncSimplePageUrl(page)
            await render()
        })
    }

    await render()
}

export async function initAdminAbTestEventsPage(root: HTMLElement): Promise<void> {
    const endpoint = root.dataset.abTestEventsEndpoint
    const body = root.querySelector<HTMLElement>('[data-ab-test-events-body]')
    const summary = root.querySelector<HTMLElement>('[data-ab-test-events-summary]')
    const empty = root.querySelector<HTMLElement>('[data-ab-test-events-empty]')
    const pagination = root.querySelector<HTMLElement>('[data-ab-test-events-pagination]')

    if (!endpoint || !body || !summary || !empty || !pagination) {
        return
    }

    let page = Number(new URL(window.location.href).searchParams.get('page') ?? '1')

    const render = async (): Promise<void> => {
        const result = await service.loadEvents(endpoint, page)

        body.innerHTML = result.items.map(renderEventRow).join('')
        empty.classList.toggle('is-hidden', result.items.length > 0)
        summary.textContent = `${result.total} total events`
        pagination.innerHTML = renderPagination(result.page, result.totalPages)
        bindSimplePagination(pagination, result.page, async (nextPage) => {
            page = nextPage
            syncSimplePageUrl(page)
            await render()
        })
    }

    await render()
}

export async function initAdminAbTestAnalyticsPage(root: HTMLElement): Promise<void> {
    const endpoint = root.dataset.abTestAnalyticsEndpoint
    const status = root.querySelector<HTMLElement>('[data-ab-test-analytics-status]')
    const traffic = root.querySelector<HTMLElement>('[data-ab-test-analytics-traffic]')
    const events = root.querySelector<HTMLElement>('[data-ab-test-analytics-events]')
    const variants = root.querySelector<HTMLElement>('[data-ab-test-analytics-variants]')
    const stats = root.querySelectorAll<HTMLElement>('[data-ab-test-analytics-stat]')

    if (!endpoint || !status || !traffic || !events || !variants || stats.length === 0) {
        return
    }

    try {
        const test = await service.loadDetail(endpoint)

        status.textContent = test.status
        traffic.textContent = `${test.trafficPercent}%`
        events.innerHTML = renderEventsSummary(test.analytics.eventsByName)
        variants.innerHTML = test.variants.map((variant) => `
            <tr>
                <td>${escapeHtml(variant.name)}</td>
                <td>${escapeHtml(variant.slug)}</td>
                <td>${variant.weight}</td>
                <td>${variant.assignmentsCount}</td>
            </tr>
        `).join('') || renderEmptyRow('No variants yet.', 4)
        syncAnalyticsStats(stats, test)
    } catch (error) {
        dispatchHttpError(error, 'Unable to load AB test analytics.')
    }
}

function renderRow(test: AdminAbTestRecord, manageBase: string): string {
    return `
        <tr data-testid="ab-tests-table-row">
            <td>${escapeHtml(test.name)}</td>
            <td>${escapeHtml(test.slug)}</td>
            <td><span class="admin-status-pill admin-status-pill--${statusVariant(test.status)}">${escapeHtml(test.status)}</span></td>
            <td>${test.trafficPercent}%</td>
            <td>${test.variantsCount}</td>
            <td><a class="admin-button" href="${manageBase}/${test.id}">Manage</a></td>
        </tr>
    `
}

function statusVariant(status: AdminAbTestRecord['status']): 'success' | 'warning' | 'danger' | 'muted' {
    switch (status) {
        case 'active':
            return 'success'
        case 'paused':
            return 'warning'
        case 'finished':
            return 'danger'
        default:
            return 'muted'
    }
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
    getState: () => AdminAbTestsQueryState,
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

function bindSimplePagination(root: HTMLElement, currentPage: number, onNavigate: (page: number) => Promise<void>): void {
    root.querySelectorAll<HTMLButtonElement>('[data-page-nav]').forEach((button) => {
        button.addEventListener('click', async () => {
            const nextPage = button.dataset.pageNav === 'next' ? currentPage + 1 : Math.max(1, currentPage - 1)
            await onNavigate(nextPage)
        })
    })
}

function bindSorting(
    buttons: NodeListOf<HTMLButtonElement>,
    getState: () => AdminAbTestsQueryState,
    onSort: (sort: AdminAbTestsSortKey, direction: AdminAbTestsSortDirection) => Promise<void>,
): void {
    buttons.forEach((button) => {
        button.addEventListener('click', async () => {
            const sortKey = button.getAttribute('sort-key') as AdminAbTestsSortKey | null

            if (!sortKey) {
                return
            }

            const currentState = getState()
            const nextDirection: AdminAbTestsSortDirection = currentState.sort === sortKey && currentState.direction === 'asc' ? 'desc' : 'asc'

            await onSort(sortKey, nextDirection)
        })
    })
}

function getInitialState(
    sort: AdminAbTestsSortKey,
    direction: AdminAbTestsSortDirection,
    search: string,
    status: string,
): AdminAbTestsQueryState {
    const url = new URL(window.location.href)
    const page = Number(url.searchParams.get('page') ?? '1')

    return {
        page: Number.isNaN(page) ? 1 : page,
        sort,
        direction,
        search: url.searchParams.get('search') ?? search ?? '',
        status: (url.searchParams.get('status') as AdminAbTestsStatusFilter | null) ?? ((status as AdminAbTestsStatusFilter) || 'all'),
    }
}

function syncSortButtons(buttons: NodeListOf<HTMLButtonElement>, state: AdminAbTestsQueryState): void {
    buttons.forEach((button) => {
        const sortKey = button.getAttribute('sort-key') as AdminAbTestsSortKey | null
        const isActive = sortKey === state.sort

        button.classList.toggle('is-active', isActive)
        button.classList.toggle('is-asc', isActive && state.direction === 'asc')
        button.classList.toggle('is-desc', isActive && state.direction === 'desc')
    })
}

function syncUrl(state: AdminAbTestsQueryState): void {
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

function syncSimplePageUrl(page: number): void {
    const url = new URL(window.location.href)

    url.searchParams.set('page', String(page))
    window.history.replaceState({}, '', url)
}

function escapeHtml(value: string): string {
    return value
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;')
}

function renderVariantRow(variant: AdminAbTestManagementRecord['variants'][number]): string {
    return `
        <tr>
            <td>${escapeHtml(variant.name)}</td>
            <td>${escapeHtml(variant.slug)}</td>
            <td>${variant.weight}</td>
            <td>${variant.assignmentsCount}</td>
            <td>
                <div class="admin-data-grid__actions">
                    <button class="admin-button" type="button" data-ab-test-variant-edit="${variant.id}">Use values</button>
                    <button class="admin-button" type="button" data-ab-test-variant-delete="${variant.id}">Delete</button>
                </div>
            </td>
        </tr>
    `
}

function renderAssignmentRow(assignment: AdminAbTestManagementRecord['recentAssignments'][number]): string {
    return `
        <tr>
            <td>${escapeHtml(assignment.visitorId)}</td>
            <td>${assignment.userId === null ? 'Guest' : `#${assignment.userId}`}</td>
            <td>${escapeHtml(assignment.variantName)}</td>
            <td>${formatDateTime(assignment.createdAt)}</td>
        </tr>
    `
}

function renderEventRow(event: AdminAbTestManagementRecord['recentEvents'][number]): string {
    return `
        <tr>
            <td>${escapeHtml(event.event)}</td>
            <td>${escapeHtml(event.variantName)}</td>
            <td>${escapeHtml(event.visitorId)}</td>
            <td>${formatDateTime(event.createdAt)}</td>
        </tr>
    `
}

function renderEventsSummary(eventsByName: Record<string, number>): string {
    const entries = Object.entries(eventsByName)

    if (entries.length === 0) {
        return '<p class="admin-toolbar-meta">No events tracked yet.</p>'
    }

    return entries
        .map(([name, count]) => `<div class="admin-data-item"><span class="admin-data-item__label">${escapeHtml(name)}</span><span class="admin-data-item__value">${count}</span></div>`)
        .join('')
}

function renderEmptyRow(label: string, colspan: number): string {
    return `<tr><td colspan="${colspan}" class="admin-toolbar-meta">${escapeHtml(label)}</td></tr>`
}

function syncStats(nodes: NodeListOf<HTMLElement>, test: AdminAbTestManagementRecord): void {
    const totalEvents = Object.values(test.analytics.eventsByName).reduce((sum, count) => sum + count, 0)

    nodes.forEach((node) => {
        switch (node.dataset.abTestStat) {
            case 'assignments':
                node.querySelector('.admin-stat-card__value')!.textContent = String(test.analytics.assignmentsCount)
                break
            case 'identified':
                node.querySelector('.admin-stat-card__value')!.textContent = String(test.analytics.identifiedAssignmentsCount)
                break
            case 'variants':
                node.querySelector('.admin-stat-card__value')!.textContent = String(test.variants.length)
                break
            case 'events':
                node.querySelector('.admin-stat-card__value')!.textContent = String(totalEvents)
                break
            default:
                break
        }
    })
}

function syncStatusButtons(buttons: NodeListOf<HTMLButtonElement>, status: AdminAbTestManagementRecord['status']): void {
    const allowed = allowedStatusTransitions(status)
    const primary = primaryStatusAction(status)

    buttons.forEach((button) => {
        const action = button.dataset.abTestStatusAction as AdminAbTestRecord['status'] | undefined
        const isAllowed = action !== undefined && allowed.includes(action)
        const isPrimary = action !== undefined && action === primary

        button.disabled = !isAllowed
        button.classList.toggle('admin-button--primary', isPrimary && isAllowed)
    })
}

function bindTrafficEstimate(
    trafficInput: HTMLInputElement,
    output: HTMLElement,
    endpoint: string,
): void {
    let timer: number | null = null

    trafficInput.addEventListener('input', () => {
        if (timer) {
            window.clearTimeout(timer)
        }

        timer = window.setTimeout(() => {
            void updateTrafficEstimate(trafficInput, output, endpoint)
        }, SEARCH_DEBOUNCE_MS)
    })
}

async function updateTrafficEstimate(
    trafficInput: HTMLInputElement,
    output: HTMLElement,
    endpoint: string,
): Promise<void> {
    const trafficPercent = clampTrafficPercent(Number(trafficInput.value || '0'))

    try {
        const estimate = await service.loadAudienceEstimate(endpoint, trafficPercent)
        output.textContent = `Estimated ${estimate.estimatedPeople} of ${estimate.audienceSize} registered users at ${estimate.trafficPercent}% traffic.`
    } catch {
        output.textContent = 'Unable to estimate audience right now.'
    }
}

function clampTrafficPercent(value: number): number {
    if (Number.isNaN(value)) {
        return 0
    }

    return Math.min(100, Math.max(0, Math.trunc(value)))
}

function allowedStatusTransitions(status: AdminAbTestRecord['status']): AdminAbTestRecord['status'][] {
    switch (status) {
        case 'draft':
            return ['active']
        case 'active':
            return ['paused', 'finished']
        case 'paused':
            return ['active', 'finished']
        default:
            return []
    }
}

function primaryStatusAction(status: AdminAbTestRecord['status']): AdminAbTestRecord['status'] | null {
    switch (status) {
        case 'draft':
            return 'active'
        case 'active':
            return 'paused'
        case 'paused':
            return 'active'
        default:
            return null
    }
}

function syncAnalyticsStats(nodes: NodeListOf<HTMLElement>, test: AdminAbTestManagementRecord): void {
    syncStats(nodes, test)
}

function bindVariantActions(
    pageRoot: HTMLElement,
    root: HTMLElement,
    variantsEndpoint: string,
    onUpdated: (view: AdminAbTestManagementRecord) => Promise<void>,
): void {
    const form = pageRoot.querySelector<HTMLFormElement>('[data-ab-test-variant-form]')
    const nameInput = pageRoot.querySelector<HTMLInputElement>('[data-ab-test-variant-input="name"]')
    const slugInput = pageRoot.querySelector<HTMLInputElement>('[data-ab-test-variant-input="slug"]')
    const weightInput = pageRoot.querySelector<HTMLInputElement>('[data-ab-test-variant-input="weight"]')
    const submit = pageRoot.querySelector<HTMLButtonElement>('[data-ab-test-variant-submit]')
    const reset = pageRoot.querySelector<HTMLButtonElement>('[data-ab-test-variant-reset]')

    if (!form || !nameInput || !slugInput || !weightInput || !submit || !reset) {
        return
    }

    root.querySelectorAll<HTMLButtonElement>('[data-ab-test-variant-edit]').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr')

            if (!row) {
                return
            }

            const [nameCell, slugCell, weightCell] = Array.from(row.querySelectorAll('td'))

            nameInput.value = nameCell?.textContent?.trim() ?? ''
            slugInput.value = slugCell?.textContent?.trim() ?? ''
            weightInput.value = weightCell?.textContent?.trim() ?? '100'
            form.dataset.abTestVariantEditing = button.dataset.abTestVariantEdit ?? ''
            form.dataset.abTestVariantMode = 'update'
            submit.textContent = 'Update variant'
            reset.hidden = false
            nameInput.focus()
        })
    })

    root.querySelectorAll<HTMLButtonElement>('[data-ab-test-variant-delete]').forEach((button) => {
        button.addEventListener('click', async () => {
            const variantId = button.dataset.abTestVariantDelete

            if (!variantId) {
                return
            }

            try {
                await onUpdated(await service.removeVariant(`${variantsEndpoint}/${variantId}`))
                dispatchToast('success', 'Variant deleted.')
            } catch (error) {
                dispatchHttpError(error, 'Unable to delete the variant.')
            }
        })
    })
}

function bindSlugSync(source: HTMLInputElement, slugInput: HTMLInputElement): void {
    let slugTouched = false

    slugInput.addEventListener('input', () => {
        slugTouched = true
    })

    source.addEventListener('input', () => {
        if (slugTouched) {
            return
        }

        slugInput.value = slugify(source.value)
    })
}

function resetVariantForm(
    form: HTMLFormElement,
    nameInput: HTMLInputElement,
    slugInput: HTMLInputElement,
    weightInput: HTMLInputElement,
    submit: HTMLButtonElement,
    reset: HTMLButtonElement,
): void {
    form.reset()
    delete form.dataset.abTestVariantEditing
    form.dataset.abTestVariantMode = 'create'
    nameInput.value = ''
    slugInput.value = ''
    weightInput.value = '100'
    submit.textContent = 'Add variant'
    reset.hidden = true
}

function slugify(value: string): string {
    const transliterated = value
        .trim()
        .toLowerCase()
        .split('')
        .map((character) => CYRILLIC_TO_LATIN[character] ?? character)
        .join('')

    return transliterated
        .trim()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '')
}

const CYRILLIC_TO_LATIN: Record<string, string> = {
    а: 'a',
    б: 'b',
    в: 'v',
    г: 'h',
    ґ: 'g',
    д: 'd',
    е: 'e',
    є: 'ye',
    ж: 'zh',
    з: 'z',
    и: 'y',
    і: 'i',
    ї: 'yi',
    й: 'y',
    к: 'k',
    л: 'l',
    м: 'm',
    н: 'n',
    о: 'o',
    п: 'p',
    р: 'r',
    с: 's',
    т: 't',
    у: 'u',
    ф: 'f',
    х: 'kh',
    ц: 'ts',
    ч: 'ch',
    ш: 'sh',
    щ: 'shch',
    ь: '',
    ю: 'yu',
    я: 'ya',
    ё: 'yo',
    э: 'e',
    ы: 'y',
    ъ: '',
}

function formatDateTime(value: string): string {
    if (value === '') {
        return '—'
    }

    return new Date(value).toLocaleString('en-CA')
}

function dispatchToast(type: 'success' | 'danger', message: string): void {
    window.dispatchEvent(new CustomEvent('app:toast:notify', {
        detail: { type, message },
    }))
}

function dispatchHttpError(error: unknown, fallback: string): void {
    const message = extractErrorMessage(error) ?? fallback

    dispatchToast('danger', message)
}

function extractErrorMessage(error: unknown): string | null {
    const payload = error as {
        response?: {
            data?: {
                message?: string
                errors?: string[] | Record<string, string[]>
            }
        }
    }

    if (typeof payload.response?.data?.message === 'string' && payload.response.data.message !== '') {
        return payload.response.data.message
    }

    if (Array.isArray(payload.response?.data?.errors)) {
        return payload.response.data.errors[0] ?? null
    }

    const errorGroups = payload.response?.data?.errors

    if (errorGroups && typeof errorGroups === 'object') {
        const firstGroup = Object.values(errorGroups)[0]

        if (Array.isArray(firstGroup)) {
            return firstGroup[0] ?? null
        }
    }

    return null
}
