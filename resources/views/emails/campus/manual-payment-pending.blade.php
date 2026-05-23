<x-mail::message>
# Inscripció pendent de pagament

Hola, **{{ $student->first_name }}**!

Hem rebut la vostra sol·licitud d'inscripció al curs **{{ $course->title }}**.

La vostra plaça queda **reservada** fins que l'equip confirmi la recepció del pagament.

---

## Dades del pagament

@if ($enrollment->payment_reference)
**Referència:** `{{ $enrollment->payment_reference }}`

@endif
@if ($method === 'transfer')
**Mètode:** Transferència bancària
@if ($settings->get('payment_iban'))
**IBAN:** {{ $settings->get('payment_iban') }}
@endif
@if ($settings->get('payment_bank_holder'))
**Titular:** {{ $settings->get('payment_bank_holder') }}
@endif
@elseif ($method === 'bizum')
**Mètode:** Bizum
@if ($settings->get('payment_bizum_number'))
**Número Bizum:** {{ $settings->get('payment_bizum_number') }}
@endif
@elseif ($method === 'cash')
**Mètode:** Efectiu a la secretaria
@elseif ($method === 'paypal')
**Mètode:** PayPal
@if ($settings->get('payment_paypal_email'))
**Envia a:** {{ $settings->get('payment_paypal_email') }}
@endif
@endif

**Concepte:** {{ $concept }}

**Import:** {{ number_format($course->price, 2, ',', '.') }} €

@if ($enrollment->payment_expires_at)
@php
    $exp = $enrollment->payment_expires_at;
    $expText = $exp->isToday()
        ? 'avui a les ' . $exp->format('H:i') . ' h'
        : ($exp->isTomorrow()
            ? 'demà a les ' . $exp->format('H:i') . ' h'
            : $exp->format('d/m/Y') . ' a les ' . $exp->format('H:i') . ' h');
@endphp

> ⚠️ La reserva caduca el **{{ $expText }}**. Passat aquest termini, la plaça quedarà lliure.
@endif

---

Un cop rebut el pagament, us confirmarem la plaça per correu electrònic.

Gràcies,<br>
{{ config('app.name') }}
</x-mail::message>
