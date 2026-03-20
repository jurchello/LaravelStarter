@extends('admin-panel.layouts.admin')

@php
    $hasUsers = count($users->items) > 0;
    $pageState = $hasUsers ? 'ready' : 'empty';
@endphp

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="users"
        data-page-state="{{ $pageState }}"
        aria-busy="false"
        data-users-endpoint="{{ route('admin.api.users.index', absolute: false) }}"
        data-users-suggestions-endpoint="{{ route('admin.api.users.suggestions', absolute: false) }}"
        data-users-role-update-base="{{ url('/management/api/users') }}"
        data-users-impersonate-base="{{ url('/management/api/users') }}"
        data-users-sort="{{ $query->sortBy }}"
        data-users-direction="{{ $query->direction }}"
        data-testid="admin-users-page"
    >
        <x-admin.page-header
            title="Users"
            subtitle="Session-authenticated users with a server-rendered first screen and API-powered management flows."
        />

        <x-admin.filter-section title="Filters" data-users-filter-section>
            <x-admin.table-filters class="admin-table-filters--compact admin-table-filters--single-row">
                <x-admin.search
                    id="users-search"
                    name="search"
                    placeholder="Search by name or email"
                    :value="$query->search"
                    list-id="users-search-suggestions"
                    autocomplete="off"
                >
                    <datalist id="users-search-suggestions" data-users-search-suggestions></datalist>
                </x-admin.search>

                <x-admin.select
                    id="users-role"
                    name="role"
                    aria-label="Filter by role"
                    data-users-role-filter
                >
                    @foreach ($roleFilters as $roleFilter)
                        <option value="{{ $roleFilter['value'] }}" @selected($query->role === $roleFilter['value'])>
                            {{ $roleFilter['label'] }}
                        </option>
                    @endforeach
                </x-admin.select>
            </x-admin.table-filters>
        </x-admin.filter-section>

        <x-admin.surface padded>
            <x-admin.toolbar title="All Users">
                <span data-users-summary data-testid="users-summary">{{ $users->total }} total users</span>
            </x-admin.toolbar>

            <x-admin.table data-testid="users-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort-button
                                data-users-sort-trigger
                                sort-key="id"
                                :active="$query->sortBy === 'id'"
                                :direction="$query->sortBy === 'id' ? $query->direction : null"
                            >
                                ID
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-users-sort-trigger
                                sort-key="name"
                                :active="$query->sortBy === 'name'"
                                :direction="$query->sortBy === 'name' ? $query->direction : null"
                            >
                                Name
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-users-sort-trigger
                                sort-key="email"
                                :active="$query->sortBy === 'email'"
                                :direction="$query->sortBy === 'email' ? $query->direction : null"
                            >
                                Email
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-users-sort-trigger
                                sort-key="role"
                                :active="$query->sortBy === 'role'"
                                :direction="$query->sortBy === 'role' ? $query->direction : null"
                            >
                                Access
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-users-sort-trigger
                                sort-key="registeredAt"
                                :active="$query->sortBy === 'registeredAt'"
                                :direction="$query->sortBy === 'registeredAt' ? $query->direction : null"
                            >
                                Joined
                            </x-admin.sort-button>
                        </th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody data-users-table-body>
                    @include('admin-panel.users.partials.rows', ['users' => $users->items])
                </tbody>
            </x-admin.table>

            <x-admin.empty-state
                class="{{ $hasUsers ? 'is-hidden' : '' }}"
                title="No users found"
                description="The current filter returned no records."
                data-users-empty
                data-testid="users-empty"
            />

            <x-admin.pagination data-users-pagination data-testid="users-pagination">
                @include('admin-panel.users.partials.pagination', [
                    'currentPage' => $users->currentPage,
                    'totalPages' => max(1, $totalPages),
                ])
            </x-admin.pagination>
        </x-admin.surface>

        <x-admin.modal title="Edit user access" data-users-role-modal>
            <div class="admin-panel__body">
                <form data-users-role-form>
                    <input type="hidden" value="" data-users-role-form-id>
                    <x-admin.multi-select
                        id="users-role-multi-select"
                        name="roles[]"
                        size="8"
                        data-users-role-select
                    ></x-admin.multi-select>
                    <x-admin.form-actions class="admin-form-actions--spaced">
                        <x-admin.button type="button" data-users-role-cancel>Cancel</x-admin.button>
                        <x-admin.button type="submit" variant="primary" data-users-role-submit>Save access</x-admin.button>
                    </x-admin.form-actions>
                </form>
            </div>
        </x-admin.modal>
    </x-admin.page>
@endsection
