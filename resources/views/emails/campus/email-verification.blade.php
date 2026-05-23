<x-mail::message>
# Verifica el teu correu electrònic

Hola, **{{ $student->first_name }}**!

Introduïu el codi següent a la pàgina de verificació per activar el compte:

<x-mail::panel>
<div style="text-align:center;font-size:2rem;font-weight:bold;letter-spacing:0.3em;font-family:monospace;color:#4f46e5;">
{{ substr($otp, 0, 3) }}&nbsp;{{ substr($otp, 3, 3) }}
</div>
</x-mail::panel>

⏱ Aquest codi és vàlid durant **15 minuts**.

Si no heu sol·licitat cap verificació, ignoreu aquest missatge.

Gràcies,<br>
{{ config('app.name') }}
</x-mail::message>
