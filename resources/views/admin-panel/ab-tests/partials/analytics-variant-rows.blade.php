@forelse ($variants as $variant)
    <tr data-testid="ab-test-analytics-variant-row">
        <td>{{ $variant->name }}</td>
        <td>{{ $variant->slug }}</td>
        <td>{{ $variant->weight }}</td>
        <td>{{ $variant->assignmentsCount }}</td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="admin-toolbar-meta">No variants yet.</td>
    </tr>
@endforelse
