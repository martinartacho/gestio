<x-mail::message>
# {{ __('Inscripció pendent de pagament') }}

{{ __('Hola, **:name**!', ['name' => $student->first_name]) }}

{{ __('Hem rebut la vostra sol·licitud d\'inscripció. La plaça queda **reservada** fins que l\'equip confirmi la recepció del pagament.') }}

---

## {{ __('Cursos reservats') }}

@foreach ($enrollments as $enrollment)
- **{{ $enrollment->course->title }}** — {{ number_format($enrollment->course->price, 2, ',', '.') }} €
@endforeach

**{{ __('Total') }}: {{ number_format($enrollments->sum(fn($e) => $e->course->price), 2, ',', '.') }} €**

---

## {{ __('Dades del pagament') }}

@if ($reference)
**{{ __('Referència') }}:** `{{ $reference }}`

@endif
@if ($method === 'transfer')
**{{ __('Mètode') }}:** {{ __('Transferència bancària') }}
@if ($settings->get('payment_iban'))
**IBAN:** {{ $settings->get('payment_iban') }}
@endif
@if ($settings->get('payment_bank_holder'))
**{{ __('Titular') }}:** {{ $settings->get('payment_bank_holder') }}
@endif
@elseif ($method === 'bizum')
**{{ __('Mètode') }}:** Bizum
@if ($settings->get('payment_bizum_number'))
**{{ __('Número Bizum') }}:** {{ $settings->get('payment_bizum_number') }}
@endif
@elseif ($method === 'cash')
**{{ __('Mètode') }}:** {{ __('Efectiu a la secretaria') }}
@elseif ($method === 'paypal')
**{{ __('Mètode') }}:** PayPal
@if ($settings->get('payment_paypal_email'))
**{{ __('Envia a') }}:** {{ $settings->get('payment_paypal_email') }}
@endif
@endif

**{{ __('Concepte') }}:** {{ $concept }}

@php $exp = $enrollments->first()->payment_expires_at; @endphp
@if ($exp)
@php
    $expText = $exp->isToday()
        ? __('avui a les :time h', ['time' => $exp->format('H:i')])
        : ($exp->isTomorrow()
            ? __('demà a les :time h', ['time' => $exp->format('H:i')])
            : __(':date a les :time h', ['date' => $exp->format('d/m/Y'), 'time' => $exp->format('H:i')]));
@endphp

> ⚠️ {{ __('La reserva caduca el **:date**. Passat aquest termini, les places quedaran lliures.', ['date' => $expText]) }}
@endif

---

{{ __('Un cop rebut el pagament, us confirmarem les places per correu electrònic.') }}

{{ __('Gràcies') }},<br>
{{ config('app.name') }}
</x-mail::message>
