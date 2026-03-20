@extends('admin-panel.layouts.admin')

@section('content')
    <x-admin.page
        class="admin-page"
        data-admin-page="dashboard"
        data-dashboard-endpoint="{{ route('admin.api.dashboard', absolute: false) }}"
        data-testid="admin-dashboard-page"
    >
        <x-admin.page-header
            title="Dashboard"
            subtitle="Operational overview for the current project."
        />

        <div class="admin-stat-grid" data-dashboard-stats data-testid="dashboard-stats"></div>
    </x-admin.page>
@endsection
