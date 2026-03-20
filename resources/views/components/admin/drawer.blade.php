@props([
    'title' => null,
    'open' => false,
])

<aside
    {{ $attributes->class(['admin-drawer', 'is-open' => $open]) }}
    @if (! $open) hidden @endif
    role="dialog"
    aria-modal="true"
>
    <div class="admin-drawer__panel">
        @if ($title)
            <header class="admin-drawer__header">
                <h3 class="admin-drawer__title">{{ $title }}</h3>
            </header>
        @endif

        <div class="admin-drawer__body">
            {{ $slot }}
        </div>
    </div>
</aside>
