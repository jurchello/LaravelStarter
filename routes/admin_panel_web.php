<?php

use App\Http\Controllers\AdminPanel\AdminAbTestAnalyticsController;
use App\Http\Controllers\AdminPanel\AdminAbTestAssignmentsController;
use App\Http\Controllers\AdminPanel\AdminAbTestCreateController;
use App\Http\Controllers\AdminPanel\AdminAbTestEventsController;
use App\Http\Controllers\AdminPanel\AdminAbTestManagementController;
use App\Http\Controllers\AdminPanel\AdminAbTestsController;
use App\Http\Controllers\AdminPanel\AdminDashboardController;
use App\Http\Controllers\AdminPanel\AdminFeatureFlagsController;
use App\Http\Controllers\AdminPanel\AdminMailPreviewRenderController;
use App\Http\Controllers\AdminPanel\AdminMailPreviewsController;
use App\Http\Controllers\AdminPanel\AdminRolesController;
use App\Http\Controllers\AdminPanel\AdminUiKitController;
use App\Http\Controllers\AdminPanel\AdminUsersController;
use App\Infrastructure\Shared\Support\Environment;
use Illuminate\Support\Facades\Route;

Route::redirect('/management/', '/management', 301);

Route::prefix('management')
    ->name('admin.')
    ->middleware(['web', 'ensure_admin'])
    ->group(function (): void {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        Route::prefix('ab-tests')->name('ab-tests.')->group(function (): void {
            Route::get('/', [AdminAbTestsController::class, 'index'])->name('index');
            Route::get('/create', AdminAbTestCreateController::class)->name('create');
            Route::get('/{abTest}', AdminAbTestManagementController::class)->name('show');
            Route::get('/{abTest}/assignments', AdminAbTestAssignmentsController::class)->name('assignments');
            Route::get('/{abTest}/events', AdminAbTestEventsController::class)->name('events');
            Route::get('/{abTest}/analytics', AdminAbTestAnalyticsController::class)->name('analytics');
        });

        Route::prefix('users')->name('users.')->group(function (): void {
            Route::get('/', [AdminUsersController::class, 'index'])->name('index');
        });

        Route::prefix('roles')->name('roles.')->group(function (): void {
            Route::get('/', [AdminRolesController::class, 'index'])->name('index');
        });

        Route::prefix('feature-flags')->name('feature-flags.')->group(function (): void {
            Route::get('/', [AdminFeatureFlagsController::class, 'index'])->name('index');
        });

        Route::get('/ui-kit', [AdminUiKitController::class, 'index'])->name('ui-kit');

        if (Environment::isLocalOrTesting()) {
            Route::prefix('mail-previews')->name('mail-previews.')->group(function (): void {
                Route::get('/', [AdminMailPreviewsController::class, 'index'])->name('index');
                Route::get('/{template}', AdminMailPreviewRenderController::class)->name('show');
            });
        }
    });
