<?php

namespace App\Providers;

use App\Settings\SettingStore;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SettingStore::class, fn() => new SettingStore());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiters();

        // Aplicar timezone des de la configuració del lloc
        try {
            /** @var SettingStore $settings */
            $settings = $this->app->make(SettingStore::class);
            $tz = $settings->get('timezone');
            if ($tz && in_array($tz, timezone_identifiers_list(), true)) {
                Config::set('app.timezone', $tz);
                date_default_timezone_set($tz);
            }
        } catch (\Throwable) {
            // Ignorar si la taula no existeix encara (p. ex. durant les migracions)
        }
    }

    private function configureRateLimiters(): void
    {
        $isLocal = app()->environment('local');

        // Registre: màx. 5 intents per IP cada 10 min (desactivat en local)
        RateLimiter::for('register', function (Request $request) use ($isLocal) {
            if ($isLocal) return Limit::perMinutes(10, 200)->by($request->ip());
            return Limit::perMinutes(10, 5)->by($request->ip());
        });

        // Login: màx. 10 intents per email+IP cada 10 min (desactivat en local)
        RateLimiter::for('login', function (Request $request) use ($isLocal) {
            if ($isLocal) return Limit::perMinutes(10, 200)->by($request->ip());
            return Limit::perMinutes(10, 10)
                ->by(strtolower((string) $request->input('email')) . '|' . $request->ip());
        });

        // Checkout: màx. 5 per alumne cada 10 min (desactivat en local)
        RateLimiter::for('checkout', function (Request $request) use ($isLocal) {
            if ($isLocal) return Limit::perMinutes(10, 200)->by($request->ip());
            return Limit::perMinutes(10, 5)
                ->by($request->user('student')?->id ?: $request->ip());
        });
    }
}
