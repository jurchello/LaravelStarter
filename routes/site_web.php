<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\GoogleAuthenticationCallbackController;
use App\Http\Controllers\Auth\GoogleAuthenticationRedirectController;
use App\Http\Controllers\Auth\LeaveImpersonationController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\Health\HealthLiveController;
use App\Http\Controllers\Health\HealthReadyController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health/live', HealthLiveController::class);
Route::get('/health/ready', HealthReadyController::class);

Route::middleware('guest')->group(function (): void {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store'])->name('register.store');

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:auth.login')
        ->name('login.store');
    Route::get('/auth/google/redirect', GoogleAuthenticationRedirectController::class)
        ->middleware('throttle:auth.social')
        ->name('auth.google.redirect');
    Route::get('/auth/google/callback', GoogleAuthenticationCallbackController::class)
        ->middleware('throttle:auth.social')
        ->name('auth.google.callback');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:auth.password-reset')
        ->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])
        ->middleware('throttle:auth.password-update')
        ->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::get('/verify-email', EmailVerificationPromptController::class)->name('verification.notice');
    Route::get('/verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:auth.verification-verify'])
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:auth.verification-send')
        ->name('verification.send');

    Route::view('/dashboard', 'dashboard')
        ->middleware('verified')
        ->name('dashboard');

    Route::post('/impersonation/leave', LeaveImpersonationController::class)
        ->middleware('ensure_impersonating')
        ->name('impersonation.leave');

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
