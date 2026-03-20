@extends('layouts.site')

@section('title', 'Reset password')

@section('content')
    <x-site.page
        data-site-page="reset-password"
        data-page-state="ready"
        data-testid="site-reset-password-page"
    >
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <x-site.card padded>
                <div class="mb-4">
                    <p class="text-uppercase small fw-semibold text-body-secondary mb-2">Password reset</p>
                    <h1 class="h3">Set a new password</h1>
                    <p class="text-body-secondary mb-0">Choose a new password for your account.</p>
                </div>

                <form method="POST" action="{{ route('password.update') }}" data-testid="reset-password-form">
                    @csrf

                    <input type="hidden" name="token" value="{{ $token }}">

                    <x-site.form-field
                        for="email"
                        label="Email"
                    >
                        <x-site.input
                            id="email"
                            name="email"
                            type="email"
                            value="{{ old('email', $email) }}"
                            required
                            autofocus
                            autocomplete="username"
                            data-testid="reset-password-email"
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
                            data-testid="reset-password-password"
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
                            data-testid="reset-password-password-confirmation"
                        />
                    </x-site.form-field>

                    <x-site.button type="submit" variant="primary" data-testid="reset-password-submit">
                        Reset password
                    </x-site.button>
                </form>
            </x-site.card>
        </div>
    </div>
    </x-site.page>
@endsection
