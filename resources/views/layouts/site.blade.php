<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
        crossorigin="anonymous"
    >
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-body-tertiary">
    <div>
        @include('partials.flashed-toasts')

        @if (session()->has(\App\Domain\User\ValueObjects\ImpersonationSession::IMPERSONATOR_ID))
            <x-site.alert
                class="rounded-0 border-0 mb-0"
                variant="danger"
                title="Impersonation mode"
                data-testid="impersonation-banner"
            >
                <div class="container d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <span>
                        You are signed in as {{ auth()->user()?->name }}.
                        Return to {{ session(\App\Domain\User\ValueObjects\ImpersonationSession::IMPERSONATOR_NAME, 'the original admin account') }} when finished.
                    </span>

                    <form method="POST" action="{{ route('impersonation.leave') }}">
                        @csrf
                        <x-site.button type="submit" variant="primary">Return to admin</x-site.button>
                    </form>
                </div>
            </x-site.alert>
        @endif

        <header class="border-bottom bg-white">
            <div class="container d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 py-3">
                <a href="{{ url('/') }}" class="text-decoration-none text-body-emphasis d-inline-flex align-items-center gap-2 fw-semibold">
                    <span class="badge text-bg-dark">LS</span>
                    <span>{{ config('app.name') }}</span>
                </a>

                <nav class="d-flex flex-wrap align-items-center gap-2">
                    @auth
                        <x-site.button :href="route('dashboard')">Dashboard</x-site.button>
                        @if (auth()->user()->is_admin)
                            <x-site.button :href="route('admin.dashboard')">Management</x-site.button>
                        @endif
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-site.button type="submit" variant="primary">Log out</x-site.button>
                        </form>
                    @else
                        <x-site.button :href="route('login')">Log in</x-site.button>
                        <x-site.button :href="route('register')" variant="primary">Register</x-site.button>
                    @endauth
                </nav>
            </div>
        </header>

        <main class="py-5">
            <div class="container">
                @yield('content')
            </div>
        </main>
    </div>

    <div class="position-fixed top-0 end-0 z-3 p-3 d-flex flex-column gap-2" data-site-toast-container></div>
</body>
</html>
