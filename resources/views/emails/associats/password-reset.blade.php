<x-mail::message>
# 🔑 {{ __('Recuperar contrasenya') }}

{{ __('Hem rebut una sol·licitud per restablir la contrasenya del vostre compte de soci a **:org**.', ['org' => setting('associats_org_name', __('Entitat'))]) }}

{{ __('Soci nº **:number**', ['number' => $member->member_number]) }} — {{ $member->full_name }}

---

{{ __('Feu clic al botó per establir una nova contrasenya. L\'enllaç és vàlid durant **1 hora**.') }}

<x-mail::button :url="$resetUrl">
{{ __('Restablir contrasenya') }}
</x-mail::button>

> {{ __('Si no heu sol·licitat aquest canvi, podeu ignorar aquest correu. El vostre compte no es modificarà.') }}

{{ __('Gràcies') }},<br>
{{ setting('associats_org_name', __('Entitat')) }}
</x-mail::message>
