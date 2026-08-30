<?php

use Illuminate\Support\Facades\Route;

// Comportament actual — exactament les mateixes rutes d'abans, sense cap
// canvi de comportament. Si mai cal desfer el bloc de sota, aquesta línia
// per si sola ja deixa l'app funcionant tal com estava.
require __DIR__.'/web-content.php';

// ── PROVA multi-tenant (2026-08-30) ────────────────────────────────────────
// Les mateixes rutes de dalt, però sota /{tenant}/... El canvi de BD el fa
// ResolveTenant, ara registrat globalment al grup 'web' (bootstrap/app.php)
// perquè s'executi abans que s'obri la sessió — aquí només calen el prefix
// i el prefix de noms de ruta. Additiu i reversible: per desactivar-ho,
// n'hi ha prou d'esborrar aquest bloc — el require de dalt queda intacte.
Route::prefix('{tenant}')
    ->name('tenant.')
    ->group(__DIR__.'/web-content.php');

// TEMPORAL — prova de concepte multi-tenant, esborrar quan es decideixi
// com integrar-ho a les rutes reals (veure pla a la memòria del projecte).
Route::get('/{slug}/tenant-test', function (string $slug) {
    $marker = null;
    try {
        $marker = \Illuminate\Support\Facades\DB::table('marker')->first();
    } catch (\Throwable $e) {
        // No existeix aquesta taula a la BD actual — esperat si el slug
        // no coincideix amb cap tenant (es queda a la BD per defecte).
    }

    return response()->json([
        'slug_rebut'    => $slug,
        'bd_connectada' => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
        'marker'        => $marker,
    ]);
});
