<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthenticateTeacher
{
    public function handle(Request $request, Closure $next)
    {
        if (! auth('teacher')->check()) {
            return redirect()->route('teacher.login')
                ->with('info', 'Cal identificar-se per accedir al portal de professorat.');
        }

        return $next($request);
    }
}
