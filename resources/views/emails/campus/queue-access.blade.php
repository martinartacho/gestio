<x-mail::message>
# 🚀 Ara és el teu torn!

Ha arribat el moment. Teniu **{{ $windowMinutes }} minuts** per accedir al catàleg i completar la inscripció.

---

## Codi d'accés

<x-mail::panel>
<div style="text-align:center">
<p style="font-size:0.85rem;color:#6b7280;margin-bottom:0.5rem">Introduïu aquest codi al catàleg de cursos</p>
<p style="font-size:2.5rem;font-weight:bold;letter-spacing:0.3em;font-family:monospace;color:#4f46e5;margin:0">
{{ substr($entry->access_code, 0, 3) }}&nbsp;{{ substr($entry->access_code, 3, 3) }}
</p>
<p style="font-size:0.75rem;color:#9ca3af;margin-top:0.5rem">
Vàlid fins: {{ $entry->access_expires_at?->format('H:i') }} h
</p>
</div>
</x-mail::panel>

<x-mail::button :url="url('/cursos')">
Accedir al catàleg de cursos
</x-mail::button>

> ⚠️ Si no completeu la inscripció en {{ $windowMinutes }} minuts, el torn s'assignarà a la persona següent.

Gràcies,<br>
{{ config('app.name') }}
</x-mail::message>
