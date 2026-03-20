<x-admin.empty-state
    title="Resource not found"
    description="The requested record could not be loaded."
    {{ $attributes }}
>
    {{ $slot }}
</x-admin.empty-state>
