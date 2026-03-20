@extends('admin-panel.layouts.admin')

@php
    $hasAssignments = count($assignments->items) > 0;
    $pageState = $hasAssignments ? 'ready' : 'empty';
@endphp

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="ab-test-assignments"
        data-page-state="{{ $pageState }}"
        aria-busy="false"
        data-ab-test-assignments-endpoint="{{ route('admin.api.ab-tests.assignments', $abTest, absolute: false) }}"
        data-ab-test-title="{{ $abTest->name }}"
        data-testid="admin-ab-test-assignments-page"
    >
        <x-admin.resource-header title="AB Test Assignments" subtitle="Paginated enrollment history for the selected experiment.">
            <x-admin.button href="{{ route('admin.ab-tests.show', $abTest) }}">Back to overview</x-admin.button>
        </x-admin.resource-header>

        @include('admin-panel.ab-tests.partials.navigation', ['abTest' => $abTest])

        <x-admin.surface padded>
            <x-admin.toolbar title="Assignments">
                <span data-ab-test-assignments-summary class="admin-toolbar-meta">{{ $assignments->total }} total assignments</span>
            </x-admin.toolbar>

            <x-admin.table data-testid="ab-test-assignments-table">
                <thead>
                    <tr>
                        <th>Visitor</th>
                        <th>User</th>
                        <th>Variant</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody data-ab-test-assignments-body>
                    @include('admin-panel.ab-tests.partials.assignment-rows', ['assignments' => $assignments->items])
                </tbody>
            </x-admin.table>

            <x-admin.empty-state class="{{ $hasAssignments ? 'is-hidden' : '' }}" title="No assignments yet" description="Visitor enrollments will appear here." data-ab-test-assignments-empty />
            <x-admin.pagination data-ab-test-assignments-pagination>
                @include('admin-panel.ab-tests.partials.simple-pagination', [
                    'currentPage' => $assignments->currentPage,
                    'totalPages' => $totalPages,
                ])
            </x-admin.pagination>
        </x-admin.surface>
    </x-admin.page>
@endsection
