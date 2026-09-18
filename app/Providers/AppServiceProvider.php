<?php

namespace App\Providers;

use App\Models\SiteSetting;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
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
<<<<<<< Updated upstream
        SiteSetting::applyToConfig();

=======
>>>>>>> Stashed changes
        View::share('company', config('cebinova'));

        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(8)->by($request->ip());
        });
    }
}
