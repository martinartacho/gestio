<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Mail\Campus\TeacherPasswordResetMail;
use App\Models\CampusTeacher;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class TeacherAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('campus.teacher.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::guard('teacher')->attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('teacher.portal.courses'));
        }

        return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('teacher')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('campus.catalog.index');
    }

    // ── Recuperació de contrasenya (OTP) ──────────────────────────────────────

    public function showForgotPassword(): View
    {
        return view('campus.teacher.auth.forgot-password');
    }

    public function sendPasswordReset(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:150']]);

        $email   = strtolower(trim($request->input('email')));
        $teacher = CampusTeacher::where('email', $email)->first();

        if (! $teacher) {
            return back()->withErrors(['email' => 'No existeix cap compte amb ' . $email . '.'])->onlyInput('email');
        }

        Mail::to($teacher->email)->send(new TeacherPasswordResetMail($teacher));

        $request->session()->put('teacher_password_reset_email', $email);

        return redirect()->route('teacher.password.code')
            ->with('info', 'Hem enviat un codi de recuperació a ' . $email . '.');
    }

    public function showPasswordResetCode(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('teacher_password_reset_email')) {
            return redirect()->route('teacher.password.request');
        }

        return view('campus.teacher.auth.reset-password');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $email = $request->session()->get('teacher_password_reset_email');

        if (! $email) {
            return redirect()->route('teacher.password.request');
        }

        $request->validate(['code' => ['required', 'string']]);

        $code    = strtoupper(str_replace(' ', '', trim($request->input('code', ''))));
        $teacher = CampusTeacher::where('email', $email)->first();

        if (! $teacher) {
            return back()->withErrors(['code' => 'No s\'ha trobat el compte.']);
        }

        $teacher->increment('verification_attempts');

        if (! $teacher->isValidOtp($code)) {
            $attemptsLeft = max(0, 3 - $teacher->fresh()->verification_attempts);

            if ($attemptsLeft === 0) {
                return back()->withErrors(['code' =>
                    'Massa intents incorrectes. Sol·liciteu un nou codi.'
                ]);
            }

            return back()->withErrors(['code' =>
                'Codi incorrecte o caducat. Us queden ' . $attemptsLeft . ' ' .
                ($attemptsLeft === 1 ? 'intent' : 'intents') . '.'
            ]);
        }

        $teacher->update([
            'verification_code'            => null,
            'verification_code_expires_at' => null,
            'verification_attempts'        => 0,
        ]);

        $request->session()->forget('teacher_password_reset_email');

        Auth::guard('teacher')->login($teacher);
        $request->session()->regenerate();

        return redirect()->route('teacher.portal.courses');
    }
}
