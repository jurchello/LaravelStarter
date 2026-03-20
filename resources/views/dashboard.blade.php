@extends('layouts.site')

@section('title', 'Dashboard')

@section('content')
    <x-site.page class="row g-4">
        <div class="col-lg-7">
        <x-site.card padded>
            <p class="text-uppercase small fw-semibold text-body-secondary mb-2">Dashboard</p>
            <h1 class="display-6 fw-semibold">Account is ready.</h1>
            <p class="lead text-body-secondary">
                Authentication, email verification, password recovery and session-based access are enabled in this template.
            </p>
            <div class="d-flex flex-wrap gap-2">
                @if (auth()->user()->is_admin)
                    <x-site.button :href="route('admin.dashboard')" variant="primary">Open management panel</x-site.button>
                @endif
                <x-site.button :href="url('/')">Open landing page</x-site.button>
            </div>
        </x-site.card>
        </div>

        <div class="col-lg-5">
        <x-site.card soft padded>
            <p class="text-uppercase small fw-semibold text-body-secondary mb-2">Profile</p>
            <x-site.kv-list>
                <x-site.kv-item label="Name" :value="auth()->user()->name" />
                <x-site.kv-item label="Email" :value="auth()->user()->email" />
                <x-site.kv-item label="Verified" :value="auth()->user()->hasVerifiedEmail() ? 'Yes' : 'No'" />
                <x-site.kv-item label="Role" :value="auth()->user()->is_admin ? 'Admin' : 'User'" />
            </x-site.kv-list>
        </x-site.card>
        </div>
    </x-site.page>
@endsection
