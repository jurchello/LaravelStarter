@props([
    'title',
    'active' => false,
    'completed' => false,
])

<li {{ $attributes->class([
    'admin-stepper__item',
    'is-active' => $active,
    'is-completed' => $completed,
]) }}>
    <span class="admin-stepper__marker">{{ $completed ? '✓' : '•' }}</span>
    <span class="admin-stepper__title">{{ $title }}</span>
</li>
