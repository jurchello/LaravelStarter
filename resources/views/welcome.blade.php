@extends('layouts.site')

@section('title', config('app.name'))

@section('content')
    <x-site.page class="row g-4">
        <div class="col-lg-7">
        <x-site.card padded>
            <p class="text-uppercase small fw-semibold text-body-secondary mb-2">Template baseline</p>
            <h1 class="display-5 fw-semibold">Ship projects with auth already wired.</h1>
            <p class="lead text-body-secondary">
                This starter now includes session authentication, registration, email verification, password recovery and
                verified-only protected routes out of the box.
            </p>

            <div class="d-flex flex-wrap gap-2">
                @auth
                    <x-site.button :href="route('dashboard')" variant="primary">Open dashboard</x-site.button>
                    @if (auth()->user()->is_admin)
                        <x-site.button :href="route('admin.dashboard')">Open management</x-site.button>
                    @endif
                @else
                    <x-site.button :href="route('register')" variant="primary">Create account</x-site.button>
                    <x-site.button :href="route('login')">Log in</x-site.button>
                @endauth
            </div>
        </x-site.card>
        </div>

        <div class="col-lg-5">
        <x-site.card soft padded>
            <p class="text-uppercase small fw-semibold text-body-secondary mb-2">What is included</p>
            <ul class="list-group list-group-flush">
                <li class="list-group-item">Login and registration screens with stable `data-testid` hooks</li>
                <li class="list-group-item">Email verification with signed links and resend flow</li>
                <li class="list-group-item">Forgot password and reset password flow</li>
                <li class="list-group-item">Verified-only dashboard and admin panel access</li>
            </ul>
        </x-site.card>
        </div>
    </x-site.page>
@endsection
