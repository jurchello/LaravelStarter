@extends('admin-panel.layouts.admin')

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="users"
        data-users-endpoint="{{ route('admin.api.users.index', absolute: false) }}"
        data-users-suggestions-endpoint="{{ route('admin.api.users.suggestions', absolute: false) }}"
        data-users-role-update-base="{{ url('/management/api/users') }}"
        data-users-impersonate-base="{{ url('/management/api/users') }}"
        data-users-sort="{{ request('sort', 'registeredAt') }}"
        data-users-direction="{{ request('direction', 'desc') }}"
        data-testid="admin-users-page"
    >
        <x-admin.page-header
            title="Users"
            subtitle="Session-authenticated users loaded from the admin API."
        />

        <x-admin.filter-section title="Filters" data-users-filter-section>
            <x-admin.table-filters class="admin-table-filters--compact admin-table-filters--single-row">
                <x-admin.search
                    id="users-search"
                    name="search"
                    placeholder="Search by name or email"
                    :value="request('search')"
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
                    <option value="all" @selected(request('role', 'all') === 'all')>All roles</option>
                </x-admin.select>
            </x-admin.table-filters>
        </x-admin.filter-section>

        <x-admin.surface padded>
            <x-admin.toolbar title="All Users">
                <span data-users-summary data-testid="users-summary"></span>
            </x-admin.toolbar>

            <x-admin.table data-testid="users-table">
                    <thead>
                        <tr>
                            <th>
                                <x-admin.sort-button
                                    data-users-sort-trigger
                                    sort-key="id"
                                    :active="request('sort') === 'id'"
                                    :direction="request('sort') === 'id' ? request('direction') : null"
                                >
                                    ID
                                </x-admin.sort-button>
                            </th>
                            <th>
                                <x-admin.sort-button
                                    data-users-sort-trigger
                                    sort-key="name"
                                    :active="request('sort') === 'name'"
                                    :direction="request('sort') === 'name' ? request('direction') : null"
                                >
                                    Name
                                </x-admin.sort-button>
                            </th>
                            <th>
                                <x-admin.sort-button
                                    data-users-sort-trigger
                                    sort-key="email"
                                    :active="request('sort') === 'email'"
                                    :direction="request('sort') === 'email' ? request('direction') : null"
                                >
                                    Email
                                </x-admin.sort-button>
                            </th>
                            <th>
                                <x-admin.sort-button
                                    data-users-sort-trigger
                                    sort-key="role"
                                    :active="request('sort') === 'role'"
                                    :direction="request('sort') === 'role' ? request('direction') : null"
                                >
                                    Access
                                </x-admin.sort-button>
                            </th>
                            <th>
                                <x-admin.sort-button
                                    data-users-sort-trigger
                                    sort-key="registeredAt"
                                    :active="request('sort', 'registeredAt') === 'registeredAt'"
                                    :direction="request('sort', 'registeredAt') === 'registeredAt' ? request('direction', 'desc') : null"
                                >
                                    Joined
                                </x-admin.sort-button>
                            </th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody data-users-table-body></tbody>
            </x-admin.table>
            <x-admin.empty-state
                class="is-hidden"
                title="No users found"
                description="The current filter returned no records."
                data-users-empty
                data-testid="users-empty"
            />

            <x-admin.pagination data-users-pagination data-testid="users-pagination" />
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
