@props([
    'name',
    'meta' => null,
    'size' => 'sm',
])

<div {{ $attributes->class(['admin-user-chip']) }}>
    <x-admin.avatar :name="$name" :size="$size" />
    <div class="admin-user-chip__content">
        <span class="admin-user-chip__name">{{ $name }}</span>
        @if ($meta)
            <span class="admin-user-chip__meta">{{ $meta }}</span>
        @endif
    </div>
</div>
