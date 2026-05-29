<?php

namespace App\Http\Controllers\Associats;

use App\Http\Controllers\Controller;
use App\Mail\Associats\MemberPasswordResetMail;
use App\Models\AssociatMember;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class MemberPasswordController extends Controller
{
    public function showForgotPassword(): View
    {
        return view('associats.auth.forgot-password');
    }

    public function sendReset(Request $request): RedirectResponse
    {
        $request->validate([
            'identifier' => ['required', 'string'],
        ]);

        $identifier = trim($request->input('identifier'));

        // Buscar per email o per número de soci
        $member = AssociatMember::where('email', $identifier)
            ->orWhere('member_number', $identifier)
            ->first();

        if (! $member) {
            return back()->withErrors([
                'identifier' => 'No hem trobat cap soci amb aquestes dades.',
            ])->onlyInput('identifier');
        }

        if (! $member->email) {
            return back()->with('info',
                'El vostre compte no té una adreça de correu associada. Contacteu amb la secretaria per recuperar l\'accés.'
            );
        }

        $token = $member->generateResetToken();
        Mail::to($member->email)->send(new MemberPasswordResetMail($member, $token));

        return back()->with('success',
            'Hem enviat un correu a ' . $member->email . ' amb les instruccions per restablir la contrasenya.'
        );
    }

    public function showResetForm(string $token): View|RedirectResponse
    {
        $member = AssociatMember::where('reset_token', $token)->first();

        if (! $member || ! $member->isValidResetToken($token)) {
            return redirect()->route('member.password.request')
                ->withErrors(['identifier' => 'L\'enllaç no és vàlid o ha caducat. Sol·liciteu-ne un de nou.']);
        }

        return view('associats.auth.reset-password', compact('token'));
    }

    public function resetPassword(Request $request, string $token): RedirectResponse
    {
        $member = AssociatMember::where('reset_token', $token)->first();

        if (! $member || ! $member->isValidResetToken($token)) {
            return redirect()->route('member.password.request')
                ->withErrors(['identifier' => 'L\'enllaç no és vàlid o ha caducat.']);
        }

        $request->validate([
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $member->update(['password' => Hash::make($request->input('password'))]);
        $member->clearResetToken();

        return redirect()->route('member.login')
            ->with('success', 'Contrasenya actualitzada correctament. Ja podeu accedir.');
    }
}
