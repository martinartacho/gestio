<nav style="background:#fff;border-bottom:1px solid #e5e7eb;padding:0.75rem 1rem;">
    <div style="max-width:72rem;margin:0 auto;display:flex;align-items:center;justify-content:space-between;">
        <a href="{{ url('/') }}"
           style="font-size:1.125rem;font-weight:700;color:#4338ca;text-decoration:none;"
           onmouseover="this.style.color='#3730a3'" onmouseout="this.style.color='#4338ca'">
            {{ setting('campus_name', 'Campus') }}
        </a>
        <div style="display:flex;align-items:center;gap:1.25rem;font-size:0.875rem;">
            {{-- Aquesta pàgina és anterior a saber quin tenant és (login
                 sense tenant encara) — no es pot construir un enllaç
                 correcte a "el" catàleg/login d'alumnat/professorat sense
                 saber de quina entitat. Es porta a l'inici, que ja mostra
                 les targetes d'accés per rol de l'entitat per defecte. --}}
            <a href="{{ url('/') }}"
               style="color:#6b7280;text-decoration:none;"
               onmouseover="this.style.color='#4338ca'" onmouseout="this.style.color='#6b7280'">Tornar al lloc</a>
        </div>
    </div>
</nav>
