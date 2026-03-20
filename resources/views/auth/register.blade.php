@extends('layouts.site')

@section('title', 'Register')

@section('content')
    <x-site.page
        data-site-page="register"
        data-page-state="ready"
        data-testid="site-register-page"
    >
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <x-site.card padded>
                <div class="mb-4">
                    <p class="text-uppercase small fw-semibold text-body-secondary mb-2">Authentication</p>
                    <h1 class="h3">Create account</h1>
                    <p class="text-body-secondary mb-0">New projects based on this template already have registration enabled.</p>
                </div>

                @if (filled(config('services.google.client_id')) && filled(config('services.google.client_secret')) && filled(config('services.google.redirect')))
                    <div class="d-grid gap-3 mb-4">
                        <x-site.button :href="route('auth.google.redirect')" data-testid="register-google-auth">
                            Continue with Google
                        </x-site.button>
                        <div class="text-center text-body-secondary small">or create an account with email</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('register.store') }}" data-testid="register-form">
                    @csrf

                    <x-site.form-field
                        for="name"
                        label="Name"
                    >
                        <x-site.input
                            id="name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            required
                            autofocus
                            autocomplete="name"
                            data-testid="register-name"
                        />
                    </x-site.form-field>

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
                            autocomplete="username"
                            data-testid="register-email"
                        />
                    </x-site.form-field>

                    <x-site.form-field
                        for="password"
                        label="Password"
                    >
                        <x-site.input
                            id="password"
                            name="password"
                            type="password"
                            required
                            autocomplete="new-password"
                            data-testid="register-password"
                        />
                    </x-site.form-field>

                    <x-site.form-field
                        for="password_confirmation"
                        label="Confirm password"
                    >
                        <x-site.input
                            id="password_confirmation"
                            name="password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                            data-testid="register-password-confirmation"
                        />
                    </x-site.form-field>

                    <x-site.button type="submit" variant="primary" data-testid="register-form-submit">
                        Create account
                    </x-site.button>
                </form>
            </x-site.card>
        </div>
    </div>
    </x-site.page>
@endsection
