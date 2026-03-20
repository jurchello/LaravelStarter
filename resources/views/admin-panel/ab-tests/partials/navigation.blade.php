<x-admin.tabs>
    <x-admin.tab :href="route('admin.ab-tests.show', $abTest)" :active="request()->routeIs('admin.ab-tests.show')">Overview</x-admin.tab>
    <x-admin.tab :href="route('admin.ab-tests.assignments', $abTest)" :active="request()->routeIs('admin.ab-tests.assignments')">Assignments</x-admin.tab>
    <x-admin.tab :href="route('admin.ab-tests.events', $abTest)" :active="request()->routeIs('admin.ab-tests.events')">Events</x-admin.tab>
    <x-admin.tab :href="route('admin.ab-tests.analytics', $abTest)" :active="request()->routeIs('admin.ab-tests.analytics')">Analytics</x-admin.tab>
</x-admin.tabs>
