<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Settings\SettingStore;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

/**
 * Prova de concepte de multi-tenant (BD per tenant, mateix codi). Mira el
 * primer segment de la URL; si coincideix amb un tenant actiu, canvia la
 * connexió per defecte a la seva BD durant la resta de la petició. Si no
 * coincideix amb res, no fa res — el comportament actual de gestio queda
 * intacte (cap risc per a demo.artacho.org).
 *
 * Encara NO està enganxat a cap ruta real, només a una ruta de diagnòstic
 * temporal (/tenant-test/{slug}) per validar el mecanisme.
 */
class ResolveTenant
{
    public function handle(Request $request, Closure $next): Response
    {
        $slug = $request->segment(1);

        $tenant = $slug
            ? Tenant::where('slug', $slug)->where('is_active', true)->first()
            : null;

        if ($tenant) {
            // Perquè route('filament.tenant-admin....') (login, redireccions
            // internes de Filament, etc.) sàpiga omplir el paràmetre {tenant}
            // encara que no se li passi explícitament.
            URL::defaults(['tenant' => $tenant->slug]);

            // Si s'obren /admin i /{tenant}/admin al mateix navegador, la
            // galeta de sessió (mateix nom, mateix path '/') es sobreescriu
            // entre l'una i l'altra i el CSRF deixa de coincidir (419).
            // Cal un NOM de galeta diferent per tenant — no un path diferent:
            // Livewire respon a /livewire/update, fora de qualsevol prefix
            // {tenant}, així que la galeta ha de seguir vàlida a tot '/'.
            config(['session.cookie' => config('session.cookie').'_'.$tenant->slug]);

            $connection = config('database.default');
            config(["database.connections.$connection.database" => $tenant->database]);
            DB::purge($connection);
            DB::reconnect($connection);

            // El driver de cache (p. ex. 'database') es resol un sol cop per
            // petició i es queda enganxat a la connexió que hi havia en aquell
            // moment — sovint abans que aquest middleware s'executi (p. ex.
            // AppServiceProvider::boot() ja fa un Cache::remember). Cal
            // forçar-lo a refer-se contra la connexió (ja canviada) del tenant.
            Cache::forgetDriver(config('cache.default'));

            // SettingStore també és un singleton carregat un sol cop per
            // petició; si ja s'havia carregat abans (amb la BD per defecte),
            // cal recarregar-lo ara que la connexió i el cache ja són correctes.
            app(SettingStore::class)->reload();
        }

        return $next($request);
    }
}
