<?php

use App\Http\Controllers\AdminPanel\AdminAbTestInsightsApiController;
use App\Http\Controllers\AdminPanel\AdminAbTestManagementApiController;
use App\Http\Controllers\AdminPanel\AdminAbTestSuggestionsApiController;
use App\Http\Controllers\AdminPanel\AdminAbTestVariantApiController;
use App\Http\Controllers\AdminPanel\AdminAbTestsApiController;
use App\Http\Controllers\AdminPanel\AdminDashboardApiController;
use App\Http\Controllers\AdminPanel\AdminFeatureFlagSuggestionsApiController;
use App\Http\Controllers\AdminPanel\AdminFeatureFlagsApiController;
use App\Http\Controllers\AdminPanel\AdminRoleSuggestionsApiController;
use App\Http\Controllers\AdminPanel\AdminRolesApiController;
use App\Http\Controllers\AdminPanel\AdminUserImpersonationApiController;
use App\Http\Controllers\AdminPanel\AdminUserSuggestionsApiController;
use App\Http\Controllers\AdminPanel\AdminUsersApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('management/api')
    ->name('admin.api.')
    ->middleware(['web', 'ensure_admin'])
    ->group(function (): void {
        Route::get('/ab-tests', AdminAbTestsApiController::class)->name('ab-tests.index');
        Route::get('/ab-tests/audience-estimate', [AdminAbTestInsightsApiController::class, 'audienceEstimate'])->name('ab-tests.audience-estimate');
        Route::get('/ab-tests/suggestions', AdminAbTestSuggestionsApiController::class)->middleware('throttle:admin.search')->name('ab-tests.suggestions');
        Route::get('/ab-tests/{abTest}', [AdminAbTestManagementApiController::class, 'show'])->name('ab-tests.show');
        Route::get('/ab-tests/{abTest}/assignments', [AdminAbTestInsightsApiController::class, 'assignments'])->name('ab-tests.assignments');
        Route::get('/ab-tests/{abTest}/events', [AdminAbTestInsightsApiController::class, 'events'])->name('ab-tests.events');
        Route::get('/ab-tests/{abTest}/analytics', [AdminAbTestInsightsApiController::class, 'analytics'])->name('ab-tests.analytics');
        Route::post('/ab-tests', [AdminAbTestManagementApiController::class, 'store'])->middleware('throttle:admin.mutation')->name('ab-tests.store');
        Route::put('/ab-tests/{abTest}', [AdminAbTestManagementApiController::class, 'update'])->middleware('throttle:admin.mutation')->name('ab-tests.update');
        Route::delete('/ab-tests/{abTest}', [AdminAbTestManagementApiController::class, 'destroy'])->middleware('throttle:admin.mutation')->name('ab-tests.destroy');
        Route::patch('/ab-tests/{abTest}/status', [AdminAbTestManagementApiController::class, 'updateStatus'])->middleware('throttle:admin.mutation')->name('ab-tests.status');
        Route::post('/ab-tests/{abTest}/variants', [AdminAbTestVariantApiController::class, 'store'])->middleware('throttle:admin.mutation')->name('ab-tests.variants.store');
        Route::put('/ab-tests/{abTest}/variants/{variant}', [AdminAbTestVariantApiController::class, 'update'])->middleware('throttle:admin.mutation')->name('ab-tests.variants.update');
        Route::delete('/ab-tests/{abTest}/variants/{variant}', [AdminAbTestVariantApiController::class, 'destroy'])->middleware('throttle:admin.mutation')->name('ab-tests.variants.destroy');
        Route::get('/dashboard', AdminDashboardApiController::class)->name('dashboard');
        Route::get('/feature-flags', AdminFeatureFlagsApiController::class)->name('feature-flags.index');
        Route::get('/feature-flags/suggestions', AdminFeatureFlagSuggestionsApiController::class)->middleware('throttle:admin.search')->name('feature-flags.suggestions');
        Route::post('/feature-flags', [AdminFeatureFlagsApiController::class, 'store'])->middleware('throttle:admin.mutation')->name('feature-flags.store');
        Route::put('/feature-flags/{featureFlag}', [AdminFeatureFlagsApiController::class, 'update'])->middleware('throttle:admin.mutation')->name('feature-flags.update');
        Route::delete('/feature-flags/{featureFlag}', [AdminFeatureFlagsApiController::class, 'destroy'])->middleware('throttle:admin.mutation')->name('feature-flags.destroy');
        Route::get('/users', AdminUsersApiController::class)->name('users.index');
        Route::get('/users/suggestions', AdminUserSuggestionsApiController::class)->middleware('throttle:admin.search')->name('users.suggestions');
        Route::post('/users/{user}/impersonation', AdminUserImpersonationApiController::class)->middleware('throttle:admin.impersonation')->name('users.impersonate');
        Route::patch('/users/{user}/roles', [AdminUsersApiController::class, 'updateRoles'])->middleware('throttle:admin.mutation')->name('users.roles.update');
        Route::get('/roles', AdminRolesApiController::class)->name('roles.index');
        Route::get('/roles/suggestions', AdminRoleSuggestionsApiController::class)->middleware('throttle:admin.search')->name('roles.suggestions');
        Route::post('/roles', [AdminRolesApiController::class, 'store'])->middleware('throttle:admin.mutation')->name('roles.store');
        Route::put('/roles/{role}', [AdminRolesApiController::class, 'update'])->middleware('throttle:admin.mutation')->name('roles.update');
        Route::patch('/roles/{role}/permissions', [AdminRolesApiController::class, 'updatePermissions'])->middleware('throttle:admin.mutation')->name('roles.permissions.update');
        Route::delete('/roles/{role}', [AdminRolesApiController::class, 'destroy'])->middleware('throttle:admin.mutation')->name('roles.destroy');
    });
