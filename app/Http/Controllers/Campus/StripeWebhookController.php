<?php

namespace App\Http\Controllers\Campus;

use App\Http\Controllers\Controller;
use App\Models\CampusEnrollment;
use Illuminate\Http\Request;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeWebhookController extends Controller
{
    public function handle(Request $request)
    {
        Stripe::setApiKey(config('services.stripe.secret'));

        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret    = config('services.stripe.webhook_secret');

        try {
            if ($secret) {
                $event = Webhook::constructEvent($payload, $sigHeader, $secret);
            } else {
                $event = \Stripe\Event::constructFrom(json_decode($payload, true));
            }
        } catch (SignatureVerificationException $e) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            $this->handleCheckoutCompleted($session);
        }

        return response()->json(['status' => 'ok']);
    }

    private function handleCheckoutCompleted(object $session): void
    {
        // Trobar totes les inscripcions per aquesta sessió Stripe (pot ser 1 o N del carret)
        $enrollments = CampusEnrollment::where('stripe_session_id', $session->id)->get();

        if ($enrollments->isEmpty()) {
            return;
        }

        foreach ($enrollments as $enrollment) {
            if ($enrollment->isPaid()) {
                continue;
            }

            $enrollment->update([
                'status'                => 'paid',
                'stripe_payment_intent' => $session->payment_intent,
                'paid_at'               => now(),
            ]);

            $enrollment->course->students()->syncWithoutDetaching([
                $enrollment->student_id => [
                    'enrollment_id' => $enrollment->id,
                    'enrolled_at'   => now(),
                ],
            ]);
        }
    }
}
