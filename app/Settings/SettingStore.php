<?php

namespace App\Settings;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Cache;

class SettingStore
{
    private const CACHE_TTL = 3600; // 1 hora

    /**
     * Dades carregades, indexades per id de tenant (o 'none' quan no n'hi
     * ha cap conegut, p. ex. /admin/login). Es resol current_tenant() a
     * cada accés en lloc de fer-ho un cop al constructor: aquest servei
     * es crea massa aviat (AppServiceProvider::boot(), abans que cap
     * middleware sàpiga quin és el tenant) per fiar-se'n en aquell moment.
     *
     * @var array<int|string, array<string,mixed>>
     */
    private array $dataByTenant = [];

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
        $data = $this->currentData();

        return array_key_exists($key, $data) ? $data[$key] : $default;
    }

    public function all(): array
    {
        return $this->currentData();
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->currentData());
    }

    // ─── Escriptura ───────────────────────────────────────────────────────────

    public function set(string $key, mixed $value): void
    {
        $tenantId = $this->tenantId();

        SiteSetting::updateOrCreate(['tenant_id' => $tenantId, 'key' => $key], ['value' => $value]);
        $this->dataByTenant[$this->memoKey($tenantId)][$key] = $value;
        $this->flush();
    }

    /** Desa un array complet de clau→valor en una sola transacció */
    public function setMany(array $settings): void
    {
        $tenantId = $this->tenantId();

        foreach ($settings as $key => $value) {
            SiteSetting::updateOrCreate(['tenant_id' => $tenantId, 'key' => $key], ['value' => $value]);
            $this->dataByTenant[$this->memoKey($tenantId)][$key] = $value;
        }
        $this->flush();
    }

    // ─── Cache ────────────────────────────────────────────────────────────────

    public function flush(): void
    {
        $tenantId = $this->tenantId();
        unset($this->dataByTenant[$this->memoKey($tenantId)]);
        Cache::forget($this->cacheKey($tenantId));
    }

    public function reload(): void
    {
        $this->flush();
        $this->currentData();
    }

    // ─── Intern ───────────────────────────────────────────────────────────────

    private function tenantId(): ?int
    {
        return current_tenant()?->id;
    }

    private function memoKey(?int $tenantId): string
    {
        return $tenantId === null ? 'none' : (string) $tenantId;
    }

    private function cacheKey(?int $tenantId): string
    {
        return 'site_settings.all.' . $this->memoKey($tenantId);
    }

    private function currentData(): array
    {
        $tenantId = $this->tenantId();
        $memoKey  = $this->memoKey($tenantId);

        if (! array_key_exists($memoKey, $this->dataByTenant)) {
            // Sense tenant conegut (p. ex. /admin/login): res a carregar —
            // cada crida a setting($key, $default) ja porta el seu propi
            // valor per defecte al codi.
            $this->dataByTenant[$memoKey] = $tenantId === null
                ? []
                : Cache::remember($this->cacheKey($tenantId), self::CACHE_TTL, function () use ($tenantId) {
                    return SiteSetting::where('tenant_id', $tenantId)->pluck('value', 'key')->all();
                });
        }

        return $this->dataByTenant[$memoKey];
    }
}
