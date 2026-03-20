@extends('admin-panel.layouts.admin')

@php
    $hasTests = count($tests->items) > 0;
    $pageState = $hasTests ? 'ready' : 'empty';
@endphp

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="ab-tests"
        data-page-state="{{ $pageState }}"
        aria-busy="false"
        data-ab-tests-endpoint="{{ route('admin.api.ab-tests.index', absolute: false) }}"
        data-ab-tests-suggestions-endpoint="{{ route('admin.api.ab-tests.suggestions', absolute: false) }}"
        data-ab-tests-base-route="{{ route('admin.ab-tests.index', absolute: false) }}"
        data-ab-tests-sort="{{ $query->sortBy }}"
        data-ab-tests-direction="{{ $query->direction }}"
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
                    :value="$query->search"
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
                    <option value="all" @selected($query->status === 'all')>All statuses</option>
                    <option value="draft" @selected($query->status === 'draft')>Draft</option>
                    <option value="active" @selected($query->status === 'active')>Active</option>
                    <option value="paused" @selected($query->status === 'paused')>Paused</option>
                    <option value="finished" @selected($query->status === 'finished')>Finished</option>
                </x-admin.select>
            </x-admin.table-filters>
        </x-admin.filter-section>

        <x-admin.surface padded>
            <x-admin.toolbar title="All Tests">
                <span data-ab-tests-summary data-testid="ab-tests-summary">{{ $tests->total }} total tests</span>
            </x-admin.toolbar>

            <x-admin.table data-testid="ab-tests-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="name"
                                :active="$query->sortBy === 'name'"
                                :direction="$query->sortBy === 'name' ? $query->direction : null"
                            >
                                Name
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="slug"
                                :active="$query->sortBy === 'slug'"
                                :direction="$query->sortBy === 'slug' ? $query->direction : null"
                            >
                                Slug
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="status"
                                :active="$query->sortBy === 'status'"
                                :direction="$query->sortBy === 'status' ? $query->direction : null"
                            >
                                Status
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="trafficPercent"
                                :active="$query->sortBy === 'trafficPercent'"
                                :direction="$query->sortBy === 'trafficPercent' ? $query->direction : null"
                            >
                                Traffic
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-ab-tests-sort-trigger
                                sort-key="variantsCount"
                                :active="$query->sortBy === 'variantsCount'"
                                :direction="$query->sortBy === 'variantsCount' ? $query->direction : null"
                            >
                                Variants
                            </x-admin.sort-button>
                        </th>
                        <th></th>
                    </tr>
                </thead>
                <tbody data-ab-tests-table-body>
                    @include('admin-panel.ab-tests.partials.rows', ['tests' => $tests->items])
                </tbody>
            </x-admin.table>

            <x-admin.empty-state
                class="{{ $hasTests ? 'is-hidden' : '' }}"
                title="No AB tests found"
                description="The current filters returned no experiments."
                data-ab-tests-empty
                data-testid="ab-tests-empty"
            />

            <x-admin.pagination data-ab-tests-pagination data-testid="ab-tests-pagination">
                @include('admin-panel.ab-tests.partials.pagination', [
                    'currentPage' => $tests->currentPage,
                    'totalPages' => $totalPages,
                ])
            </x-admin.pagination>
        </x-admin.surface>
    </x-admin.page>
@endsection
