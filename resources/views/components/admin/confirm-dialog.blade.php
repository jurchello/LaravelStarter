@props([
    'title' => 'Confirm action',
    'description' => null,
    'confirmLabel' => 'Confirm',
    'cancelLabel' => 'Cancel',
])

<x-admin.modal :title="$title" {{ $attributes }}>
    @if ($description)
        <p class="admin-confirm-dialog__description">{{ $description }}</p>
    @endif

    <x-admin.form-actions>
        <x-admin.button type="button">{{ $cancelLabel }}</x-admin.button>
        <x-admin.button type="submit" variant="primary">{{ $confirmLabel }}</x-admin.button>
    </x-admin.form-actions>
</x-admin.modal>
