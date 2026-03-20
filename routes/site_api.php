<?php

use App\Http\Controllers\AbTesting\AbTestingController;
use App\Http\Controllers\I18nController;
use Illuminate\Support\Facades\Route;

Route::get('/i18n', [I18nController::class, 'index']);

Route::prefix('ab')->group(function (): void {
    Route::get('/assign/{test}', [AbTestingController::class, 'assign'])->middleware('throttle:site.ab-assign');
    Route::post('/event', [AbTestingController::class, 'event'])->middleware('throttle:site.ab-event');
});
