@props([
    'href' => null,
    'active' => false,
])

<li class="admin-breadcrumbs__item">
    @if ($href && ! $active)
        <a href="{{ $href }}" class="admin-breadcrumbs__link">{{ $slot }}</a>
    @else
        <span class="admin-breadcrumbs__current">{{ $slot }}</span>
    @endif
</li>
