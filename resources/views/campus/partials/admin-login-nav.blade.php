<nav style="background:#fff;border-bottom:1px solid #e5e7eb;padding:0.75rem 1rem;">
    <div style="max-width:72rem;margin:0 auto;display:flex;align-items:center;justify-content:space-between;">
        <a href="{{ url('/') }}"
           style="font-size:1.125rem;font-weight:700;color:#4338ca;text-decoration:none;"
           onmouseover="this.style.color='#3730a3'" onmouseout="this.style.color='#4338ca'">
            {{ setting('campus_name', 'Campus') }}
        </a>
        <div style="display:flex;align-items:center;gap:1.25rem;font-size:0.875rem;">
            <a href="{{ route('campus.catalog.index') }}"
               style="color:#6b7280;text-decoration:none;"
               onmouseover="this.style.color='#4338ca'" onmouseout="this.style.color='#6b7280'">Catàleg</a>
            <a href="{{ route('campus.login') }}"
               style="color:#6b7280;text-decoration:none;"
               onmouseover="this.style.color='#4338ca'" onmouseout="this.style.color='#6b7280'">Alumnat</a>
            <a href="{{ route('teacher.login') }}"
               style="color:#6b7280;text-decoration:none;"
               onmouseover="this.style.color='#4338ca'" onmouseout="this.style.color='#6b7280'">Professorat</a>
        </div>
    </div>
</nav>
