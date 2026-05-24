<?php

namespace App\Http\Middleware;

use App\Models\BlockedIp;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousIp
{
    public function handle(Request $request, Closure $next): Response
    {
        // Saltar IPs locals i webhook de Stripe
        if (in_array($request->ip(), ['127.0.0.1', '::1'], true)
            || $request->routeIs('stripe.webhook')) {
            return $next($request);
        }

        if (BlockedIp::isBlocked($request->ip())) {
            abort(403, 'Accés denegat.');
        }

        return $next($request);
    }
}
