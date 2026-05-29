<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateMember
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth('member')->check()) {
            return redirect()->route('member.login')
                ->with('info', 'Cal identificar-se per accedir al portal de socis.');
        }

        return $next($request);
    }
}
