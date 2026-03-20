@props([
    'title' => null,
    'open' => false,
])

<aside
    {{ $attributes->class(['admin-offcanvas-form', 'is-open' => $open]) }}
    @if (! $open) hidden @endif
    role="dialog"
    aria-modal="true"
>
    <div class="admin-offcanvas-form__panel">
        @if ($title)
            <header class="admin-offcanvas-form__header">
                <h3 class="admin-offcanvas-form__title">{{ $title }}</h3>
            </header>
        @endif
        <div class="admin-offcanvas-form__body">
            {{ $slot }}
        </div>
    </div>
</aside>
