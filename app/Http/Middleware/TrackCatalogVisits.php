<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackCatalogVisits
{
    /** Nombre màxim de visites en la finestra de temps abans d'avisar. */
    private const THRESHOLD = 15;

    /** Finestra de temps en minuts. */
    private const WINDOW_MINUTES = 5;

    public function handle(Request $request, Closure $next): Response
    {
        $ip  = $request->ip();
        $key = "catalog_visits:{$ip}";

        $count = (int) Cache::get($key, 0) + 1;
        Cache::put($key, $count, now()->addMinutes(self::WINDOW_MINUTES));

        // Compartir amb la vista si supera el llindar (només GET, no POST)
        if ($request->isMethod('GET')) {
            view()->share('manyReloads', $count > self::THRESHOLD);
        }

        return $next($request);
    }
}
