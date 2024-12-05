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
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            \Log::info(request()->ip() . ' => ' . request()->ip());
            Route::middleware(['api', 'throttle:api'])->prefix('api')->group(base_path('routes/api.php'));

            Route::middleware(['web', 'throttle:web'])->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            $ip = $request->ip();
    
            // Check if the IP is banned
            if (cache()->has("banned:$ip")) {
                \Log::warning("Banned IP attempted access: $ip");
                return Limit::none(); // Deny all requests
            }
    
            // Define rate limiting (30 requests per minute)
            $limit = Limit::perMinute(30)->by($ip);
            $key = "throttle:$ip";
    
            if (RateLimiter::tooManyAttempts($key, 30)) {
                // Ban the IP for 10 minutes
                cache()->put("banned:$ip", true, now()->addMinutes(10));
                \Log::warning("IP banned due to too many requests: $ip");
                return Limit::none(); // Deny all requests
            }
    
            return $limit;
        });
    
        RateLimiter::for('web', function (Request $request) {
            $ip = $request->ip();
    
            // Check if the IP is banned
            if (cache()->has("banned:$ip")) {
                \Log::warning("Banned IP attempted access: $ip");
                return Limit::none(); // Deny all requests
            }
    
            // Define rate limiting (30 requests per minute)
            $limit = Limit::perMinute(30)->by($ip);
            $key = "throttle:$ip";
    
            if (RateLimiter::tooManyAttempts($key, 30)) {
                // Ban the IP for 10 minutes
                cache()->put("banned:$ip", true, now()->addMinutes(10));
                \Log::warning("IP banned due to too many requests: $ip");
                return Limit::none(); // Deny all requests
            }
    
            return $limit;
        });
    }
}
