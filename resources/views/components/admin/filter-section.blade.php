@props([
    'title' => null,
    'subtitle' => null,
])

<x-admin.surface padded {{ $attributes->class(['admin-filter-section']) }}>
    @if ($title || $subtitle)
        <x-admin.toolbar :title="$title" :subtitle="$subtitle" />
    @endif

    <div class="admin-filter-section__body">
        {{ $slot }}
    </div>
</x-admin.surface>
