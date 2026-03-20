@props([
    'id' => null,
    'name' => 'search',
    'value' => null,
    'placeholder' => 'Search',
    'listId' => null,
    'autocomplete' => 'off',
])

<div {{ $attributes->class(['admin-search']) }}>
    <x-admin.input
        :id="$id"
        :name="$name"
        type="search"
        :value="$value"
        :placeholder="$placeholder"
        :list="$listId"
        :autocomplete="$autocomplete"
        class="admin-search__input"
    />

    @if (trim((string) $slot) !== '')
        {{ $slot }}
    @endif
</div>
