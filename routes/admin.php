<?php

use App\Http\Controllers\Admin\ActivityLogController;
use App\Http\Controllers\Admin\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Admin\Auth\NewPasswordController;
use App\Http\Controllers\Admin\Auth\PasswordResetLinkController;
use App\Http\Controllers\Admin\CacheController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\IndustryController;
use App\Http\Controllers\Admin\LeadController;
use App\Http\Controllers\Admin\LiveChatController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\PackageController;
use App\Http\Controllers\Admin\PageSectionController;
use App\Http\Controllers\Admin\PricingOptionController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PromotionCampaignController;
use App\Http\Controllers\Admin\PromotionCouponController;
use App\Http\Controllers\Admin\PromotionReportController;
use App\Http\Controllers\Admin\ProofController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\SolutionController;
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
        Route::post('notifications/seen', [NotificationController::class, 'seen'])->name('notifications.seen');

        Route::get('/', [DashboardController::class, 'index'])->middleware('can:admin.dashboard')->name('dashboard');
        Route::get('dashboard/sections/{section}', [DashboardController::class, 'section'])->middleware('can:admin.dashboard')->name('dashboard.section');

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

        Route::middleware('can:admin.solutions.manage')->group(function () {
            Route::get('solutions/create', [SolutionController::class, 'create'])->name('solutions.create');
            Route::post('solutions', [SolutionController::class, 'store'])->name('solutions.store');
            Route::get('solutions/{solution}/edit', [SolutionController::class, 'edit'])->name('solutions.edit');
            Route::put('solutions/{solution}', [SolutionController::class, 'update'])->name('solutions.update');
            Route::delete('solutions/{solution}', [SolutionController::class, 'destroy'])->name('solutions.destroy');
        });
        Route::middleware('can:admin.solutions.view')->group(function () {
            Route::get('solutions', [SolutionController::class, 'index'])->name('solutions.index');
            Route::get('solutions/{solution}', [SolutionController::class, 'show'])->name('solutions.show');
        });

        Route::middleware('can:admin.industries.manage')->group(function () {
            Route::get('industries/create', [IndustryController::class, 'create'])->name('industries.create');
            Route::post('industries', [IndustryController::class, 'store'])->name('industries.store');
            Route::get('industries/{industry}/edit', [IndustryController::class, 'edit'])->name('industries.edit');
            Route::put('industries/{industry}', [IndustryController::class, 'update'])->name('industries.update');
            Route::delete('industries/{industry}', [IndustryController::class, 'destroy'])->name('industries.destroy');
        });
        Route::middleware('can:admin.industries.view')->group(function () {
            Route::get('industries', [IndustryController::class, 'index'])->name('industries.index');
            Route::get('industries/{industry}', [IndustryController::class, 'show'])->name('industries.show');
        });

        Route::middleware('can:admin.promotions.view')->group(function () {
            Route::get('promotions', [PromotionCampaignController::class, 'index'])->name('promotions.campaigns.index');
            Route::get('promotions/coupons', [PromotionCouponController::class, 'index'])->name('promotions.coupons.index');
            Route::get('promotions/usage', [PromotionReportController::class, 'usage'])->name('promotions.usage');
            Route::get('promotions/analytics', [PromotionReportController::class, 'analytics'])->name('promotions.analytics');
            Route::get('promotions/{campaign}/preview', [PromotionCampaignController::class, 'preview'])->whereNumber('campaign')->name('promotions.campaigns.preview');
            Route::get('promotions/{campaign}', [PromotionCampaignController::class, 'edit'])->whereNumber('campaign')->name('promotions.campaigns.edit');
            Route::get('promotions/coupons/{coupon}/edit', [PromotionCouponController::class, 'edit'])->name('promotions.coupons.edit');
        });
        Route::middleware('can:admin.promotions.manage')->group(function () {
            Route::get('promotions/create', [PromotionCampaignController::class, 'create'])->name('promotions.campaigns.create');
            Route::post('promotions', [PromotionCampaignController::class, 'store'])->name('promotions.campaigns.store');
            Route::put('promotions/{campaign}', [PromotionCampaignController::class, 'update'])->whereNumber('campaign')->name('promotions.campaigns.update');
            Route::delete('promotions/{campaign}', [PromotionCampaignController::class, 'destroy'])->whereNumber('campaign')->name('promotions.campaigns.destroy');
            Route::post('promotions/{campaign}/duplicate', [PromotionCampaignController::class, 'duplicate'])->whereNumber('campaign')->name('promotions.campaigns.duplicate');
            Route::get('promotions/coupons/create', [PromotionCouponController::class, 'create'])->name('promotions.coupons.create');
            Route::post('promotions/coupons', [PromotionCouponController::class, 'store'])->name('promotions.coupons.store');
            Route::put('promotions/coupons/{coupon}', [PromotionCouponController::class, 'update'])->name('promotions.coupons.update');
            Route::delete('promotions/coupons/{coupon}', [PromotionCouponController::class, 'destroy'])->name('promotions.coupons.destroy');
        });
        Route::middleware('can:admin.promotions.publish')->group(function () {
            Route::post('promotions/{campaign}/publish', [PromotionCampaignController::class, 'publish'])->whereNumber('campaign')->name('promotions.campaigns.publish');
            Route::post('promotions/{campaign}/disable', [PromotionCampaignController::class, 'disable'])->whereNumber('campaign')->name('promotions.campaigns.disable');
        });

        Route::middleware('can:admin.packages.manage')->group(function () {
            Route::get('packages/create', [PackageController::class, 'create'])->name('packages.create');
            Route::post('packages', [PackageController::class, 'store'])->name('packages.store');
            Route::get('packages/{package}/edit', [PackageController::class, 'edit'])->name('packages.edit');
            Route::put('packages/{package}', [PackageController::class, 'update'])->name('packages.update');
            Route::delete('packages/{package}', [PackageController::class, 'destroy'])->name('packages.destroy');
            Route::get('pricing-options/create', [PricingOptionController::class, 'create'])->name('pricing-options.create');
            Route::post('pricing-options', [PricingOptionController::class, 'store'])->name('pricing-options.store');
            Route::get('pricing-options/{pricingOption}/edit', [PricingOptionController::class, 'edit'])->name('pricing-options.edit');
            Route::put('pricing-options/{pricingOption}', [PricingOptionController::class, 'update'])->name('pricing-options.update');
            Route::delete('pricing-options/{pricingOption}', [PricingOptionController::class, 'destroy'])->name('pricing-options.destroy');
        });
        Route::middleware('can:admin.packages.view')->group(function () {
            Route::get('packages', [PackageController::class, 'index'])->name('packages.index');
            Route::get('packages/{package}', [PackageController::class, 'show'])->name('packages.show');
            Route::get('pricing-options', [PricingOptionController::class, 'index'])->name('pricing-options.index');
        });

        Route::middleware('can:admin.content.manage')->group(function () {
            Route::get('page-sections/create', [PageSectionController::class, 'create'])->name('page-sections.create');
            Route::post('page-sections', [PageSectionController::class, 'store'])->name('page-sections.store');
            Route::get('page-sections/{page_section}/edit', [PageSectionController::class, 'edit'])->name('page-sections.edit');
            Route::put('page-sections/{page_section}', [PageSectionController::class, 'update'])->name('page-sections.update');
            Route::delete('page-sections/{page_section}', [PageSectionController::class, 'destroy'])->name('page-sections.destroy');
        });

        Route::middleware('can:admin.content.view')->group(function () {
            Route::get('page-sections', [PageSectionController::class, 'index'])->name('page-sections.index');
            Route::get('page-sections/{page_section}', [PageSectionController::class, 'show'])->name('page-sections.show');
        });

        foreach (['demos'] as $kind) {
            Route::middleware('can:admin.content.view')->get($kind, [ProofController::class, 'index'])
                ->defaults('kind', $kind)->name($kind.'.index');
            Route::middleware('can:admin.content.manage')->group(function () use ($kind) {
                Route::get($kind.'/create', [ProofController::class, 'create'])->defaults('kind', $kind)->name($kind.'.create');
                Route::post($kind, [ProofController::class, 'store'])->defaults('kind', $kind)->name($kind.'.store');
                Route::get($kind.'/{record}/edit', [ProofController::class, 'edit'])->whereNumber('record')->defaults('kind', $kind)->name($kind.'.edit');
                Route::put($kind.'/{record}', [ProofController::class, 'update'])->whereNumber('record')->defaults('kind', $kind)->name($kind.'.update');
                Route::delete($kind.'/{record}', [ProofController::class, 'destroy'])->whereNumber('record')->defaults('kind', $kind)->name($kind.'.destroy');
            });
        }

        Route::middleware('can:admin.settings.view')->get('settings', [SettingController::class, 'edit'])->name('settings.edit');
        Route::middleware('can:admin.settings.manage')->put('settings', [SettingController::class, 'update'])->name('settings.update');
        Route::middleware('can:admin.cache.manage')->group(function () {
            Route::get('cache', [CacheController::class, 'index'])->name('cache.index');
            Route::post('cache/application/clear', [CacheController::class, 'clearApplication'])->name('cache.application.clear');
            Route::post('cache/config/clear', [CacheController::class, 'clearConfig'])->name('cache.config.clear');
            Route::post('cache/config/rebuild', [CacheController::class, 'rebuildConfig'])->name('cache.config.rebuild');
            Route::post('cache/routes/clear', [CacheController::class, 'clearRoutes'])->name('cache.routes.clear');
            Route::post('cache/routes/rebuild', [CacheController::class, 'rebuildRoutes'])->name('cache.routes.rebuild');
            Route::post('cache/views/clear', [CacheController::class, 'clearViews'])->name('cache.views.clear');
            Route::post('cache/views/rebuild', [CacheController::class, 'rebuildViews'])->name('cache.views.rebuild');
            Route::post('cache/events/clear', [CacheController::class, 'clearEvents'])->name('cache.events.clear');
            Route::post('cache/events/rebuild', [CacheController::class, 'rebuildEvents'])->name('cache.events.rebuild');
            Route::post('cache/compiled/clear', [CacheController::class, 'clearCompiled'])->name('cache.compiled.clear');
            Route::post('cache/all/clear', [CacheController::class, 'clearAll'])->name('cache.all.clear');
        });

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

        require __DIR__.'/reports.php';
        require __DIR__.'/analytics.php';
        Route::middleware('can:admin.analytics.view')->get('customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::middleware('can:admin.leads.view')->get('live-chats', [LiveChatController::class, 'index'])->name('live-chats.index');
        Route::middleware('can:admin.leads.view')->get('live-chats/{liveChat}', [LiveChatController::class, 'show'])->name('live-chats.show');
    });
});
