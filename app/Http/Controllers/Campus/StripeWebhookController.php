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
                // Dev: sense secret configurat, parsem l'event sense verificar signatura
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
        $enrollment = CampusEnrollment::where('stripe_session_id', $session->id)->first();

        if (! $enrollment || $enrollment->isPaid()) {
            return;
        }

        $enrollment->update([
            'status'                 => 'paid',
            'stripe_payment_intent'  => $session->payment_intent,
            'paid_at'                => now(),
        ]);

        // Inserir al pivot de confirmats
        $enrollment->course->students()->syncWithoutDetaching([
            $enrollment->student_id => [
                'enrollment_id' => $enrollment->id,
                'enrolled_at'   => now(),
            ],
        ]);
    }
}
