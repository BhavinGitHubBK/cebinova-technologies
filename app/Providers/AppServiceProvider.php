<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Policies\AdminAccessPolicy;
use App\Services\Admin\ActivityLogger;
use App\Services\Admin\AdminNotifications;
use App\Support\NavigationMenu;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        SiteSetting::applyToConfig();

        Event::listen('eloquent.updating: *', function (string $event, array $payload) {
            $model = $payload[0] ?? null;
            if ($model instanceof Model) {
                ActivityLogger::remember($model);
            }
        });

        View::share('company', config('cebinova'));

        View::composer('layouts.admin', function ($view) {
            $view->with('adminNotifications', app(AdminNotifications::class)->current());
        });

        View::composer('components.navbar', function ($view) {
            $view->with('navServices', NavigationMenu::services())
                ->with('navOffers', NavigationMenu::offers());
        });

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.$request->input('email'));
        });

        RateLimiter::for('analytics', function (Request $request) {
            return Limit::perMinute((int) config('analytics.rate_per_minute'))->by($request->ip());
        });

        RateLimiter::for('promotions', function (Request $request) {
            return Limit::perMinute(20)->by($request->ip());
        });

        $policy = app(AdminAccessPolicy::class);
        Gate::define('admin.dashboard', [$policy, 'viewDashboard']);
        Gate::define('admin.leads.view', [$policy, 'viewLeads']);
        Gate::define('admin.leads.manage', [$policy, 'manageLeads']);
        Gate::define('admin.services.view', [$policy, 'viewServices']);
        Gate::define('admin.services.manage', [$policy, 'manageServices']);
        Gate::define('admin.solutions.view', [$policy, 'viewSolutions']);
        Gate::define('admin.solutions.manage', [$policy, 'manageSolutions']);
        Gate::define('admin.industries.view', [$policy, 'viewIndustries']);
        Gate::define('admin.industries.manage', [$policy, 'manageIndustries']);
        Gate::define('admin.packages.view', [$policy, 'viewPackages']);
        Gate::define('admin.packages.manage', [$policy, 'managePackages']);
        Gate::define('admin.settings.view', [$policy, 'viewSettings']);
        Gate::define('admin.settings.manage', [$policy, 'manageSettings']);
        Gate::define('admin.cache.manage', [$policy, 'manageCache']);
        Gate::define('admin.users.view', [$policy, 'viewUsers']);
        Gate::define('admin.users.manage', [$policy, 'manageUsers']);
        Gate::define('admin.activity.view', [$policy, 'viewActivityLogs']);
        Gate::define('admin.content.view', [$policy, 'viewContent']);
        Gate::define('admin.content.manage', [$policy, 'manageContent']);
        Gate::define('admin.promotions.view', [$policy, 'viewPromotions']);
        Gate::define('admin.promotions.manage', [$policy, 'managePromotions']);
        Gate::define('admin.promotions.publish', [$policy, 'publishPromotions']);
        Gate::define('admin.reports.view', fn () => true);
        Gate::define('admin.analytics.view', fn () => true);
    }
}
