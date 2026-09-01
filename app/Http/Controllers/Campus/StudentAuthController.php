<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Mail\Campus\EmailVerificationMail;
use App\Mail\Campus\PasswordResetMail;
use App\Models\CampusQueueEntry;
use App\Models\CampusStudent;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        // Login per email/contrasenya, com abans — el compte és de la
        // persona, no d'un tenant concret (pot pertànyer a més d'un).
        $student = CampusStudent::where('email', $credentials['email'])->first();

        if (! $student || ! Hash::check($credentials['password'], $student->password)) {
            return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
        }

        if ($student->isSuspended()) {
            return back()->withErrors([
                'email' => 'El compte ha estat suspès. Contacteu amb l\'administració per a més informació.',
            ])->onlyInput('email');
        }

        // No pertany al tenant de la URL actual: si en té d'altres, que triï.
        if (! $student->belongsToTenant(current_tenant()?->id)) {
            $memberships = $student->tenants;

            if ($memberships->isEmpty()) {
                return back()->withErrors(['email' => __('auth.failed')])->onlyInput('email');
            }

            $request->session()->put('pending_student_id', $student->id);
            return redirect()->route('campus.login.select-institution');
        }

        Auth::guard('student')->login($student, $request->boolean('remember'));
        $request->session()->regenerate();

        return redirect()->intended(route('campus.portal.courses'));
    }

    /** L'alumne ha entrat bé però no pertany al tenant de la URL — tria entre les seves institucions. */
    public function selectInstitution(Request $request): View|RedirectResponse
    {
        $student = CampusStudent::find($request->session()->get('pending_student_id'));

        if (! $student) {
            return redirect()->route('campus.login');
        }

        return view('campus.auth.select-institution', ['tenants' => $student->tenants]);
    }

    /** Arribada des del selector, ja a la URL del tenant triat — completa el login. */
    public function completeLogin(Request $request): RedirectResponse
    {
        $student = CampusStudent::find($request->session()->get('pending_student_id'));

        if (! $student || ! $student->belongsToTenant(current_tenant()?->id)) {
            return redirect()->route('campus.login')
                ->withErrors(['email' => 'No tens accés a aquesta institució.']);
        }

        $request->session()->forget('pending_student_id');
        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        return redirect()->route('campus.portal.courses');
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
            // Únic globalment: la persona (no el compte) és d'una institució
            // o altra; si l'email ja existeix, cal iniciar sessió, no registrar-se de nou.
            'email'        => ['required', 'email', 'unique:campus_students,email'],
            'password'     => ['required', 'confirmed', Password::min(8)],
            'phone'        => ['nullable', 'string', 'max:20'],
            'data_consent' => ['accepted'],
        ]);

        $data['data_consent'] = true;

        $student = CampusStudent::create($data);
        $student->tenants()->attach(current_tenant()?->id);

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

        // Normalitzar: treure espais (el correu mostra "104 PYW" però l'input pot contenir espai copiat)
        $code = strtoupper(str_replace(' ', '', trim($request->input('code', ''))));

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

        // Si l'alumne ja és a la cua (registrat mentre esperava el torn), redirigir a l'estat
        $queueEntry = \App\Models\CampusQueueEntry::where('tenant_id', current_tenant()?->id)
            ->where('email', $student->email)
            ->whereIn('status', [
                \App\Models\CampusQueueEntry::STATUS_WAITING,
                \App\Models\CampusQueueEntry::STATUS_NOTIFIED,
            ])
            ->first();

        if ($queueEntry) {
            return redirect()->route('campus.queue.status', ['email' => $student->email])
                ->with('success', '✓ Correu verificat. Aquí teniu l\'estat del vostre torn a la cua.');
        }

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

        return redirect()->route('campus.verification.notice')
            ->with('info', 'Nou codi enviat a ' . $student->email . '. Introduïu-lo aquí.');
    }

    // ── Recuperació de contrasenya (OTP) ──────────────────────────────────────

    public function showForgotPassword(): View
    {
        return view('campus.auth.forgot-password');
    }

    public function sendPasswordReset(Request $request): RedirectResponse
    {
        $request->validate(['email' => ['required', 'email', 'max:150']]);

        $email   = strtolower(trim($request->input('email')));
        $student = CampusStudent::where('email', $email)->first();

        // Resposta sempre igual per evitar enumerar comptes
        if ($student) {
            Mail::to($student->email)->send(new PasswordResetMail($student));
        }

        $request->session()->put('password_reset_email', $email);

        return redirect()->route('campus.password.code')
            ->with('info', 'Si existeix un compte amb ' . $email . ', hem enviat un codi de recuperació al correu.');
    }

    public function showPasswordResetCode(Request $request): View|RedirectResponse
    {
        if (! $request->session()->has('password_reset_email')) {
            return redirect()->route('campus.password.request');
        }

        return view('campus.auth.reset-password');
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $email = $request->session()->get('password_reset_email');

        if (! $email) {
            return redirect()->route('campus.password.request');
        }

        $request->validate([
            'code'     => ['required', 'string'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $code    = strtoupper(str_replace(' ', '', trim($request->input('code', ''))));
        $student = CampusStudent::where('email', $email)->first();

        if (! $student) {
            return back()->withErrors(['code' => 'No s\'ha trobat el compte.']);
        }

        $student->increment('verification_attempts');

        if (! $student->isValidOtp($code)) {
            $attemptsLeft = max(0, 3 - $student->fresh()->verification_attempts);

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

        $student->update([
            'password'                     => Hash::make($request->input('password')),
            'verification_code'            => null,
            'verification_code_expires_at' => null,
            'verification_attempts'        => 0,
            // Si el compte no estava verificat, aprofitem per verificar-lo (prova de recepció de correu)
            'email_verified_at'            => $student->email_verified_at ?? now(),
        ]);

        $request->session()->forget('password_reset_email');

        return redirect()->route('campus.login')
            ->with('success', '✓ Contrasenya actualitzada correctament. Ja podeu accedir.');
    }
}
