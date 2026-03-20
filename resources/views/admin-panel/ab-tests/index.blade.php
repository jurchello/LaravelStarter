@extends('admin-panel.layouts.admin')

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="ab-tests"
        data-ab-tests-endpoint="{{ route('admin.api.ab-tests.index', absolute: false) }}"
        data-ab-tests-suggestions-endpoint="{{ route('admin.api.ab-tests.suggestions', absolute: false) }}"
        data-ab-tests-base-route="{{ route('admin.ab-tests.index', absolute: false) }}"
        data-ab-tests-sort="{{ request('sort', 'name') }}"
        data-ab-tests-direction="{{ request('direction', 'asc') }}"
        data-testid="admin-ab-tests-page"
    >
        <x-admin.page-header
            title="AB Tests"
            subtitle="Manage experiments, statuses, traffic distribution, and variants."
        >
            <x-admin.button href="{{ route('admin.ab-tests.create') }}" variant="primary">Create test</x-admin.button>
        </x-admin.page-header>

        <x-admin.filter-section title="Filters" data-ab-tests-filter-section>
            <x-admin.table-filters class="admin-table-filters--compact admin-table-filters--single-row">
                <x-admin.search
                    id="ab-tests-search"
                    name="search"
                    placeholder="Search by name or slug"
                    :value="request('search')"
                    list-id="ab-tests-search-suggestions"
                    autocomplete="off"
                >
                    <datalist id="ab-tests-search-suggestions" data-ab-tests-search-suggestions></datalist>
                </x-admin.search>

                <x-admin.select
                    id="ab-tests-status"
                    name="status"
                    aria-label="Filter by status"
                    data-ab-tests-status-filter
                >
                    <option value="all" @selected(request('status', 'all') === 'all')>All statuses</option>
                    <option value="draft" @selected(request('status') === 'draft')>Draft</option>
                    <option value="active" @selected(request('status') === 'active')>Active</option>
                    <option value="paused" @selected(request('status') === 'paused')>Paused</option>
                    <option value="finished" @selected(request('status') === 'finished')>Finished</option>
                </x-admin.select>
            </x-admin.table-filters>
        </x-admin.filter-section>

        <x-admin.surface padded>
            <x-admin.toolbar title="All Tests">
                <span data-ab-tests-summary data-testid="ab-tests-summary"></span>
            </x-admin.toolbar>

            <x-admin.table data-testid="ab-tests-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="name"
                                :active="request('sort', 'name') === 'name'"
                                :direction="request('sort', 'name') === 'name' ? request('direction', 'asc') : null"
                            >
                                Name
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="slug"
                                :active="request('sort') === 'slug'"
                                :direction="request('sort') === 'slug' ? request('direction') : null"
                            >
                                Slug
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="status"
                                :active="request('sort') === 'status'"
                                :direction="request('sort') === 'status' ? request('direction') : null"
                            >
                                Status
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="trafficPercent"
                                :active="request('sort') === 'trafficPercent'"
                                :direction="request('sort') === 'trafficPercent' ? request('direction') : null"
                            >
                                Traffic
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="variantsCount"
                                :active="request('sort') === 'variantsCount'"
                                :direction="request('sort') === 'variantsCount' ? request('direction') : null"
                            >
                                Variants
                            </x-admin.sort-button>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody data-ab-tests-table-body></tbody>
            </x-admin.table>

            <x-admin.empty-state
                class="is-hidden"
                title="No AB tests found"
                description="The current filters returned no experiments."
                data-ab-tests-empty
                data-testid="ab-tests-empty"
            />

            <x-admin.pagination data-ab-tests-pagination data-testid="ab-tests-pagination" />
        </x-admin.surface>
    </x-admin.page>
@endsection
