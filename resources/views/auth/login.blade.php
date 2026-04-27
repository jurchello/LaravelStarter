@extends('layouts.site')

@section('title', 'Log in')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h1 class="h4 mb-4">Log in</h1>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            {{ $errors->first() }}
                        </div>
                    @endif

                    @if (filled(config('services.google.client_id')) && filled(config('services.google.client_secret')) && filled(config('services.google.redirect')))
                        <a class="btn btn-outline-secondary w-100 mb-3" href="{{ route('auth.google.redirect') }}">
                            Continue with Google
                        </a>
                    @endif

                    <form method="POST" action="{{ route('login.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label" for="email">Email</label>
                            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="password">Password</label>
                            <input class="form-control @error('password') is-invalid @enderror" id="password" name="password" type="password" required autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" id="remember" name="remember" type="checkbox" value="1">
                            <label class="form-check-label" for="remember">Remember me</label>
                        </div>

                        <button class="btn btn-primary w-100" type="submit">Log in</button>
                    </form>

                    <div class="mt-3 d-flex justify-content-between">
                        <a href="{{ route('password.request') }}">Forgot password?</a>
                        <a href="{{ route('register') }}">Register</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
