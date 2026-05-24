<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureStudentEmailIsVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $student = auth('student')->user();

        if ($student && ! $student->hasVerifiedEmail()) {
            return redirect()->route('campus.verification.notice')
                ->with('warning', 'Cal verificar el correu electrònic abans de continuar.');
        }

        return $next($request);
    }
}
