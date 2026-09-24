<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TeamMemberController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Middleware\EnsureAdminIsActive;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
        Route::post('login', [AuthenticatedSessionController::class, 'store'])
            ->middleware('throttle:admin-login')
            ->name('login.store');
        Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

    Route::middleware(['auth', EnsureAdminIsActive::class])->group(function () {
        Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

        Route::get('/', [DashboardController::class, 'index'])->middleware('can:admin.dashboard')->name('dashboard');

        Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

        Route::middleware('can:admin.leads.view')->group(function () {
            Route::get('leads/export', [LeadController::class, 'export'])->name('leads.export');
            Route::get('leads', [LeadController::class, 'index'])->name('leads.index');
            Route::get('leads/{lead}', [LeadController::class, 'show'])->withTrashed()->name('leads.show');
        });

        Route::middleware('can:admin.leads.manage')->group(function () {
            Route::put('leads/{lead}', [LeadController::class, 'update'])->withTrashed()->name('leads.update');
            Route::post('leads/bulk-status', [LeadController::class, 'bulkStatus'])->name('leads.bulk-status');
            Route::delete('leads/{lead}', [LeadController::class, 'destroy'])->name('leads.destroy');
            Route::post('leads/{id}/restore', [LeadController::class, 'restore'])->name('leads.restore');
        });

        Route::middleware('can:admin.services.manage')->group(function () {
            Route::get('services/create', [ServiceController::class, 'create'])->name('services.create');
            Route::post('services', [ServiceController::class, 'store'])->name('services.store');
            Route::get('services/{service}/edit', [ServiceController::class, 'edit'])->name('services.edit');
            Route::put('services/{service}', [ServiceController::class, 'update'])->name('services.update');
            Route::delete('services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');
        });
        Route::middleware('can:admin.services.view')->group(function () {
            Route::get('services', [ServiceController::class, 'index'])->name('services.index');
            Route::get('services/{service}', [ServiceController::class, 'show'])->name('services.show');
        });

        Route::middleware('can:admin.packages.manage')->group(function () {
            Route::get('packages/create', [PackageController::class, 'create'])->name('packages.create');
            Route::post('packages', [PackageController::class, 'store'])->name('packages.store');
            Route::get('packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
            Route::put('packages/{package}', [PackageController::class, 'update'])->name('packages.update');
            Route::delete('packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');
        });
        Route::middleware('can:admin.packages.view')->group(function () {
            Route::get('packages', [PackageController::class, 'index'])->name('packages.index');
            Route::get('packages/{package}', [PackageController::class, 'show'])->name('packages.show');
        });

        Route::middleware('can:admin.content.manage')->group(function () {
            Route::get('testimonials/create', [TestimonialController::class, 'create'])->name('testimonials.create');
            Route::post('testimonials', [TestimonialController::class, 'store'])->name('testimonials.store');
            Route::get('testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('testimonials.edit');
            Route::put('testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update');
            Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy');

            Route::get('team-members/create', [TeamMemberController::class, 'create'])->name('team-members.create');
            Route::post('team-members', [TeamMemberController::class, 'store'])->name('team-members.store');
            Route::get('team-members/{team_member}/edit', [TeamMemberController::class, 'edit'])->name('team-members.edit');
            Route::put('team-members/{team_member}', [TeamMemberController::class, 'update'])->name('team-members.update');
            Route::delete('team-members/{team_member}', [TeamMemberController::class, 'destroy'])->name('team-members.destroy');

            Route::get('faqs/create', [FaqController::class, 'create'])->name('faqs.create');
            Route::post('faqs', [FaqController::class, 'store'])->name('faqs.store');
            Route::get('faqs/{faq}/edit', [FaqController::class, 'edit'])->name('faqs.edit');
            Route::put('faqs/{faq}', [FaqController::class, 'update'])->name('faqs.update');
            Route::delete('faqs/{faq}', [FaqController::class, 'destroy'])->name('faqs.destroy');

            Route::get('page-sections/create', [PageSectionController::class, 'create'])->name('page-sections.create');
            Route::post('page-sections', [PageSectionController::class, 'store'])->name('page-sections.store');
            Route::get('page-sections/{page_section}/edit', [PageSectionController::class, 'edit'])->name('page-sections.edit');
            Route::put('page-sections/{page_section}', [PageSectionController::class, 'update'])->name('page-sections.update');
            Route::delete('page-sections/{page_section}', [PageSectionController::class, 'destroy'])->name('page-sections.destroy');
        });

        Route::middleware('can:admin.content.view')->group(function () {
            Route::get('testimonials', [TestimonialController::class, 'index'])->name('testimonials.index');
            Route::get('testimonials/{testimonial}', [TestimonialController::class, 'show'])->name('testimonials.show');

            Route::get('team-members', [TeamMemberController::class, 'index'])->name('team-members.index');
            Route::get('team-members/{team_member}', [TeamMemberController::class, 'show'])->name('team-members.show');

            Route::get('faqs', [FaqController::class, 'index'])->name('faqs.index');
            Route::get('faqs/{faq}', [FaqController::class, 'show'])->name('faqs.show');

            Route::get('page-sections', [PageSectionController::class, 'index'])->name('page-sections.index');
            Route::get('page-sections/{page_section}', [PageSectionController::class, 'show'])->name('page-sections.show');
        });

        Route::middleware('can:admin.media.view')->get('media', [MediaController::class, 'index'])->name('media.index');
        Route::middleware('can:admin.media.manage')->group(function () {
            Route::post('media', [MediaController::class, 'store'])->name('media.store');
            Route::delete('media/{medium}', [MediaController::class, 'destroy'])->name('media.destroy');
        });

        Route::middleware('can:admin.settings.view')->get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::middleware('can:admin.settings.manage')->put('settings', [SettingController::class, 'update'])->name('settings.update');

        Route::middleware('can:admin.users.manage')->group(function () {
            Route::get('users/create', [UserController::class, 'create'])->name('users.create');
            Route::post('users', [UserController::class, 'store'])->name('users.store');
            Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
            Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
            Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            Route::post('users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        });
        Route::middleware('can:admin.users.view')->get('users', [UserController::class, 'index'])->name('users.index');

        Route::middleware('can:admin.activity.view')->get('activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');
    });
});
