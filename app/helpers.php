<?php

use App\Models\Tenant;
use App\Settings\SettingStore;
use Filament\Facades\Filament;

if (! function_exists('setting')) {
    /**
     * Obté (o desa) un ajust global del lloc.
     *
     * setting('key')              → retorna el valor (o null)
     * setting('key', 'default')   → retorna el valor o el default
     * setting(['key' => 'valor']) → desa un o més ajustos i retorna null
     */
    function setting(string|array $key, mixed $default = null): mixed
    {
        /** @var SettingStore $store */
        $store = app(SettingStore::class);

        if (is_array($key)) {
            $store->setMany($key);
            return null;
        }

        return $store->get($key, $default);
    }
}

if (! function_exists('current_tenant')) {
    /**
     * Tenant actiu de la petició: dins del panell d'admin ho resol Filament
     * (segons /{tenant} de la URL); al lloc públic ho lliga ResolveWebTenant
     * al contenidor (segons /{tenant} de la URL). Null si cap dels dos
     * s'ha executat (p. ex. comandes de consola).
     */
    function current_tenant(): ?Tenant
    {
        if (Filament::isServing() && ($tenant = Filament::getTenant())) {
            return $tenant;
        }

        return app()->bound(Tenant::class) ? app(Tenant::class) : null;
    }
}
