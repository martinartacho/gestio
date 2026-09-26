<x-mail::message>
# ↩ {{ __('Devolució processada') }}

{{ __('Hola, **:name**!', ['name' => $enrollment->student->first_name ?? $enrollment->first_name]) }}

{{ __('Us confirmem que hem processat la devolució de la vostra inscripció al curs **:course**.', ['course' => $enrollment->course->title]) }}

---

## {{ __('Detalls de la devolució') }}

**{{ __('Import retornat') }}:** {{ number_format($enrollment->refunded_amount, 2, ',', '.') }} €

@if ($enrollment->refunded_amount < $enrollment->amount)
*({{ __('Devolució parcial — import original: :amount €', ['amount' => number_format($enrollment->amount, 2, ',', '.')]) }})*
@endif

@if ($isStripe)
**{{ __('Mètode') }}:** {{ __('Targeta bancària (Stripe) — el reemborsament apareixerà al vostre extracte en 5-10 dies hàbils.') }}
@else
**{{ __('Mètode') }}:** {{ __(\App\Models\CampusEnrollment::PAYMENT_METHODS[$enrollment->payment_method] ?? $enrollment->payment_method) }}
— {{ __('El reemborsament es farà pel mateix canal de pagament. Si no el rebeu en 5 dies hàbils, contacteu amb nosaltres.') }}
@endif

@if ($enrollment->refund_notes)
**{{ __('Observació') }}:** {{ $enrollment->refund_notes }}
@endif

---

{{ __('Si teniu qualsevol dubte, no dubteu a contactar amb l\'equip.') }}

{{ __('Gràcies') }},<br>
{{ config('app.name') }}
</x-mail::message>
