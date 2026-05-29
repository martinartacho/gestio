<?php

namespace App\Http\Controllers\Associats;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MemberAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('associats.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::guard('member')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $member = Auth::guard('member')->user();

        if ($member->status === 'cancelled') {
            Auth::guard('member')->logout();
            return back()->withErrors([
                'email' => 'El compte de soci ha estat donat de baixa. Contacteu amb l\'entitat.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('member.card'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.login');
    }
}
