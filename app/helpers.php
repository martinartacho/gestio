<?php

use App\Settings\SettingStore;

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
