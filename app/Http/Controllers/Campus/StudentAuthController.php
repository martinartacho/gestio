<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Mail\Campus\EmailVerificationMail;
use App\Models\CampusStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class StudentAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('campus.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::guard('student')->attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        $student = Auth::guard('student')->user();

        // Bloquejar accés si l'alumne està suspès
        if ($student->isSuspended()) {
            Auth::guard('student')->logout();
            return back()->withErrors([
                'email' => 'El compte ha estat suspès. Contacteu amb l\'administració per a més informació.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('campus.portal.courses'));
    }

    public function showRegister(): View
    {
        return view('campus.auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        // Honeypot: si el camp ocult ve omplert és un bot → falla silenciosa
        if ($request->filled('website')) {
            return redirect()->route('campus.register')
                ->with('success', 'Compte creat correctament. Podeu accedir ara.');
        }

        $data = $request->validate([
            'first_name'   => ['required', 'string', 'max:100'],
            'last_name'    => ['required', 'string', 'max:100'],
            'email'        => ['required', 'email', 'unique:campus_students,email'],
            'password'     => ['required', 'confirmed', Password::min(8)],
            'phone'        => ['nullable', 'string', 'max:20'],
            'data_consent' => ['accepted'],
        ]);

        $data['data_consent'] = true;

        $student = CampusStudent::create($data);

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        // Enviar correu de verificació
        Mail::to($student->email)->send(new EmailVerificationMail($student));

        return redirect()->route('campus.verification.notice')
            ->with('info', 'Hem enviat un correu de verificació a ' . $student->email . '. Comproveu la safata d\'entrada.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('campus.catalog.index');
    }

    // ── Verificació d'email ───────────────────────────────────────────────────

    public function verificationNotice(): View|RedirectResponse
    {
        $student = auth('student')->user();

        if ($student?->hasVerifiedEmail()) {
            return redirect()->route('campus.portal.courses');
        }

        return view('campus.auth.verify-email');
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $student = auth('student')->user();

        if ($student->hasVerifiedEmail()) {
            return redirect()->route('campus.portal.courses');
        }

        $code = strtoupper(trim($request->input('code', '')));

        // Incrementar intents sempre (abans de validar)
        $student->increment('verification_attempts');

        if (! $student->isValidOtp($code)) {
            $attemptsLeft = max(0, 3 - $student->fresh()->verification_attempts);

            if ($attemptsLeft === 0) {
                // Codi exhaurit → regenerar automàticament
                Mail::to($student->email)->send(new EmailVerificationMail($student));
                return back()->withErrors(['code' =>
                    'Massa intents incorrectes. Hem enviat un nou codi al vostre correu.'
                ]);
            }

            return back()->withErrors(['code' =>
                'Codi incorrecte o caducat. Us queden ' . $attemptsLeft . ' ' .
                ($attemptsLeft === 1 ? 'intent' : 'intents') . '.'
            ])->withInput();
        }

        // Codi correcte → verificar
        $student->update([
            'email_verified_at'            => now(),
            'verification_code'            => null,
            'verification_code_expires_at' => null,
            'verification_attempts'        => 0,
        ]);

        return redirect()->route('campus.portal.courses')
            ->with('success', '✓ Correu verificat correctament. Ja pots inscriure\'t als cursos!');
    }

    public function resendVerification(Request $request): RedirectResponse
    {
        $student = auth('student')->user();

        if ($student->hasVerifiedEmail()) {
            return redirect()->route('campus.portal.courses');
        }

        Mail::to($student->email)->send(new EmailVerificationMail($student));

        return back()->with('info', 'Hem enviat un nou codi a ' . $student->email . '. Comproveu la safata d\'entrada.');
    }
}
