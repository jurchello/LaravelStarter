@extends('admin-panel.layouts.admin')

@php
    $eventsTotal = array_sum($testView->analytics->eventsByName);
    $statusVariant = match ($testView->status) {
        'active' => 'success',
        'paused' => 'warning',
        'finished' => 'danger',
        default => 'muted',
    };
@endphp

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="ab-test-analytics"
        data-page-state="ready"
        aria-busy="false"
        data-ab-test-analytics-endpoint="{{ route('admin.api.ab-tests.analytics', $abTest, absolute: false) }}"
        data-testid="admin-ab-test-analytics-page"
    >
        <x-admin.resource-header title="AB Test Analytics" subtitle="Current totals, event breakdown, and variant allocation.">
            <x-admin.button href="{{ route('admin.ab-tests.show', $abTest) }}">Back to overview</x-admin.button>
        </x-admin.resource-header>

        @include('admin-panel.ab-tests.partials.navigation', ['abTest' => $abTest])

        <x-admin.metric-grid data-ab-test-analytics-stats>
            <x-admin.stat-card label="Assignments" :value="$testView->analytics->assignmentsCount" tone="neutral" data-ab-test-analytics-stat="assignments" />
            <x-admin.stat-card label="Identified" :value="$testView->analytics->identifiedAssignmentsCount" tone="accent" data-ab-test-analytics-stat="identified" />
            <x-admin.stat-card label="Variants" :value="count($testView->variants)" tone="success" data-ab-test-analytics-stat="variants" />
            <x-admin.stat-card label="Events" :value="$eventsTotal" tone="warning" data-ab-test-analytics-stat="events" />
        </x-admin.metric-grid>

        <x-admin.resource-layout>
            <div>
                <x-admin.resource-section title="Event breakdown">
                    <div data-ab-test-analytics-events>
                        @forelse ($testView->analytics->eventsByName as $eventName => $count)
                            <div class="admin-data-item">
                                <span class="admin-data-item__label">{{ $eventName }}</span>
                                <span class="admin-data-item__value">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="admin-toolbar-meta">No events tracked yet.</p>
                        @endforelse
                    </div>
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
                        <tbody data-ab-test-analytics-variants>
                            @include('admin-panel.ab-tests.partials.analytics-variant-rows', ['variants' => $testView->variants])
                        </tbody>
                    </x-admin.table>
                </x-admin.resource-section>
            </div>

            <x-admin.resource-sidebar>
                <x-admin.detail-card title="Status">
                    <x-admin.data-list>
                        <x-admin.data-item label="Status"><x-admin.status-pill :variant="$statusVariant" data-ab-test-analytics-status>{{ $testView->status }}</x-admin.status-pill></x-admin.data-item>
                        <x-admin.data-item label="Traffic"><span data-ab-test-analytics-traffic>{{ $testView->trafficPercent }}%</span></x-admin.data-item>
                    </x-admin.data-list>
                </x-admin.detail-card>
            </x-admin.resource-sidebar>
        </x-admin.resource-layout>
    </x-admin.page>
@endsection
