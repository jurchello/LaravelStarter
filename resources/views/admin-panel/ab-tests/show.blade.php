@extends('admin-panel.layouts.admin')

@php
    $statusVariant = match ($testView->status) {
        'active' => 'success',
        'paused' => 'warning',
        'finished' => 'danger',
        default => 'muted',
    };
    $eventsTotal = array_sum($testView->analytics->eventsByName);
    $primaryStatusAction = match ($testView->status) {
        'draft' => 'active',
        'active' => 'paused',
        'paused' => 'active',
        default => null,
    };
    $allowedStatusActions = match ($testView->status) {
        'draft' => ['active'],
        'active' => ['paused', 'finished'],
        'paused' => ['active', 'finished'],
        default => [],
    };
@endphp

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="ab-test-management"
        data-page-state="ready"
        aria-busy="false"
        data-ab-test-id="{{ $abTest->id }}"
        data-ab-test-endpoint="{{ route('admin.api.ab-tests.show', $abTest, absolute: false) }}"
        data-ab-test-update-endpoint="{{ route('admin.api.ab-tests.update', $abTest, absolute: false) }}"
        data-ab-test-delete-endpoint="{{ route('admin.api.ab-tests.destroy', $abTest, absolute: false) }}"
        data-ab-test-status-endpoint="{{ route('admin.api.ab-tests.status', $abTest, absolute: false) }}"
        data-ab-test-variants-endpoint="{{ route('admin.api.ab-tests.variants.store', $abTest, absolute: false) }}"
        data-ab-test-audience-estimate-endpoint="{{ route('admin.api.ab-tests.audience-estimate', absolute: false) }}"
        data-ab-tests-base-route="{{ route('admin.ab-tests.index', absolute: false) }}"
        data-testid="admin-ab-test-management-page"
    >
        <x-admin.resource-header title="AB Test" subtitle="Manage experiment configuration, variants, and recent activity.">
            <span class="admin-toolbar-meta" data-ab-test-header-status>{{ $testView->name }} · {{ $testView->slug }} · {{ $testView->trafficPercent }}% traffic · {{ $testView->distributionMode->value === 'equal' ? 'equal split' : 'manual weights' }}</span>
            <x-admin.button href="{{ route('admin.ab-tests.index') }}">Back to tests</x-admin.button>
            <button
                type="button"
                class="admin-button {{ $primaryStatusAction === 'active' ? 'admin-button--primary' : '' }}"
                data-ab-test-status-action="active"
                {{ in_array('active', $allowedStatusActions, true) ? '' : 'disabled' }}
            >
                Activate
            </button>
            <button
                type="button"
                class="admin-button {{ $primaryStatusAction === 'paused' ? 'admin-button--primary' : '' }}"
                data-ab-test-status-action="paused"
                {{ in_array('paused', $allowedStatusActions, true) ? '' : 'disabled' }}
            >
                Pause
            </button>
            <button
                type="button"
                class="admin-button {{ $primaryStatusAction === 'finished' ? 'admin-button--primary' : '' }}"
                data-ab-test-status-action="finished"
                {{ in_array('finished', $allowedStatusActions, true) ? '' : 'disabled' }}
            >
                Finish
            </button>
            <x-admin.button type="button" data-ab-test-delete-trigger>Delete</x-admin.button>
        </x-admin.resource-header>

        @include('admin-panel.ab-tests.partials.navigation', ['abTest' => $abTest])

        <x-admin.metric-grid data-ab-test-stats>
            <x-admin.stat-card label="Assignments" :value="$testView->analytics->assignmentsCount" tone="neutral" data-ab-test-stat="assignments" />
            <x-admin.stat-card label="Identified" :value="$testView->analytics->identifiedAssignmentsCount" tone="accent" data-ab-test-stat="identified" />
            <x-admin.stat-card label="Variants" :value="count($testView->variants)" tone="success" data-ab-test-stat="variants" />
            <x-admin.stat-card label="Events" :value="$eventsTotal" tone="warning" data-ab-test-stat="events" />
        </x-admin.metric-grid>

        <x-admin.resource-layout>
            <div>
                <x-admin.resource-section title="Test configuration">
                    <x-admin.form data-ab-test-config-form>
                        <x-admin.form-grid class="admin-form-grid--2">
                            <x-admin.field for="ab-test-name" label="Name">
                                <x-admin.input
                                    id="ab-test-name"
                                    name="name"
                                    autocomplete="off"
                                    :value="$testView->name"
                                    data-ab-test-input="name"
                                />
                            </x-admin.field>

                            <x-admin.field for="ab-test-slug" label="Slug">
                                <x-admin.input
                                    id="ab-test-slug"
                                    name="slug"
                                    autocomplete="off"
                                    readonly
                                    :value="$testView->slug"
                                    data-ab-test-input="slug"
                                />
                            </x-admin.field>
                        </x-admin.form-grid>

                        <x-admin.field for="ab-test-traffic" label="Traffic Percent">
                            <x-admin.input
                                id="ab-test-traffic"
                                name="trafficPercent"
                                type="number"
                                min="0"
                                max="100"
                                :value="$testView->trafficPercent"
                                data-ab-test-input="traffic"
                            />
                            <x-admin.form-help data-ab-test-traffic-estimate></x-admin.form-help>
                        </x-admin.field>

                        <x-admin.field label="Variant distribution" hint="Equal split ignores manual weights and picks a variant uniformly at random.">
                            <x-admin.checkbox
                                id="ab-test-distribution-mode"
                                name="distributionMode"
                                label="Split evenly across all variants"
                                :checked="$testView->distributionMode->value === 'equal'"
                                data-ab-test-input="split-evenly"
                            />
                        </x-admin.field>

                        <x-admin.form-actions>
                            <x-admin.button type="submit" variant="primary">Save changes</x-admin.button>
                        </x-admin.form-actions>
                    </x-admin.form>
                </x-admin.resource-section>

                <x-admin.resource-section title="Variants" description="Weights are evaluated when the test is activated.">
                    <x-admin.form data-ab-test-variant-form data-ab-test-variant-mode="create">
                        <x-admin.form-grid class="admin-form-grid--3">
                            <x-admin.field for="ab-test-variant-name" label="Name">
                                <x-admin.input id="ab-test-variant-name" name="name" autocomplete="off" data-ab-test-variant-input="name" />
                            </x-admin.field>

                            <x-admin.field for="ab-test-variant-slug" label="Slug">
                                <x-admin.input id="ab-test-variant-slug" name="slug" autocomplete="off" data-ab-test-variant-input="slug" />
                            </x-admin.field>

                            <x-admin.field for="ab-test-variant-weight" label="Weight" hint="Used only when equal split is off.">
                                <x-admin.input id="ab-test-variant-weight" name="weight" type="number" min="1" max="10000" value="{{ $testView->distributionMode->value === 'equal' ? '100' : '100' }}" data-ab-test-variant-input="weight" />
                            </x-admin.field>
                        </x-admin.form-grid>

                        <x-admin.form-actions>
                            <x-admin.button type="submit" variant="primary" data-ab-test-variant-submit>Add variant</x-admin.button>
                            <x-admin.button type="button" data-ab-test-variant-reset hidden>Cancel edit</x-admin.button>
                        </x-admin.form-actions>
                    </x-admin.form>

                    <x-admin.table data-testid="ab-test-variants-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Slug</th>
                                <th>Weight</th>
                                <th>Assignments</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody data-ab-test-variants-body>
                            @include('admin-panel.ab-tests.partials.variant-rows', ['variants' => $testView->variants])
                        </tbody>
                    </x-admin.table>
                </x-admin.resource-section>
            </div>

            <x-admin.resource-sidebar>
                <x-admin.detail-card title="Status">
                    <div class="admin-resource-status">
                        <x-admin.status-pill :variant="$statusVariant" data-ab-test-status-pill>{{ $testView->status }}</x-admin.status-pill>
                    </div>
                </x-admin.detail-card>

                <x-admin.detail-card title="Events by name">
                    <div data-ab-test-events-summary>
                        @forelse ($testView->analytics->eventsByName as $eventName => $count)
                            <div class="admin-data-item">
                                <span class="admin-data-item__label">{{ $eventName }}</span>
                                <span class="admin-data-item__value">{{ $count }}</span>
                            </div>
                        @empty
                            <p class="admin-toolbar-meta">No events tracked yet.</p>
                        @endforelse
                    </div>
                </x-admin.detail-card>
            </x-admin.resource-sidebar>
        </x-admin.resource-layout>
    </x-admin.page>
@endsection
