@extends('layouts.site')

@section('title', 'Verify email')

@section('content')
    <x-site.page
        data-site-page="verify-email"
        data-page-state="ready"
        data-testid="site-verify-email-page"
    >
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <x-site.card padded>
                <div class="mb-4">
                    <p class="text-uppercase small fw-semibold text-body-secondary mb-2">Email verification</p>
                    <h1 class="h3">Verify your email address</h1>
                    <p class="text-body-secondary mb-0">
                        Before continuing, please check your inbox for a verification link. If you did not receive the email,
                        we can send another one.
                    </p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <x-site.button type="submit" variant="primary" data-testid="verification-resend-submit">
                            Resend verification email
                        </x-site.button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-site.button type="submit">Log out</x-site.button>
                    </form>
                </div>
            </x-site.card>
        </div>
    </div>
    </x-site.page>
@endsection
