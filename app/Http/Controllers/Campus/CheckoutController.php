<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusCourse;
use App\Models\CampusEnrollment;
use Illuminate\Http\Request;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Stripe;

class CheckoutController extends Controller
{
    public function create(Request $request, string $slug)
    {
        $course  = CampusCourse::where('slug', $slug)->where('is_public', true)->firstOrFail();
        $student = auth('student')->user();

        // Evitar doble inscripció
        if ($student->courses()->where('campus_courses.id', $course->id)->exists()) {
            return redirect()->route('campus.catalog.show', $slug)
                ->with('info', 'Ja estàs inscrit/a a aquest curs.');
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
            'mode'          => 'payment',
            'customer_email'=> $student->email,
            'success_url'   => route('campus.checkout.success') . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url'    => route('campus.catalog.show', $slug),
            'metadata'      => [
                'student_id' => $student->id,
                'course_id'  => $course->id,
            ],
        ]);

        // Crear inscripció pendent
        CampusEnrollment::updateOrCreate(
            ['student_id' => $student->id, 'course_id' => $course->id],
            [
                'status'            => 'pending',
                'amount'            => $course->price,
                'stripe_session_id' => $stripeSession->id,
            ]
        );

        return redirect($stripeSession->url);
    }

    public function success(Request $request)
    {
        return view('campus.checkout.success');
    }

    public function cancel()
    {
        return redirect()->route('campus.catalog.index')
            ->with('info', 'Pagament cancel·lat.');
    }
}
