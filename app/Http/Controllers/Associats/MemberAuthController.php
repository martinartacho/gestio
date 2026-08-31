<?php

namespace App\Http\Controllers\Associats;

use App\Http\Controllers\Controller;
use App\Models\AssociatMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $member = AssociatMember::where('email', $credentials['email'])->first();

        if (! $member || ! Hash::check($credentials['password'], $member->password)) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        if ($member->status === 'cancelled') {
            return back()->withErrors([
                'email' => 'El compte de soci ha estat donat de baixa. Contacteu amb l\'entitat.',
            ])->onlyInput('email');
        }

        if (! $member->belongsToTenant(current_tenant()?->id)) {
            $memberships = $member->tenants;

            if ($memberships->isEmpty()) {
                return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
            }

            $request->session()->put('pending_member_id', $member->id);
            return redirect()->route('member.login.select-institution');
        }

        Auth::guard('member')->login($member, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('member.card'));
    }

    /** El soci ha entrat bé però no pertany al tenant de la URL — tria entre les seves institucions. */
    public function selectInstitution(Request $request): View|RedirectResponse
    {
        $member = AssociatMember::find($request->session()->get('pending_member_id'));

        if (! $member) {
            return redirect()->route('member.login');
        }

        return view('campus.auth.select-institution', [
            'tenants'   => $member->tenants,
            'complete'  => 'member.login.complete',
            'backRoute' => 'member.login',
        ]);
    }

    /** Arribada des del selector, ja a la URL del tenant triat — completa el login. */
    public function completeLogin(Request $request): RedirectResponse
    {
        $member = AssociatMember::find($request->session()->get('pending_member_id'));

        if (! $member || ! $member->belongsToTenant(current_tenant()?->id)) {
            return redirect()->route('member.login')
                ->withErrors(['email' => 'No tens accés a aquesta institució.']);
        }

        $request->session()->forget('pending_member_id');
        Auth::guard('member')->login($member);
        $request->session()->regenerate();

        return redirect()->route('member.card');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('member.login');
    }
}
