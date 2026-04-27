@extends('layouts.site')

@section('title', 'Verify email')

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card">
                <div class="card-body">
                    <h1 class="h4 mb-4">Verify email</h1>

                    @if (session('status'))
                        <div class="alert alert-success">{{ session('status') }}</div>
                    @endif

                    <form method="POST" action="{{ route('verification.send') }}" class="mb-3">
                        @csrf
                        <button class="btn btn-primary w-100" type="submit">Resend email</button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-outline-secondary w-100" type="submit">Log out</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
