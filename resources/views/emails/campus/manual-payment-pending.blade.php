<x-mail::message>
# Inscripció pendent de pagament

Hola, **{{ $student->first_name }}**!

Hem rebut la vostra sol·licitud d'inscripció. La plaça queda **reservada** fins que l'equip confirmi la recepció del pagament.

---

## Cursos reservats

@foreach ($enrollments as $enrollment)
- **{{ $enrollment->course->title }}** — {{ number_format($enrollment->course->price, 2, ',', '.') }} €
@endforeach

**Total: {{ number_format($enrollments->sum(fn($e) => $e->course->price), 2, ',', '.') }} €**

---

## Dades del pagament

@if ($reference)
**Referència:** `{{ $reference }}`

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

@php $exp = $enrollments->first()->payment_expires_at; @endphp
@if ($exp)
@php
    $expText = $exp->isToday()
        ? 'avui a les ' . $exp->format('H:i') . ' h'
        : ($exp->isTomorrow()
            ? 'demà a les ' . $exp->format('H:i') . ' h'
            : $exp->format('d/m/Y') . ' a les ' . $exp->format('H:i') . ' h');
@endphp

> ⚠️ La reserva caduca el **{{ $expText }}**. Passat aquest termini, les places quedaran lliures.
@endif

---

Un cop rebut el pagament, us confirmarem les places per correu electrònic.

Gràcies,<br>
{{ config('app.name') }}
</x-mail::message>
