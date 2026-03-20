@extends('admin-panel.layouts.admin')

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="ab-test-analytics"
        data-ab-test-analytics-endpoint="{{ route('admin.api.ab-tests.analytics', $abTest, absolute: false) }}"
        data-testid="admin-ab-test-analytics-page"
    >
        <x-admin.resource-header title="AB Test Analytics" subtitle="Current totals, event breakdown, and variant allocation.">
            <x-admin.button href="{{ route('admin.ab-tests.show', $abTest) }}">Back to overview</x-admin.button>
        </x-admin.resource-header>

        @include('admin-panel.ab-tests.partials.navigation', ['abTest' => $abTest])

        <x-admin.metric-grid data-ab-test-analytics-stats>
            <x-admin.stat-card label="Assignments" value="0" tone="neutral" data-ab-test-analytics-stat="assignments" />
            <x-admin.stat-card label="Identified" value="0" tone="accent" data-ab-test-analytics-stat="identified" />
            <x-admin.stat-card label="Variants" value="0" tone="success" data-ab-test-analytics-stat="variants" />
            <x-admin.stat-card label="Events" value="0" tone="warning" data-ab-test-analytics-stat="events" />
        </x-admin.metric-grid>

        <x-admin.resource-layout>
            <div>
                <x-admin.resource-section title="Event breakdown">
                    <div data-ab-test-analytics-events></div>
                </x-admin.resource-section>

                <x-admin.resource-section title="Variant allocation">
                    <x-admin.table>
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Weight</th>
                                <th>Assignments</th>
                            </tr>
                        </thead>
                        <tbody data-ab-test-analytics-variants></tbody>
                    </x-admin.table>
                </x-admin.resource-section>
            </div>

            <x-admin.resource-sidebar>
                <x-admin.detail-card title="Status">
                    <x-admin.data-list>
                        <x-admin.data-item label="Status"><span data-ab-test-analytics-status></span></x-admin.data-item>
                        <x-admin.data-item label="Traffic"><span data-ab-test-analytics-traffic></span></x-admin.data-item>
                    </x-admin.data-list>
                </x-admin.detail-card>
            </x-admin.resource-sidebar>
        </x-admin.resource-layout>
    </x-admin.page>
@endsection
