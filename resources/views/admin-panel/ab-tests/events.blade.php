@extends('admin-panel.layouts.admin')

@php
    $hasEvents = count($events->items) > 0;
    $pageState = $hasEvents ? 'ready' : 'empty';
@endphp

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="ab-test-events"
        data-page-state="{{ $pageState }}"
        aria-busy="false"
        data-ab-test-events-endpoint="{{ route('admin.api.ab-tests.events', $abTest, absolute: false) }}"
        data-ab-test-title="{{ $abTest->name }}"
        data-testid="admin-ab-test-events-page"
    >
        <x-admin.resource-header title="AB Test Events" subtitle="Paginated tracking events emitted by enrolled visitors.">
            <x-admin.button href="{{ route('admin.ab-tests.show', $abTest) }}">Back to overview</x-admin.button>
        </x-admin.resource-header>

        @include('admin-panel.ab-tests.partials.navigation', ['abTest' => $abTest])

        <x-admin.surface padded>
            <x-admin.toolbar title="Events">
                <span data-ab-test-events-summary class="admin-toolbar-meta">{{ $events->total }} total events</span>
            </x-admin.toolbar>

            <x-admin.table data-testid="ab-test-events-table">
                <thead>
                    <tr>
                        <th>Event</th>
                        <th>Variant</th>
                        <th>Visitor</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody data-ab-test-events-body>
                    @include('admin-panel.ab-tests.partials.event-rows', ['events' => $events->items])
                </tbody>
            </x-admin.table>

            <x-admin.empty-state class="{{ $hasEvents ? 'is-hidden' : '' }}" title="No events yet" description="Tracked events will appear here." data-ab-test-events-empty />
            <x-admin.pagination data-ab-test-events-pagination>
                @include('admin-panel.ab-tests.partials.simple-pagination', [
                    'currentPage' => $events->currentPage,
                    'totalPages' => $totalPages,
                ])
            </x-admin.pagination>
        </x-admin.surface>
    </x-admin.page>
@endsection
