<footer style="border-top:1px solid #e5e7eb;background:#fff;margin-top:auto;">
    <div style="max-width:72rem;margin:0 auto;padding:1.5rem 1rem;display:flex;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;gap:1rem;">

        {{-- Columna 1: Identificació del campus --}}
        <div style="display:flex;flex-direction:column;gap:0.25rem;font-size:0.75rem;color:#9ca3af;">
            <a href="{{ url('/') }}"
               style="font-size:0.875rem;font-weight:600;color:#4b5563;text-decoration:none;"
               onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#4b5563'">
                {{ setting('campus_name', 'Campus de Formació Continuada') }}
            </a>
            @if(setting('campus_contact_email'))
                <a href="mailto:{{ setting('campus_contact_email') }}"
                   style="color:#9ca3af;text-decoration:none;"
                   onmouseover="this.style.color='#4f46e5'" onmouseout="this.style.color='#9ca3af'">
                    {{ setting('campus_contact_email') }}
                </a>
            @endif
            @if(setting('campus_contact_phone'))
                <span>{{ setting('campus_contact_phone') }}</span>
            @endif
            @if(setting('campus_address'))
                <span>{{ setting('campus_address') }}</span>
            @endif
        </div>

        {{-- Columna 2: Menús de navegació --}}
        @php $campusActiu = setting('campus_enabled', true) || auth('web')->check(); @endphp
        @php $associatsActiu = setting('associats_enabled', false) || auth('web')->check(); @endphp
        <nav style="display:flex;align-items:center;gap:0;font-size:0.75rem;">
            @if($campusActiu)
            <a href="{{ route('campus.catalog.index') }}"
               style="color:#9ca3af;text-decoration:none;padding:0 0.75rem;border-right:1px solid #e5e7eb;"
               onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">Catàleg</a>
            <a href="{{ route('campus.login') }}"
               style="color:#9ca3af;text-decoration:none;padding:0 0.75rem;border-right:1px solid #e5e7eb;"
               onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">Alumnat</a>
            <a href="{{ route('teacher.login') }}"
               style="color:#9ca3af;text-decoration:none;padding:0 0.75rem;border-right:1px solid #e5e7eb;"
               onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">Professorat</a>
            @endif
            @if($associatsActiu)
            <a href="{{ route('member.login') }}"
               style="color:#9ca3af;text-decoration:none;padding:0 0.75rem;border-right:1px solid #e5e7eb;"
               onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">Socis</a>
            @endif
            <a href="{{ route('campus.noticies') }}"
               style="color:#9ca3af;text-decoration:none;padding:0 0.75rem;border-right:1px solid #e5e7eb;"
               onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">Notícies</a>
            <a href="/admin"
               style="color:#9ca3af;text-decoration:none;padding:0 0.75rem;"
               onmouseover="this.style.color='#374151'" onmouseout="this.style.color='#9ca3af'">Administració</a>
        </nav>

    </div>
</footer>
