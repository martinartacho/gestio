<x-mail::message>
# ↩ Devolució processada

Hola, **{{ $enrollment->student->first_name ?? $enrollment->first_name }}**!

Us confirmem que hem processat la devolució de la vostra inscripció al curs **{{ $enrollment->course->title }}**.

---

## Detalls de la devolució

**Import retornat:** {{ number_format($enrollment->refunded_amount, 2, ',', '.') }} €

@if ($enrollment->refunded_amount < $enrollment->amount)
*(Devolució parcial — import original: {{ number_format($enrollment->amount, 2, ',', '.') }} €)*
@endif

@if ($isStripe)
**Mètode:** Targeta bancària (Stripe) — el reemborsament apareixerà al vostre extracte en 5-10 dies hàbils.
@else
**Mètode:** {{ \App\Models\CampusEnrollment::PAYMENT_METHODS[$enrollment->payment_method] ?? $enrollment->payment_method }}
— El reemborsament es farà pel mateix canal de pagament. Si no el rebeu en 5 dies hàbils, contacteu amb nosaltres.
@endif

@if ($enrollment->refund_notes)
**Observació:** {{ $enrollment->refund_notes }}
@endif

---

Si teniu qualsevol dubte, no dubteu a contactar amb l'equip.

Gràcies,<br>
{{ config('app.name') }}
</x-mail::message>
