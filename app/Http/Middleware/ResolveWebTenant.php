<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Equivalent, per al lloc públic, del que Filament ja fa sol dins de
 * /admin/{tenant}/...: llegeix el primer segment de la URL, busca el
 * tenant actiu i el lliga al contenidor perquè current_tenant() el trobi
 * a qualsevol controlador. BD compartida — no canvia cap connexió, només
 * filtra dades (a diferència de la prova de BD-per-tenant aparcada).
 */
class ResolveWebTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenant::where('slug', $request->route('tenant'))
            ->where('is_active', true)
            ->firstOrFail();

        app()->instance(Tenant::class, $tenant);

        // Perquè route('campus.catalog.index') etc. (a les vistes, sense
        // passar-hi el tenant explícitament) omplin soles el paràmetre.
        URL::defaults(['tenant' => $tenant->slug]);

        // Ja hem consumit el segment {tenant} (current_tenant() el troba via
        // el binding de dalt); si el deixem al paràmetre de ruta, Laravel
        // l'afegeix als arguments posicionals de qualsevol mètode de
        // controlador amb més d'un paràmetre, descol·locant els següents
        // (p. ex. DocumentController::download rebia 'campus' on esperava
        // el $document). Oblidar-lo aquí evita tocar cada controlador.
        $request->route()->forgetParameter('tenant');

        return $next($request);
    }
}
