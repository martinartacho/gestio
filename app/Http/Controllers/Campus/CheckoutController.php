<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use App\Settings\SettingStore;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function create(Request $request, string $slug): RedirectResponse|View
    {
        $course  = CampusCourse::where('slug', $slug)->where('is_public', true)->firstOrFail();
        $student = auth('student')->user();

        // Evitar doble inscripció
        if ($student->enrollments()->where('course_id', $course->id)->exists()) {
            return redirect()->route('campus.catalog.show', $slug)
                ->with('info', 'Ja estàs inscrit/a a aquest curs.');
        }

        // Verificar capacitat (pending + paid + confirmed + completed compten)
        if ($course->isFull()) {
            return redirect()->route('campus.catalog.show', $slug)
                ->with('error', 'Ho sentim, el curs ja no té places disponibles.');
        }

        // Verificar que les inscripcions estan obertes (tret que el curs ho permeti sempre)
        if (! $course->open_enrollment) {
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

        // ── Curs gratuït → inscripció directa ────────────────────────────────
        if ($course->price <= 0) {
            CampusEnrollment::updateOrCreate(
                ['student_id' => $student->id, 'course_id' => $course->id],
                $studentData + [
                    'status'         => 'paid',
                    'payment_method' => 'free',
                    'amount'         => 0,
                    'paid_at'        => now(),
                ]
            );

            return redirect()->route('campus.portal.courses')
                ->with('success', '✓ Inscripció completada. El curs és gratuït!');
        }

        $method = $request->input('payment_method', 'stripe');

        // ── Pagament manual (transferència, bizum, efectiu, paypal) ───────────
        if ($method !== 'stripe') {
            $allowedMethods = array_filter([
                'transfer' => setting('payment_transfer_enabled'),
                'bizum'    => setting('payment_bizum_enabled'),
                'cash'     => setting('payment_cash_enabled'),
                'paypal'   => setting('payment_paypal_enabled'),
            ]);

            // Rebutja mètode no habilitat
            if (! isset($allowedMethods[$method])) {
                return redirect()->route('campus.catalog.show', $slug)
                    ->with('error', 'El mètode de pagament seleccionat no està disponible.');
            }

            $settings     = app(SettingStore::class);
            $expiryValue  = (int)    $settings->get('payment_expiry_value', 5);
            $expiryUnit   = (string) $settings->get('payment_expiry_unit', 'days');
            $expiresAt    = $expiryValue > 0
                ? ($expiryUnit === 'hours' ? now()->addHours($expiryValue) : now()->addDays($expiryValue))
                : null;

            $enrollment = CampusEnrollment::updateOrCreate(
                ['student_id' => $student->id, 'course_id' => $course->id],
                $studentData + [
                    'status'              => 'pending',
                    'payment_method'      => $method,
                    'amount'              => $course->price,
                    'payment_reference'   => CampusEnrollment::generateReference(),
                    'payment_expires_at'  => $expiresAt,
                ]
            );

            return view('campus.checkout.pending', compact('course', 'student', 'enrollment', 'method', 'settings'));
        }

        // ── Stripe ────────────────────────────────────────────────────────────
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
