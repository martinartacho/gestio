<?php

namespace App\Http\Middleware;

use App\Models\CampusQueueEntry;
use App\Settings\SettingStore;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureQueueAccess
{
    public function handle(Request $request, Closure $next): Response
    {
        $settings = app(SettingStore::class);

        if (! $settings->get('queue_enabled', false)) {
            return $next($request);
        }

        $queueStart = $settings->get('queue_start_at');
        if (! $queueStart || now()->lt($queueStart)) {
            // Cua configura però encara no ha obert
            return $next($request);
        }

        // Comprovar si la sessió té accés vàlid
        $token = $request->session()->get('queue_access_token');

        if ($token) {
            $entry = CampusQueueEntry::where('access_code', $token)
                ->where('status', CampusQueueEntry::STATUS_ACCESSED)
                ->where('access_expires_at', '>', now())
                ->first();

            if ($entry) {
                return $next($request);
            }
        }

        // Sense token vàlid → redirigir a la cua
        return redirect()->route('campus.queue.join')
            ->with('info', 'Cal registrar-se a la cua d\'inscripcions per accedir al catàleg.');
    }
}
