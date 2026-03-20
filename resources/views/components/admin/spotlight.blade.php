@props([
    'open' => false,
    'placeholder' => 'Search commands...',
])

<section
    {{ $attributes->class(['admin-spotlight', 'is-open' => $open]) }}
    @if (! $open) hidden @endif
    role="dialog"
    aria-modal="true"
>
    <div class="admin-spotlight__panel">
        <x-admin.input type="search" :placeholder="$placeholder" class="admin-spotlight__input" />
        <div class="admin-spotlight__results">
            {{ $slot }}
        </div>
    </div>
</section>
