<x-mail::message>
# {{ __('Verifica el teu correu electrònic') }}

{{ __('Hola, **:name**!', ['name' => $student->first_name]) }}

{{ __('Introduïu el codi següent a la pàgina de verificació per activar el compte:') }}

<x-mail::panel>
<div style="text-align:center;font-size:2rem;font-weight:bold;letter-spacing:0.3em;font-family:monospace;color:#4f46e5;">
{{ substr($otp, 0, 3) }}&nbsp;{{ substr($otp, 3, 3) }}
</div>
</x-mail::panel>

⏱ {{ __('Aquest codi és vàlid durant **15 minuts**.') }}

{{ __('Si no heu sol·licitat cap verificació, ignoreu aquest missatge.') }}

{{ __('Gràcies') }},<br>
{{ config('app.name') }}
</x-mail::message>
