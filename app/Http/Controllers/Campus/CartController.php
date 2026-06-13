<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Mail\Campus\ManualPaymentPendingMail;
use App\Models\CampusCart;
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

class CartController extends Controller
{
    private function isPreviewMode(): bool
    {
        return request()->boolean('preview')
            && auth('web')->user()?->hasPermissionTo('courses.edit');
    }

    public function show(): View
    {
        $student = auth('student')->user();
        $cart    = CampusCart::forStudent($student);
        $cart->load('items.course.season', 'items.course.category', 'items.course.space');

        $settings = app(SettingStore::class);

        return view('campus.cart.show', compact('cart', 'student', 'settings'));
    }

    public function add(Request $request): RedirectResponse
    {
        if (! auth('student')->check()) {
            return redirect()->route('campus.login', ['redirect' => url()->previous()])
                ->with('info', 'Identifica\'t per afegir cursos al carret.');
        }

        $student = auth('student')->user();
        $course  = CampusCourse::where('slug', $request->input('slug'))
            ->where('is_public', true)
            ->firstOrFail();

        if ($student->isSuspended()) {
            return back()->with('error', 'El compte ha estat suspès. Contacteu amb l\'administració.');
        }

        if ($student->enrollments()
                ->where('course_id', $course->id)
                ->whereNotIn('status', ['cancelled', 'refunded'])
                ->exists()) {
            return back()->with('info', 'Ja estàs inscrit/a a aquest curs.');
        }

        if ($course->isFull()) {
            return back()->with('error', 'El curs ja no té places disponibles.');
        }

        $cart = CampusCart::forStudent($student);

        if ($cart->items()->where('course_id', $course->id)->exists()) {
            return redirect()->route('campus.cart.show')
                ->with('info', '"' . $course->title . '" ja és al carret.');
        }

        $maxCartItems = (int) setting('payment_max_cart_items', 5);
        if ($cart->items()->count() >= $maxCartItems) {
            return redirect()->route('campus.cart.show')
                ->with('error', 'El carret ja conté el màxim de ' . $maxCartItems . ' cursos.');
        }

        $cart->items()->create([
            'course_id' => $course->id,
            'price'     => $course->price,
        ]);

        // Si el carret ja és ple, anar directament al carret per fer el pagament
        if ($cart->items()->count() >= $maxCartItems) {
            return redirect()->route('campus.cart.show')
                ->with('success', '"' . $course->title . '" afegit. Carret complet — podeu procedir al pagament.');
        }

        // Tornar al catàleg per continuar afegint cursos
        return redirect()->route('campus.catalog.index')
            ->with('success', '"' . $course->title . '" afegit al carret.');
    }

    public function remove(int $itemId): RedirectResponse
    {
        $student = auth('student')->user();
        $cart    = CampusCart::forStudent($student);
        $item    = $cart->items()->findOrFail($itemId);
        $item->delete();

        return back()->with('success', 'Curs eliminat del carret.');
    }

