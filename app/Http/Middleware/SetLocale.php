<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prioritat: idioma triat per l'usuari → idioma per defecte del tenant de la
 * URL → idioma del tenant de l'usuari (API, sense tenant a la URL) → APP_LOCALE.
 * Ha d'anar després de resoldre el tenant.
 */
class SetLocale
{
    private const GUARDS = ['student', 'teacher', 'member', 'web', 'sanctum'];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $this->currentUser();

        $locale = collect([
            $user?->locale,
            current_tenant()?->default_locale,
            $user?->preferredLocale(),
        ])->first(fn (?string $candidate) => Locales::isSupported($candidate));

        if ($locale) {
            app()->setLocale($locale);
        }

        return $next($request);
    }

    private function currentUser(): mixed
    {
        foreach (self::GUARDS as $guard) {
            $user = auth()->guard($guard)->user();
            if ($user && method_exists($user, 'preferredLocale')) {
                return $user;
            }
        }

        return null;
    }
}
