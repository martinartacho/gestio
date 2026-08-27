<?php

namespace App\Settings;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SettingStore
{
    private const CACHE_KEY = 'site_settings.all';
    private const CACHE_TTL = 3600; // 1 hora

    /** @var array<string,mixed> */
    private array $data = [];

    public function __construct()
    {
        $this->load();
    }

    // ─── Lectura ─────────────────────────────────────────────────────────────

    public function get(string $key, mixed $default = null): mixed
    {
        // super-admin veu tots els mòduls sempre (bypass per a guards de navegació)
        if (str_ends_with($key, '_enabled')
            && auth()->check()
            && auth()->user() instanceof \App\Models\User
            && auth()->user()->hasRole('super-admin')) {
            return true;
        }

        return $this->getRaw($key, $default);
    }

    /** Retorna el valor real de la DB, sense el bypass de super-admin. */
    public function getRaw(string $key, mixed $default = null): mixed
    {
        return array_key_exists($key, $this->data)
            ? $this->data[$key]
            : $default;
    }

    public function all(): array
    {
        return $this->data;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    // ─── Escriptura ───────────────────────────────────────────────────────────

    public function set(string $key, mixed $value): void
    {
        SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
        $this->data[$key] = $value;
        $this->flush();
    }

    /** Desa un array complet de clau→valor en una sola transacció */
    public function setMany(array $settings): void
    {
        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['key' => $key], ['value' => $value]);
            $this->data[$key] = $value;
        }
        $this->flush();
    }

    // ─── Cache ────────────────────────────────────────────────────────────────

    public function flush(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function reload(): void
    {
        $this->flush();
        $this->load();
    }

    // ─── Intern ───────────────────────────────────────────────────────────────

    private function load(): void
    {
        $raw = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return SiteSetting::all()->pluck('value', 'key')->all();
        });

        $this->data = $raw;
    }
}
