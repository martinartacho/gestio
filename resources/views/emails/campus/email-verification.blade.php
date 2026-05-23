<x-mail::message>
# Confirma el teu correu electrònic

Hola, **{{ $student->first_name }}**!

Gràcies per registrar-te a {{ config('app.name') }}.

Per activar el compte i poder inscriure't als cursos, confirmeu el vostre correu electrònic fent clic al botó:

<x-mail::button :url="$verificationUrl" color="primary">
Verificar correu electrònic
</x-mail::button>

Aquest enllaç és vàlid durant **72 hores**.

Si no heu creat cap compte, podeu ignorar aquest missatge.

Gràcies,<br>
{{ config('app.name') }}
</x-mail::message>
