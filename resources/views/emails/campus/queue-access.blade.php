<x-mail::message>
# 🚀 {{ __('Ara és el teu torn!') }}

{{ __('Ha arribat el moment. Teniu **:minutes minuts** per accedir al catàleg i completar la inscripció.', ['minutes' => $windowMinutes]) }}

---

## {{ __('Codi d\'accés') }}

<x-mail::panel>
<div style="text-align:center">
<p style="font-size:0.85rem;color:#6b7280;margin-bottom:0.5rem">{{ __('Introduïu aquest codi al catàleg de cursos') }}</p>
<p style="font-size:2.5rem;font-weight:bold;letter-spacing:0.3em;font-family:monospace;color:#4f46e5;margin:0">
{{ substr($entry->access_code, 0, 3) }}&nbsp;{{ substr($entry->access_code, 3, 3) }}
</p>
<p style="font-size:0.75rem;color:#9ca3af;margin-top:0.5rem">
{{ __('Vàlid fins') }}: {{ $entry->access_expires_at?->format('H:i') }} h
</p>
</div>
</x-mail::panel>

<x-mail::button :url="route('campus.catalog.index', ['tenant' => $entry->tenant?->slug])">
{{ __('Accedir al catàleg de cursos') }}
</x-mail::button>

> ⚠️ {{ __('Si no completeu la inscripció en :minutes minuts, el torn s\'assignarà a la persona següent.', ['minutes' => $windowMinutes]) }}

{{ __('Gràcies') }},<br>
{{ config('app.name') }}
</x-mail::message>
