<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Tenant;

final class Locales
{
    public const SUPPORTED = [
        'ca' => 'Català',
        'es' => 'Castellano',
    ];

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && array_key_exists($locale, self::SUPPORTED);
    }

    public static function codes(): array
    {
        return array_keys(self::SUPPORTED);
    }

    public static function rule(): array
    {
        return ['nullable', 'string', 'in:'.implode(',', self::codes())];
    }

    /**
     * Prioritat: idioma triat per l'usuari → idioma del tenant indicat (o el de la URL)
     * → idioma del tenant de l'usuari → APP_LOCALE. Serveix també fora d'una petició
     * (commands, mails enviats des de l'admin).
     */
    public static function resolve(?object $user = null, ?Tenant $tenant = null): string
    {
        $candidates = [
            $user?->locale ?? null,
            ($tenant ?? current_tenant())?->default_locale,
            $user !== null && method_exists($user, 'preferredLocale') ? $user->preferredLocale() : null,
        ];

        foreach ($candidates as $candidate) {
            if (self::isSupported($candidate)) {
                return $candidate;
            }
        }

        return config('app.locale');
    }
}
