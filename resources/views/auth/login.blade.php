@extends('layouts.site')

@section('title', 'Log in')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <x-site.card padded>
                <div class="mb-4">
                    <p class="text-uppercase small fw-semibold text-body-secondary mb-2">Authentication</p>
                    <h1 class="h3">Log in</h1>
                    <p class="text-body-secondary mb-0">Use your account to access the dashboard and management panel.</p>
                </div>

                @if (filled(config('services.google.client_id')) && filled(config('services.google.client_secret')) && filled(config('services.google.redirect')))
                    <div class="d-grid gap-3 mb-4">
                        <x-site.button :href="route('auth.google.redirect')" data-testid="login-google-auth">
                            Continue with Google
                        </x-site.button>
                        <div class="text-center text-body-secondary small">or use email and password</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}" data-testid="login-form">
                    @csrf

                    <x-site.form-field
                        for="email"
                        label="Email"
                    >
                        <x-site.input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            data-testid="login-email"
                        />
                    </x-site.form-field>

                    <x-site.form-field
                        for="password"
                        label="Password"
                        :link-href="route('password.request')"
                        link-label="Forgot password?"
                    >
                        <x-site.input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="current-password"
                            data-testid="login-password"
                        />
                    </x-site.form-field>

                    <x-site.checkbox name="remember" value="1" label="Remember me" />

                    <x-site.button type="submit" variant="primary" data-testid="login-form-submit">
                        Log in
                    </x-site.button>
                </form>
            </x-site.card>
        </div>
    </div>
@endsection
