<?php

use App\Http\Controllers\Admin\AnalyticsController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:admin.analytics.view')->prefix('analytics')->name('analytics.')->group(function () {
    Route::get('/', [AnalyticsController::class, 'index'])->name('index');
    Route::get('visitors/{visitor}', [AnalyticsController::class, 'show'])->name('visitors.show');
});
