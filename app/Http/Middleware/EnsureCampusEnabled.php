<?php

namespace App\Http\Middleware;

use App\Settings\SettingStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCampusEnabled
{
    public function handle(Request $request, Closure $next): Response
    {
        $store = app(SettingStore::class);

        if ($store->get('campus_enabled', true)) {
            return $next($request);
        }

        // Campus desactivat: els admins del panel poden seguir accedint
        if (auth('web')->check()) {
            return $next($request);
        }

        abort(404);
    }
}
