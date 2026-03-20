@forelse ($roles as $role)
    <tr data-testid="roles-table-row">
        <td>#{{ $role->id }}</td>
        <td>{{ $role->name }}</td>
        <td>{{ $role->usersCount }}</td>
        <td>
            @if (count($role->permissions) === 0)
                <span class="admin-badge admin-badge--muted">No permissions</span>
            @else
                <span class="admin-badge admin-badge--accent">{{ count($role->permissions) }} assigned</span>
            @endif
        </td>
        <td>
            <div class="admin-row-actions">
                <button class="admin-button" type="button" data-role-edit="{{ $role->id }}">Edit</button>
                <button class="admin-button" type="button" data-role-edit-permissions="{{ $role->id }}">Permissions</button>
                <button class="admin-button" type="button" data-role-delete="{{ $role->id }}">Delete</button>
            </div>
        </td>
    </tr>
@empty
@endforelse
