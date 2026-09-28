<x-mail::message>
# 🔑 {{ __('Recuperar contrasenya') }}

{{ __('Hem rebut una sol·licitud per restablir la contrasenya del vostre compte a **:app**.', ['app' => config('app.name')]) }}

---

## {{ __('Codi de verificació') }}

<x-mail::panel>
<div style="text-align:center">
<p style="font-size:0.85rem;color:#6b7280;margin-bottom:0.5rem">{{ __('Introduïu aquest codi per confirmar la vostra identitat') }}</p>
<p style="font-size:2.5rem;font-weight:bold;letter-spacing:0.3em;font-family:monospace;color:#4f46e5;margin:0">
{{ substr($otp, 0, 3) }}&nbsp;{{ substr($otp, 3, 3) }}
</p>
<p style="font-size:0.75rem;color:#9ca3af;margin-top:0.5rem">{{ __('Vàlid durant 15 minuts') }}</p>
</div>
</x-mail::panel>

<x-mail::button :url="route('campus.password.code')">
{{ __('Canviar contrasenya') }}
</x-mail::button>

> {{ __('Si no heu sol·licitat aquest canvi, podeu ignorar aquest correu. El vostre compte no es modificarà.') }}

{{ __('Gràcies') }},<br>
{{ config('app.name') }}
</x-mail::message>
