@forelse ($flags as $flag)
    <tr data-testid="feature-flags-table-row">
        <td>#{{ $flag->id }}</td>
        <td>{{ $flag->key }}</td>
        <td>{{ $flag->name }}</td>
        <td>
            @if ($flag->enabled)
                <span class="admin-badge admin-badge--accent">Enabled</span>
            @else
                <span class="admin-badge admin-badge--muted">Disabled</span>
            @endif
        </td>
        <td>{{ $flag->rolloutPercent }}%</td>
        <td>
            <div class="admin-row-actions">
                <button class="admin-button" type="button" data-feature-flag-edit="{{ $flag->id }}">Edit</button>
                <button class="admin-button" type="button" data-feature-flag-delete="{{ $flag->id }}">Delete</button>
            </div>
        </td>
    </tr>
@empty
@endforelse
