<?php

namespace App\Providers;

use App\Settings\SettingStore;
use Illuminate\Support\Facades\Config;
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
}
