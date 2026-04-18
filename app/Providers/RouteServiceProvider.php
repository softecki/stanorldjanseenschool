<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/app/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     *
     * @return void
     */
    public function boot()
    {
        
        $this->configureRateLimiting();

        $this->routes(function () {

            $this->mapApiRoutes();
            $this->mapWebRoutes();

        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
    }

    protected function mapWebRoutes()
    {
        // Load web routes once to avoid duplicate route names when serializing for optimize (multiple central domains)
        $domain = $this->centralDomains()[0] ?? null;
        if ($domain) {
            Route::middleware('web')
                ->domain($domain)
                ->namespace($this->namespace)
                ->group(base_path('Modules/MainApp/Routes/web.php'));
        } else {
            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('Modules/MainApp/Routes/web.php'));
        }
    }

    protected function mapApiRoutes()
    {
        // Load api routes once to avoid duplicate route names (e.g. ussd.handle) when multiple central domains exist
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    protected function centralDomains(): array
    {
        return config('tenancy.central_domains');
    }
}
