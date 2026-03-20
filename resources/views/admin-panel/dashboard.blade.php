@extends('admin-panel.layouts.admin')

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="dashboard"
        data-page-state="ready"
        aria-busy="false"
        data-dashboard-endpoint="{{ route('admin.api.dashboard', absolute: false) }}"
        data-testid="admin-dashboard-page"
    >
        <x-admin.page-header
            title="Dashboard"
            subtitle="Operational overview for the current project."
        />

        <div class="admin-stat-grid" data-dashboard-stats data-testid="dashboard-stats">
            @foreach ($stats as $stat)
                <article class="admin-stat-card" data-tone="{{ $stat['tone'] }}">
                    <p class="admin-stat-card__label">{{ $stat['label'] }}</p>
                    <p class="admin-stat-card__value">{{ $stat['value'] }}</p>
                </article>
            @endforeach
        </div>
    </x-admin.page>
@endsection
