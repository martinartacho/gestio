<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\Locales;

/**
 * Idioma propi de l'usuari; si no n'ha triat cap, el per defecte del seu
 * tenant. Laravel el fa servir sol per als Mailables/Notifications
 * (HasLocalePreference), també des de commands sense petició.
 */
trait HasPreferredLocale
{
    public function preferredLocale(): ?string
    {
        if (Locales::isSupported($this->locale)) {
            return $this->locale;
        }

        $tenant = method_exists($this, 'tenants')
            ? $this->tenants()->first()
            : $this->tenant;

        return $tenant?->default_locale;
    }
}
