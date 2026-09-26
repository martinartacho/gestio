<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** Aplica Locales::resolve() a la petició. Ha d'anar després de resoldre el tenant. */
class SetLocale
{
    private const GUARDS = ['student', 'teacher', 'member', 'web', 'sanctum'];

    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale(Locales::resolve($this->currentUser()));

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
