@extends('layouts.site')

@section('title', 'Forgot password')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <x-site.card padded>
                <div class="mb-4">
                    <p class="text-uppercase small fw-semibold text-body-secondary mb-2">Password reset</p>
                    <h1 class="h3">Forgot your password?</h1>
                    <p class="text-body-secondary mb-0">Enter your email and we will send you a reset link.</p>
                </div>

                <form method="POST" action="{{ route('password.email') }}" data-testid="forgot-password-form">
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
                            data-testid="forgot-password-email"
                        />
                    </x-site.form-field>

                    <x-site.button type="submit" variant="primary" data-testid="forgot-password-submit">
                        Email password reset link
                    </x-site.button>
                </form>
            </x-site.card>
        </div>
    </div>
@endsection
