<x-mail::message>
# ⏰ {{ __('Recordatori: queda 1 hora per fer el pagament') }}

{{ __('Hola, **:name**!', ['name' => $enrollment->student->first_name]) }}

{{ __('Us recordem que la reserva de plaça al curs **:course** **caduca en aproximadament 1 hora**.', ['course' => $enrollment->course->title]) }}

@php
    $exp = $enrollment->payment_expires_at;
    $expText = $exp->isToday()
        ? __('avui a les :time h', ['time' => $exp->format('H:i')])
        : __(':date a les :time h', ['date' => $exp->format('d/m/Y'), 'time' => $exp->format('H:i')]);
@endphp

> ⚠️ {{ __('Termini: **:date**. Si no es rep el pagament, la plaça quedarà alliberada.', ['date' => $expText]) }}

---

## {{ __('Recordatori de les dades de pagament') }}

@if ($enrollment->payment_reference)
**{{ __('Referència') }}:** `{{ $enrollment->payment_reference }}`

@endif
@if ($enrollment->payment_method === 'transfer')
**{{ __('Mètode') }}:** {{ __('Transferència bancària') }}
@if ($settings->get('payment_iban'))
**IBAN:** {{ $settings->get('payment_iban') }}
@endif
@if ($settings->get('payment_bank_holder'))
**{{ __('Titular') }}:** {{ $settings->get('payment_bank_holder') }}
@endif
@elseif ($enrollment->payment_method === 'bizum')
**{{ __('Mètode') }}:** Bizum
@if ($settings->get('payment_bizum_number'))
**{{ __('Número Bizum') }}:** {{ $settings->get('payment_bizum_number') }}
@endif
@elseif ($enrollment->payment_method === 'cash')
**{{ __('Mètode') }}:** {{ __('Efectiu a la secretaria') }}
@elseif ($enrollment->payment_method === 'paypal')
**{{ __('Mètode') }}:** PayPal
@if ($settings->get('payment_paypal_email'))
**{{ __('Envia a') }}:** {{ $settings->get('payment_paypal_email') }}
@endif
@endif

**{{ __('Concepte') }}:** {{ $concept }}

**{{ __('Import') }}:** {{ number_format($enrollment->amount, 2, ',', '.') }} €

---

{{ __('Si ja heu realitzat el pagament, ignoreu aquest missatge. L\'equip ho verificarà i us confirmarà la plaça per correu.') }}

{{ __('Si voleu cancel·lar la inscripció, podeu fer-ho des del catàleg de cursos.') }}

{{ __('Gràcies') }},<br>
{{ config('app.name') }}
</x-mail::message>
