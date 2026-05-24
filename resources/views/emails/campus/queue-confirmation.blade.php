<x-mail::message>
# 🎟 Torn reservat correctament

Hola!

Us heu apuntat a la cua d'inscripcions de **{{ config('app.name') }}**.

---

## El vostre torn

<x-mail::panel>
<div style="text-align:center">
<p style="font-size:0.85rem;color:#6b7280;margin-bottom:0.25rem">Número de torn</p>
<p style="font-size:2.5rem;font-weight:bold;color:#4f46e5;margin:0">#{{ $entry->queue_number }}</p>
<p style="font-size:0.85rem;color:#6b7280;margin-top:0.5rem">Hora estimada d'accés: <strong>{{ $entry->slotTimeLabel() }}</strong></p>
</div>
</x-mail::panel>

⏰ Rebreu un segon correu amb el **codi d'accés** quan arribi el vostre torn.

> No cal que espereu davant del navegador. Us avisarem per correu.

Gràcies per la paciència,<br>
{{ config('app.name') }}
</x-mail::message>
