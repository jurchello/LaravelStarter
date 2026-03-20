@forelse ($events as $event)
    <tr data-testid="ab-test-event-row">
        <td>{{ $event->event }}</td>
        <td>{{ $event->variantName }}</td>
        <td>{{ $event->visitorId }}</td>
        <td>{{ $event->createdAt }}</td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="admin-toolbar-meta">No events yet.</td>
    </tr>
@endforelse
