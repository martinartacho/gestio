<x-mail::message>
# 🔑 Recuperar contrasenya

Hem rebut una sol·licitud per restablir la contrasenya del vostre compte de soci a **{{ setting('associats_org_name', 'AC Granollers') }}**.

Soci nº **{{ $member->member_number }}** — {{ $member->full_name }}

---

Feu clic al botó per establir una nova contrasenya. L'enllaç és vàlid durant **1 hora**.

<x-mail::button :url="$resetUrl">
Restablir contrasenya
</x-mail::button>

> Si no heu sol·licitat aquest canvi, podeu ignorar aquest correu. El vostre compte no es modificarà.

Gràcies,<br>
{{ setting('associats_org_name', 'AC Granollers') }}
</x-mail::message>
