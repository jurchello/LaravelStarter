<x-admin.empty-state
    title="No results matched your search"
    description="Try changing filters or broadening the query."
    {{ $attributes }}
>
    {{ $slot }}
</x-admin.empty-state>
