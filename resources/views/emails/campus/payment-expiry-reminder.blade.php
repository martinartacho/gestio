<x-mail::message>
# ⏰ Recordatori: queda 1 hora per fer el pagament

Hola, **{{ $enrollment->student->first_name }}**!

Us recordem que la reserva de plaça al curs **{{ $enrollment->course->title }}** **caduca en aproximadament 1 hora**.

@php
    $exp = $enrollment->payment_expires_at;
    $expText = $exp->isToday()
        ? 'avui a les ' . $exp->format('H:i') . ' h'
        : $exp->format('d/m/Y') . ' a les ' . $exp->format('H:i') . ' h';
@endphp

> ⚠️ Termini: **{{ $expText }}**. Si no es rep el pagament, la plaça quedarà alliberada.

---

## Recordatori de les dades de pagament

@if ($enrollment->payment_reference)
**Referència:** `{{ $enrollment->payment_reference }}`

@endif
@if ($enrollment->payment_method === 'transfer')
**Mètode:** Transferència bancària
@if ($settings->get('payment_iban'))
**IBAN:** {{ $settings->get('payment_iban') }}
@endif
@if ($settings->get('payment_bank_holder'))
**Titular:** {{ $settings->get('payment_bank_holder') }}
@endif
@elseif ($enrollment->payment_method === 'bizum')
**Mètode:** Bizum
@if ($settings->get('payment_bizum_number'))
**Número Bizum:** {{ $settings->get('payment_bizum_number') }}
@endif
@elseif ($enrollment->payment_method === 'cash')
**Mètode:** Efectiu a la secretaria
@elseif ($enrollment->payment_method === 'paypal')
**Mètode:** PayPal
@if ($settings->get('payment_paypal_email'))
**Envia a:** {{ $settings->get('payment_paypal_email') }}
@endif
@endif

**Concepte:** {{ $concept }}

**Import:** {{ number_format($enrollment->amount, 2, ',', '.') }} €

---

Si ja heu realitzat el pagament, ignoreu aquest missatge. L'equip ho verificarà i us confirmarà la plaça per correu.

Si voleu cancel·lar la inscripció, podeu fer-ho des del catàleg de cursos.

Gràcies,<br>
{{ config('app.name') }}
</x-mail::message>
