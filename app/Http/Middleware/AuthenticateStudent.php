<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateStudent
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth('student')->check()) {
            return redirect()->route('campus.login')
                ->with('info', 'Cal identificar-se per accedir al portal.');
        }

        return $next($request);
    }
}
