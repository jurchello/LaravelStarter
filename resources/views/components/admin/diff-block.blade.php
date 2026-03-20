@props([
    'before' => null,
    'after' => null,
])

<div {{ $attributes->class(['admin-diff-block']) }}>
    <div class="admin-diff-block__column">
        <span class="admin-diff-block__label">Before</span>
        <pre class="admin-diff-block__code"><code>{{ $before ?? '' }}</code></pre>
    </div>
    <div class="admin-diff-block__column">
        <span class="admin-diff-block__label">After</span>
        <pre class="admin-diff-block__code"><code>{{ $after ?? $slot }}</code></pre>
    </div>
</div>
