@forelse ($assignments as $assignment)
    <tr data-testid="ab-test-assignment-row">
        <td>{{ $assignment->visitorId }}</td>
        <td>{{ $assignment->userId === null ? 'Guest' : '#'.$assignment->userId }}</td>
        <td>{{ $assignment->variantName }}</td>
        <td>{{ $assignment->createdAt }}</td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="admin-toolbar-meta">No assignments yet.</td>
    </tr>
@endforelse
