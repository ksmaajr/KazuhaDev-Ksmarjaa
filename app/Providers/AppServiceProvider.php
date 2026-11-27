<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('member-login', function (Request $request): array {
            $key = $request->input('key');

            return [Limit::perMinute(10)->by($request->ip()), Limit::perMinute(5)->by(hash('sha256', is_string($key) ? $key : ''))];
        });
        RateLimiter::for('member-reset', fn (Request $request) => Limit::perMinute(1)->by((string) $request->session()->get('member_license_id')));

        RateLimiter::for('admin-login', function (Request $request): array {
            $email = $request->input('email');
            $fingerprint = hash('sha256', is_string($email) ? strtolower(trim($email)) : '');

            return [Limit::perMinute(5)->by($fingerprint.'|'.$request->ip()), Limit::perMinute(10)->by('account:'.$fingerprint), Limit::perMinute(20)->by($request->ip())];
        });
        RateLimiter::for('license-api', function (Request $request): array {
            $key = $request->bearerToken() ?? $request->input('key');

            return [Limit::perMinute(120)->by($request->ip()), Limit::perMinute(30)->by(hash('sha256', is_string($key) ? $key : ''))];
        });
        if ($this->app->isProduction()) {
            config(['app.debug' => false, 'session.secure' => true, 'session.http_only' => true, 'session.encrypt' => true]);
            URL::forceScheme('https');
        }
    }
}