    public function checkout(Request $request): RedirectResponse|View
    {
        $student = auth('student')->user();

        if ($student->isSuspended()) {
            return redirect()->route('campus.cart.show')
                ->with('error', 'El compte ha estat suspès. Contacteu amb l\'administració.');
        }

        $cart = CampusCart::forStudent($student);
        $cart->load('items.course');

        if ($cart->items->isEmpty()) {
            return redirect()->route('campus.cart.show')
                ->with('error', 'El carret és buit.');
        }

        // Verificar que tots els cursos estan oberts per inscripció (tret de preview)
        if (! $this->isPreviewMode()) {
            foreach ($cart->items as $item) {
                $course = $item->course;
                if (! $course->open_enrollment) {
                    $season = $course->season;
                    if (! $season || ! $season->isActive() || ! $season->enrollmentIsOpen()) {
                        return redirect()->route('campus.cart.show')
                            ->with('error', 'Les inscripcions per a "' . $course->title . '" no estan obertes.');
                    }
                }
            }
        }

        $studentData = [
            'first_name'      => $student->first_name,
            'last_name'       => $student->last_name,
            'email'           => $student->email,
            'phone'           => $student->phone,
            'enrollment_date' => now()->toDateString(),
        ];

        $method = $request->input('payment_method', 'stripe');

        // ── Pagament manual ──────────────────────────────────────────────────────
        if ($method !== 'stripe') {
            $allowedMethods = array_filter([
                'transfer' => setting('payment_transfer_enabled'),
                'bizum'    => setting('payment_bizum_enabled'),
                'cash'     => setting('payment_cash_enabled'),
                'paypal'   => setting('payment_paypal_enabled'),
            ]);

            if (! isset($allowedMethods[$method])) {
                return redirect()->route('campus.cart.show')
                    ->with('error', 'El mètode de pagament seleccionat no està disponible.');
            }

            $settings    = app(SettingStore::class);
            $expiryValue = (int)    $settings->get('payment_expiry_value', 5);
            $expiryUnit  = (string) $settings->get('payment_expiry_unit', 'days');
            $expiresAt   = $expiryValue > 0
                ? ($expiryUnit === 'hours' ? now()->addHours($expiryValue) : now()->addDays($expiryValue))
                : null;

            $reference   = CampusEnrollment::generateReference();
            $enrollments = collect();
            $fullCourse  = null;

            DB::transaction(function () use (
                $cart, $student, $studentData, $method, $expiresAt, $reference, &$enrollments, &$fullCourse
            ) {
                foreach ($cart->items as $item) {
                    $locked = CampusCourse::where('id', $item->course_id)->lockForUpdate()->first();
                    if ($locked->isFull()) {
                        $fullCourse = $locked->title;
                        throw new \Exception('full');
                    }
                    $enrollment = CampusEnrollment::updateOrCreate(
                        ['student_id' => $student->id, 'course_id' => $item->course_id],
                        $studentData + [
                            'status'             => 'pending',
                            'payment_method'     => $method,
                            'amount'             => $item->price,
                            'payment_reference'  => $reference,
                            'payment_expires_at' => $expiresAt,
                        ]
                    );
                    $enrollments->push($enrollment);
                }
                // Buidar el carret un cop creades les inscripcions
                $cart->items()->delete();
            });

            if ($fullCourse) {
                return redirect()->route('campus.cart.show')
                    ->with('error', 'Ho sentim, "' . $fullCourse . '" ja no té places disponibles.');
            }

            Mail::to($student->email)->send(
                new ManualPaymentPendingMail($student, $enrollments, $method)
            );

            return view('campus.cart.pending', compact('cart', 'student', 'enrollments', 'method', 'settings', 'reference'));
        }

        // ── Stripe multi-curs ────────────────────────────────────────────────────
        $lineItems = $cart->items->map(fn($item) => [
            'price_data' => [
                'currency'     => 'eur',
                'unit_amount'  => (int) ($item->price * 100),
                'product_data' => [
                    'name'        => $item->course->title,
                    'description' => $item->course->code,
                ],
            ],
            'quantity' => 1,
        ])->values()->toArray();

        $isFull     = false;
        $fullCourse = null;

        // Verificació de capacitat abans de crear la sessió Stripe
        DB::transaction(function () use ($cart, &$isFull, &$fullCourse) {
            foreach ($cart->items as $item) {
                $locked = CampusCourse::where('id', $item->course_id)->lockForUpdate()->first();
                if ($locked->isFull()) {
                    $isFull     = true;
                    $fullCourse = $locked->title;
                    return;
                }
            }
        });

        if ($isFull) {
            return redirect()->route('campus.cart.show')
                ->with('error', 'Ho sentim, "' . $fullCourse . '" ja no té places disponibles.');
        }

        Stripe::setApiKey(config('services.stripe.secret'));

        $stripeSession = StripeSession::create([
            'payment_method_types' => ['card'],
            'line_items'           => $lineItems,
            'mode'                 => 'payment',
            'customer_email'       => $student->email,
            'success_url'          => route('campus.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'           => route('campus.cart.show'),
            'metadata'             => ['cart_id' => $cart->id],
        ]);

        // Crear inscripcions pendents per tots els cursos del carret
        DB::transaction(function () use ($cart, $student, $studentData, $stripeSession) {
            foreach ($cart->items as $item) {
                CampusEnrollment::updateOrCreate(
                    ['student_id' => $student->id, 'course_id' => $item->course_id],
                    $studentData + [
                        'status'            => 'pending',
                        'payment_method'    => 'stripe',
                        'amount'            => $item->price,
                        'stripe_session_id' => $stripeSession->id,
                    ]
                );
            }
            // Buidar el carret un cop reserves creades
            $cart->items()->delete();
        });

        return redirect($stripeSession->url);
    }
}
