<?php

namespace App\Providers;

use App\Models\SiteSetting;
use App\Policies\AdminAccessPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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

        View::share('company', config('cebinova'));

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });

        RateLimiter::for('admin-login', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip().'|'.$request->input('email'));
        });

        $policy = app(AdminAccessPolicy::class);
        Gate::define('admin.dashboard', [$policy, 'viewDashboard']);
        Gate::define('admin.leads.view', [$policy, 'viewLeads']);
        Gate::define('admin.leads.manage', [$policy, 'manageLeads']);
        Gate::define('admin.services.view', [$policy, 'viewServices']);
        Gate::define('admin.services.manage', [$policy, 'manageServices']);
        Gate::define('admin.packages.view', [$policy, 'viewPackages']);
        Gate::define('admin.packages.manage', [$policy, 'managePackages']);
        Gate::define('admin.media.view', [$policy, 'viewMedia']);
        Gate::define('admin.media.manage', [$policy, 'manageMedia']);
        Gate::define('admin.settings.view', [$policy, 'viewSettings']);
        Gate::define('admin.settings.manage', [$policy, 'manageSettings']);
        Gate::define('admin.users.view', [$policy, 'viewUsers']);
        Gate::define('admin.users.manage', [$policy, 'manageUsers']);
        Gate::define('admin.activity.view', [$policy, 'viewActivityLogs']);
        Gate::define('admin.content.view', [$policy, 'viewContent']);
        Gate::define('admin.content.manage', [$policy, 'manageContent']);
    }
}
