<?php

namespace App\Http\Middleware;

use App\Settings\SettingStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAssociatsEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $store = app(SettingStore::class);

        if ($store->get('associats_enabled', false)) {
            return $next($request);
        }

        if (auth('web')->check()) {
            return $next($request);
        }

        abort(404);
    }
}
