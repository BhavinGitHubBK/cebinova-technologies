<?php

use App\Http\Controllers\Admin\BusinessReportController;
use Illuminate\Support\Facades\Route;

Route::middleware('can:admin.reports.view')->group(function () {
    Route::get('reports', [BusinessReportController::class, 'index'])->name('reports.index');
    Route::get('reports/dashboard', [BusinessReportController::class, 'dashboard'])->name('reports.dashboard');
    Route::get('reports/review/{report}', [BusinessReportController::class, 'review'])->name('reports.review');
    Route::get('reports/{type}/export', [BusinessReportController::class, 'export'])->name('reports.export');
    Route::get('reports/{type}', [BusinessReportController::class, 'show'])->name('reports.show');
});
