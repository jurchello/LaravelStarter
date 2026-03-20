@props([
    'id' => null,
    'name' => null,
    'label' => 'Choose file',
])

<label {{ $attributes->class(['admin-file-upload']) }}>
    <span class="admin-file-upload__label">{{ $label }}</span>
    <input
        @if ($id) id="{{ $id }}" @endif
        @if ($name) name="{{ $name }}" @endif
        type="file"
        class="admin-file-upload__input"
    >
</label>
