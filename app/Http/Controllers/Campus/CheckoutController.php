<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Mail\Campus\ManualPaymentPendingMail;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Settings\SettingStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    private const MAX_PENDING = 3;

    private function isPreviewMode(): bool
    {
        return request()->boolean('preview')
            && auth('web')->user()?->hasPermissionTo('courses.edit');
    }

    public function create(Request $request, string $slug): RedirectResponse|View
    {
        $course  = CampusCourse::where('slug', $slug)->where('is_public', true)->firstOrFail();
        $student = auth('student')->user();

        // ── Compte suspès ────────────────────────────────────────────────────────
        if ($student->isSuspended()) {
            return redirect()->route('campus.catalog.show', $slug)
                ->with('error', 'El compte ha estat suspès. Contacteu amb l\'administració.');
        }

        // ── Evitar doble inscripció (cancel·lades/retornades permeten re-inscripció) ─
        if ($student->enrollments()
                    ->where('course_id', $course->id)
                    ->whereNotIn('status', ['cancelled', 'refunded'])
                    ->exists()) {
            return redirect()->route('campus.catalog.show', $slug)
                ->with('info', 'Ja estàs inscrit/a a aquest curs.');
        }

        // ── A2: Límit de pendents per alumne (anti seat-squatting) ───────────────
        $pendingCount = $student->enrollments()->where('status', 'pending')->count();
        if ($pendingCount >= self::MAX_PENDING) {
            return redirect()->route('campus.catalog.show', $slug)
                ->with('error', 'Tens ' . self::MAX_PENDING . ' inscripcions pendents de pagament. '
                    . 'Completa-les o cancel·la-les abans d\'inscriure\'t a un nou curs.');
        }

        // Verificar que les inscripcions estan obertes (tret que el curs ho permeti sempre o sigui preview)
        if (! $course->open_enrollment && ! $this->isPreviewMode()) {
            $season = $course->season;
            if (! $season || ! $season->isActive() || ! $season->enrollmentIsOpen()) {
                return redirect()->route('campus.catalog.show', $slug)
                    ->with('error', 'Les inscripcions per a aquest curs no estan obertes.');
            }
        }

        $studentData = [
            'first_name'      => $student->first_name,
            'last_name'       => $student->last_name,
            'email'           => $student->email,
            'phone'           => $student->phone,
            'enrollment_date' => now()->toDateString(),
        ];

        // ── Curs gratuït → inscripció directa ────────────────────────────────────
        if ($course->price <= 0) {
            // A3: transacció + lock per evitar race condition
            DB::transaction(function () use ($course, $student, $studentData, $slug, &$redirect) {
                $locked = CampusCourse::where('id', $course->id)->lockForUpdate()->first();
                if ($locked->isFull()) {
                    $redirect = redirect()->route('campus.catalog.show', $slug)
                        ->with('error', 'Ho sentim, el curs ja no té places disponibles.');
                    return;
                }
                CampusEnrollment::updateOrCreate(
                    ['student_id' => $student->id, 'course_id' => $course->id],
                    $studentData + [
                        'status'         => 'paid',
                        'payment_method' => 'free',
                        'amount'         => 0,
                        'paid_at'        => now(),
                    ]
                );
            });

            return $redirect ?? redirect()->route('campus.portal.courses')
                ->with('success', '✓ Inscripció completada. El curs és gratuït!');
        }

        $method = $request->input('payment_method', 'stripe');

        // ── Pagament manual (transferència, bizum, efectiu, paypal) ──────────────
        if ($method !== 'stripe') {
            $allowedMethods = array_filter([
                'transfer' => setting('payment_transfer_enabled'),
                'bizum'    => setting('payment_bizum_enabled'),
                'cash'     => setting('payment_cash_enabled'),
                'paypal'   => setting('payment_paypal_enabled'),
            ]);

            if (! isset($allowedMethods[$method])) {
                return redirect()->route('campus.catalog.show', $slug)
                    ->with('error', 'El mètode de pagament seleccionat no està disponible.');
            }

            $settings    = app(SettingStore::class);
            $expiryValue = (int)    $settings->get('payment_expiry_value', 5);
            $expiryUnit  = (string) $settings->get('payment_expiry_unit', 'days');
            $expiresAt   = $expiryValue > 0
                ? ($expiryUnit === 'hours' ? now()->addHours($expiryValue) : now()->addDays($expiryValue))
                : null;

            // A3: transacció + lock — la comprovació de capacitat i la inserció son atòmiques
            $enrollment = null;
            $isFull     = false;

            DB::transaction(function () use (
                $course, $student, $studentData, $method, $expiresAt, &$enrollment, &$isFull
            ) {
                $locked = CampusCourse::where('id', $course->id)->lockForUpdate()->first();

                if ($locked->isFull()) {
                    $isFull = true;
                    return;
                }

                $enrollment = CampusEnrollment::updateOrCreate(
                    ['student_id' => $student->id, 'course_id' => $course->id],
                    $studentData + [
                        'status'             => 'pending',
                        'payment_method'     => $method,
                        'amount'             => $course->price,
                        'payment_reference'  => CampusEnrollment::generateReference(),
                        'payment_expires_at' => $expiresAt,
                    ]
                );
            });

            if ($isFull) {
                return redirect()->route('campus.catalog.show', $slug)
                    ->with('error', 'Ho sentim, el curs ja no té places disponibles.');
            }

            Mail::to($student->email)
                ->send(new ManualPaymentPendingMail($student, $course, $enrollment, $method));

            return view('campus.checkout.pending', compact('course', 'student', 'enrollment', 'method', 'settings'));
        }

        // ── Stripe ────────────────────────────────────────────────────────────────
        // A3: verificació de capacitat amb lock abans de crear la sessió Stripe
        $isFull = false;
        DB::transaction(function () use ($course, &$isFull) {
            $locked = CampusCourse::where('id', $course->id)->lockForUpdate()->first();
            $isFull = $locked->isFull();
        });

        if ($isFull) {
            return redirect()->route('campus.catalog.show', $slug)
                ->with('error', 'Ho sentim, el curs ja no té places disponibles.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $stripeSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => [[
                'price_data' => [
                    'currency'     => 'eur',
                    'unit_amount'  => (int) ($course->price * 100),
                    'product_data' => [
                        'name'        => $course->title,
                        'description' => $course->code,
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'           => 'payment',
            'customer_email' => $student->email,
            'success_url'    => route('campus.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'     => route('campus.catalog.show', $slug),
            'metadata'       => [
                'student_id' => $student->id,
                'course_id'  => $course->id,
            ],
        ]);

        CampusEnrollment::updateOrCreate(
            ['student_id' => $student->id, 'course_id' => $course->id],
            $studentData + [
                'status'            => 'pending',
                'payment_method'    => 'stripe',
                'amount'            => $course->price,
                'stripe_session_id' => $stripeSession->id,
            ]
        );

        return redirect($stripeSession->url);
    }

    /**
     * Cancel·la una matrícula pendent de l'alumne autenticat.
     */
    public function cancelEnrollment(Request $request, string $slug): RedirectResponse
    {
        $course  = CampusCourse::where('slug', $slug)->where('is_public', true)->firstOrFail();
        $student = auth('student')->user();

        $enrollment = $student->enrollments()
            ->where('course_id', $course->id)
            ->where('status', 'pending')
            ->first();

        if (! $enrollment) {
            return redirect()->route('campus.catalog.show', $slug)
                ->with('error', 'No s\'ha trobat cap inscripció pendent per cancel·lar.');
        }

        $enrollment->update(['status' => 'cancelled']);

        return redirect()->route('campus.catalog.show', $slug)
            ->with('success', 'Inscripció cancel·lada. Ara pots tornar a inscriure\'t amb el mètode que prefereixis.');
    }

    public function success(Request $request): View
    {
        return view('campus.checkout.success');
    }

    public function cancel(): RedirectResponse
    {
        return redirect()->route('campus.catalog.index')
            ->with('info', 'Pagament cancel·lat.');
    }
}
