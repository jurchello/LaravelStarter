@extends('admin-panel.layouts.admin')

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="roles"
        data-roles-endpoint="{{ route('admin.api.roles.index', absolute: false) }}"
        data-roles-suggestions-endpoint="{{ route('admin.api.roles.suggestions', absolute: false) }}"
        data-roles-permission-update-base="{{ url('/management/api/roles') }}"
        data-roles-sort="{{ request('sort', 'name') }}"
        data-roles-direction="{{ request('direction', 'asc') }}"
        data-testid="admin-roles-page"
    >
        <x-admin.page-header
            title="Roles"
            subtitle="Session-authenticated role management loaded from the admin API."
        />

        <x-admin.filter-section title="Filters" data-roles-filter-section>
            <x-admin.table-filters class="admin-table-filters--compact admin-table-filters--single-row">
                <x-admin.search
                    id="roles-search"
                    name="search"
                    placeholder="Search by role name"
                    :value="request('search')"
                    list-id="roles-search-suggestions"
                    autocomplete="off"
                >
                    <datalist id="roles-search-suggestions" data-roles-search-suggestions></datalist>
                </x-admin.search>
            </x-admin.table-filters>
        </x-admin.filter-section>

        <x-admin.surface padded>
            <x-admin.toolbar title="Role Editor" subtitle="Create new roles or update the selected role." />

            <form data-roles-form>
                <input type="hidden" name="role_id" value="" data-roles-form-id>
                <div class="admin-filters">
                    <x-admin.field class="admin-search">
                        <x-admin.input
                            id="role-name"
                            name="name"
                            placeholder="Role name"
                            data-roles-form-name
                            autocomplete="off"
                        />
                    </x-admin.field>

                    <x-admin.form-actions>
                        <x-admin.button type="button" data-roles-form-cancel>Cancel</x-admin.button>
                        <x-admin.button type="submit" variant="primary" data-roles-form-submit>Save role</x-admin.button>
                    </x-admin.form-actions>
                </div>
            </form>
        </x-admin.surface>

        <x-admin.surface padded>
            <x-admin.toolbar title="All Roles">
                <span data-roles-summary data-testid="roles-summary"></span>
            </x-admin.toolbar>

            <x-admin.table data-testid="roles-table">
                <thead>
                    <tr>
                        <th>
                            <x-admin.sort-button
                                data-roles-sort-trigger
                                sort-key="id"
                                :active="request('sort') === 'id'"
                                :direction="request('sort') === 'id' ? request('direction') : null"
                            >
                                ID
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-roles-sort-trigger
                                sort-key="name"
                                :active="request('sort', 'name') === 'name'"
                                :direction="request('sort', 'name') === 'name' ? request('direction', 'asc') : null"
                            >
                                Name
                            </x-admin.sort-button>
                        </th>
                        <th>
                            <x-admin.sort-button
                                data-roles-sort-trigger
                                sort-key="usersCount"
                                :active="request('sort') === 'usersCount'"
                                :direction="request('sort') === 'usersCount' ? request('direction') : null"
                            >
                                Users
                            </x-admin.sort-button>
                        </th>
                        <th>Permissions</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody data-roles-table-body></tbody>
            </x-admin.table>

            <x-admin.empty-state
                class="is-hidden"
                title="No roles found"
                description="The current filter returned no roles."
                data-roles-empty
                data-testid="roles-empty"
            />

            <x-admin.pagination data-roles-pagination data-testid="roles-pagination" />
        </x-admin.surface>

        <x-admin.modal title="Edit role permissions" data-roles-permissions-modal>
            <div class="admin-panel__body">
                <form data-roles-permissions-form>
                    <input type="hidden" value="" data-roles-permissions-form-id>
                    <x-admin.multi-select
                        id="roles-permissions-multi-select"
                        name="permissions[]"
                        size="12"
                        data-roles-permissions-select
                    ></x-admin.multi-select>
                    <x-admin.form-actions class="admin-form-actions--spaced">
                        <x-admin.button type="button" data-roles-permissions-cancel>Cancel</x-admin.button>
                        <x-admin.button type="submit" variant="primary" data-roles-permissions-submit>Save permissions</x-admin.button>
                    </x-admin.form-actions>
                </form>
            </div>
        </x-admin.modal>
    </x-admin.page>
@endsection
