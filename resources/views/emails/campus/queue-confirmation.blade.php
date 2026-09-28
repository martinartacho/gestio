<x-mail::message>
# 🎟 {{ __('Torn reservat correctament') }}

{{ __('Hola!') }}

{{ __('Us heu apuntat a la cua d\'inscripcions de **:app**.', ['app' => config('app.name')]) }}

---

## {{ __('El vostre torn') }}

<x-mail::panel>
<div style="text-align:center">
<p style="font-size:0.85rem;color:#6b7280;margin-bottom:0.25rem">{{ __('Número de torn') }}</p>
<p style="font-size:2.5rem;font-weight:bold;color:#4f46e5;margin:0">#{{ $entry->queue_number }}</p>
<p style="font-size:0.85rem;color:#6b7280;margin-top:0.5rem">{{ __('Hora estimada d\'accés') }}: <strong>{{ $entry->slotTimeLabel() }}</strong></p>
</div>
</x-mail::panel>

⏰ {{ __('Rebreu un segon correu amb el **codi d\'accés** quan arribi el vostre torn.') }}

> {{ __('No cal que espereu davant del navegador. Us avisarem per correu.') }}

---

{{ __('Necessiteu un torn diferent? Podeu canviar l\'hora:') }}

<div style="text-align:center;margin:1rem 0;">
<a href="{{ route('campus.queue.change-slot', ['email' => $entry->email, 'n' => $entry->queue_number]) }}"
   style="display:inline-block;background:#6b7280;color:#ffffff;text-decoration:none;
          font-size:0.875rem;font-weight:600;padding:0.625rem 1.5rem;border-radius:0.5rem;">
    📅 {{ __('Canviar l\'hora del torn') }}
</a>
</div>

{{ __('Gràcies per la paciència') }},<br>
{{ config('app.name') }}
</x-mail::message>
