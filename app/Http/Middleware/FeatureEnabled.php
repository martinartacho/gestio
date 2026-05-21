<?php

namespace App\Http\Middleware;

use App\Settings\SettingStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FeatureEnabled
{
    /**
     * Comprova que un feature flag estigui actiu.
     *
     * Ús a les rutes:
     *   ->middleware('feature:documents_enabled')
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string $flag): Response
    {
        /** @var SettingStore $store */
        $store = app(SettingStore::class);

        // Valor per defecte: true (si no existeix la clau, el mòdul és actiu)
        if (! $store->get($flag, true)) {
            abort(404);
        }

        return $next($request);
    }
}
