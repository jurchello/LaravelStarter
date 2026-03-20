@if ($user->isSuperadmin)
    <span class="admin-badge admin-badge--accent">Superadmin</span>
@endif

@forelse ($user->roles as $role)
    <span class="admin-badge admin-badge--muted">{{ $role }}</span>
@empty
    @if (! $user->isSuperadmin)
        <span class="admin-badge admin-badge--muted">No roles</span>
    @endif
@endforelse
