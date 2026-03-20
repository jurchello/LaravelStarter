@forelse ($users as $user)
    <tr data-testid="users-table-row">
        <td>#{{ $user->id }}</td>
        <td>{{ $user->name }}</td>
        <td>{{ $user->email }}</td>
        <td>
            <div class="admin-applied-filters">
                @include('admin-panel.users.partials.access-badges', ['user' => $user])
            </div>
        </td>
        <td>{{ $user->registeredAt->format('Y-m-d') }}</td>
        <td>
            <div class="admin-row-actions">
                <button class="admin-button" type="button" data-user-impersonate="{{ $user->id }}">Login as</button>
                <button class="admin-button" type="button" data-user-edit-access="{{ $user->id }}">Edit access</button>
            </div>
        </td>
    </tr>
@empty
@endforelse
