@forelse ($variants as $variant)
    <tr data-testid="ab-test-variant-row">
        <td>{{ $variant->name }}</td>
        <td>{{ $variant->slug }}</td>
        <td>{{ $variant->weight }}</td>
        <td>{{ $variant->assignmentsCount }}</td>
        <td>
            <div class="admin-data-grid__actions">
                <button class="admin-button" type="button" data-ab-test-variant-edit="{{ $variant->id }}">Use values</button>
                <button class="admin-button" type="button" data-ab-test-variant-delete="{{ $variant->id }}">Delete</button>
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="admin-toolbar-meta">No variants yet.</td>
    </tr>
@endforelse
