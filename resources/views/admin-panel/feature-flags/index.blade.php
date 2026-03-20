@extends('admin-panel.layouts.admin')

@php
    $hasFlags = count($flags->items) > 0;
    $pageState = $hasFlags ? 'ready' : 'empty';
@endphp

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="feature-flags"
        data-page-state="{{ $pageState }}"
        aria-busy="false"
        data-feature-flags-endpoint="{{ route('admin.api.feature-flags.index', absolute: false) }}"
        data-feature-flags-suggestions-endpoint="{{ route('admin.api.feature-flags.suggestions', absolute: false) }}"
        data-feature-flags-sort="{{ $query->sortBy }}"
        data-feature-flags-direction="{{ $query->direction }}"
        data-testid="admin-feature-flags-page"
    >
        <x-admin.page-header
            title="Feature Flags"
            subtitle="Server-rendered operational flags with API-powered filtering and mutations."
        />

        <x-admin.filter-section title="Filters" data-feature-flags-filter-section>
            <x-admin.table-filters class="admin-table-filters--compact admin-table-filters--single-row">
                <x-admin.search
                    id="feature-flags-search"
                    name="search"
                    placeholder="Search by key or name"
                    :value="$query->search"
                    list-id="feature-flags-search-suggestions"
                    autocomplete="off"
                >
                    <datalist id="feature-flags-search-suggestions" data-feature-flags-search-suggestions></datalist>
                </x-admin.search>

                <x-admin.select
                    id="feature-flags-status"
                    name="status"
                    aria-label="Filter by status"
                    data-feature-flags-status-filter
                >
                    <option value="all" @selected($query->status === 'all')>All flags</option>
                    <option value="enabled" @selected($query->status === 'enabled')>Enabled</option>
                    <option value="disabled" @selected($query->status === 'disabled')>Disabled</option>
                </x-admin.select>
            </x-admin.table-filters>
        </x-admin.filter-section>

        <x-admin.surface padded>
            <x-admin.toolbar title="Flag Editor" subtitle="Create new flags or update the selected one." />

            <form data-feature-flags-form>
                <input type="hidden" name="flag_id" value="" data-feature-flags-form-id>
                <div class="admin-form-grid">
                    <x-admin.field>
                        <x-admin.input
                            id="feature-flag-key"
                            name="key"
                            placeholder="feature-key"
                            data-feature-flags-form-key
                            autocomplete="off"
                        />
                    </x-admin.field>

                    <x-admin.field>
                        <x-admin.input
                            id="feature-flag-name"
                            name="name"
                            placeholder="Flag name"
                            data-feature-flags-form-name
                            autocomplete="off"
                        />
                    </x-admin.field>

                    <x-admin.field class="admin-form-grid__full">
                        <x-admin.textarea
                            id="feature-flag-description"
                            name="description"
                            rows="3"
                            placeholder="What this flag controls"
                            data-feature-flags-form-description
                        />
                    </x-admin.field>

                    <x-admin.field>
                        <x-admin.select
                            id="feature-flag-enabled"
                            name="enabled"
                            data-feature-flags-form-enabled
                        >
                            <option value="0">Disabled</option>
                            <option value="1">Enabled</option>
                        </x-admin.select>
                    </x-admin.field>

                    <x-admin.field>
                        <x-admin.input
                            id="feature-flag-rollout"
                            name="rolloutPercent"
                            type="number"
                            min="0"
                            max="100"
                            step="1"
                            placeholder="0"
                            data-feature-flags-form-rollout
                        />
                    </x-admin.field>
                </div>

                <x-admin.form-actions>
                    <x-admin.button type="button" data-feature-flags-form-cancel>Cancel</x-admin.button>
                    <x-admin.button type="submit" variant="primary" data-feature-flags-form-submit>Save flag</x-admin.button>
                </x-admin.form-actions>
            </form>
        </x-admin.surface>

        <x-admin.surface padded>
            <x-admin.toolbar title="All Flags">
                <span data-feature-flags-summary data-testid="feature-flags-summary">{{ $flags->total }} total flags</span>
            </x-admin.toolbar>

            <x-admin.table data-testid="feature-flags-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort-button
                                data-feature-flags-sort-trigger
                                sort-key="id"
                                :active="$query->sortBy === 'id'"
                                :direction="$query->sortBy === 'id' ? $query->direction : null"
                            >
                                ID
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-feature-flags-sort-trigger
                                sort-key="key"
                                :active="$query->sortBy === 'key'"
                                :direction="$query->sortBy === 'key' ? $query->direction : null"
                            >
                                Key
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-feature-flags-sort-trigger
                                sort-key="name"
                                :active="$query->sortBy === 'name'"
                                :direction="$query->sortBy === 'name' ? $query->direction : null"
                            >
                                Name
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-feature-flags-sort-trigger
                                sort-key="enabled"
                                :active="$query->sortBy === 'enabled'"
                                :direction="$query->sortBy === 'enabled' ? $query->direction : null"
                            >
                                Status
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-feature-flags-sort-trigger
                                sort-key="rolloutPercent"
                                :active="$query->sortBy === 'rolloutPercent'"
                                :direction="$query->sortBy === 'rolloutPercent' ? $query->direction : null"
                            >
                                Rollout
                            </x-admin.sort-button>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody data-feature-flags-table-body>
                    @include('admin-panel.feature-flags.partials.rows', ['flags' => $flags->items])
                </tbody>
            </x-admin.table>

            <x-admin.empty-state
                class="{{ $hasFlags ? 'is-hidden' : '' }}"
                title="No feature flags found"
                description="The current filter returned no flags."
                data-feature-flags-empty
                data-testid="feature-flags-empty"
            />

            <x-admin.pagination data-feature-flags-pagination data-testid="feature-flags-pagination">
                @include('admin-panel.feature-flags.partials.pagination', [
                    'currentPage' => $flags->currentPage,
                    'totalPages' => $totalPages,
                ])
            </x-admin.pagination>
        </x-admin.surface>
    </x-admin.page>
@endsection
