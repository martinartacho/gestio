@extends('campus.teacher.layouts.app')

@section('title', 'Crear nou curs · Revisió')

@section('content')

{{-- Indicador de passos --}}
@include('campus.lms.teacher.wizard.partials.steps', ['current' => 3])

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:2rem;max-width:42rem;margin:0 auto;">
    <h1 style="font-size:1.25rem;font-weight:700;color:#111827;margin-bottom:0.25rem;">Revisa el teu curs</h1>
    <p style="font-size:0.875rem;color:#6b7280;margin-bottom:1.75rem;">Comprova que tot és correcte abans de crear el curs.</p>

    {{-- Resum del curs --}}
    <div style="background:#f9fafb;border:1px solid #e5e7eb;border-radius:0.625rem;padding:1.25rem;margin-bottom:1.5rem;">
        <dl style="display:grid;grid-template-columns:9rem 1fr;gap:0.625rem;font-size:0.9rem;margin:0;">
            <dt style="color:#6b7280;font-weight:600;">Títol</dt>
            <dd style="color:#111827;margin:0;">{{ $draft['title'] }}</dd>

            <dt style="color:#6b7280;font-weight:600;">Format</dt>
            <dd style="color:#111827;margin:0;">{{ $formats[$draft['format']] }}</dd>

            <dt style="color:#6b7280;font-weight:600;">Temporada</dt>
            <dd style="color:#111827;margin:0;">{{ $season?->name ?? '—' }}</dd>

            <dt style="color:#6b7280;font-weight:600;">Sessions</dt>
            <dd style="color:#111827;margin:0;">{{ count($sessionTitles) }}</dd>

            @if (!empty($draft['description']))
                <dt style="color:#6b7280;font-weight:600;">Descripció</dt>
                <dd style="color:#111827;margin:0;">{{ $draft['description'] }}</dd>
            @endif

            @if (!empty($draft['objectives']))
                <dt style="color:#6b7280;font-weight:600;">Objectius</dt>
                <dd style="color:#111827;margin:0;">{{ $draft['objectives'] }}</dd>
            @endif

            <dt style="color:#6b7280;font-weight:600;">Estat inicial</dt>
            <dd style="margin:0;">
                <span style="background:#f3f4f6;color:#6b7280;font-size:0.8rem;font-weight:600;padding:0.2rem 0.6rem;border-radius:999px;">
                    Esborrany
                </span>
            </dd>
        </dl>
    </div>

    {{-- Llistat de sessions --}}
    <h2 style="font-size:0.9rem;font-weight:700;color:#374151;margin-bottom:0.75rem;">Sessions</h2>
    <ol style="margin:0 0 1.75rem;padding-left:1.5rem;display:flex;flex-direction:column;gap:0.375rem;">
        @foreach ($sessionTitles as $i => $session)
            <li style="font-size:0.9rem;color:#374151;">{{ $session['title'] }}</li>
        @endforeach
    </ol>

    {{-- Nota informativa --}}
    <div style="background:#eff6ff;border:1px solid #bfdbfe;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:2rem;font-size:0.8125rem;color:#1d4ed8;">
        ℹ️ Un cop creat, l'administrador revisarà el curs i l'activarà. Fins aleshores estarà en estat <strong>Esborrany</strong>.
    </div>

    <div style="display:flex;justify-content:space-between;align-items:center;">
        <a href="{{ route('teacher.lms.wizard.step2') }}"
           style="font-size:0.9375rem;color:#6b7280;text-decoration:none;padding:0.6rem 1rem;border:1px solid #d1d5db;border-radius:0.5rem;"
           onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
            ← Tornar
        </a>

        <form method="POST" action="{{ route('teacher.lms.wizard.confirm') }}">
            @csrf
            <button type="submit"
                    style="background:#059669;color:#fff;font-size:0.9375rem;font-weight:600;padding:0.6rem 1.5rem;border:none;border-radius:0.5rem;cursor:pointer;"
                    onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                ✓ Crear curs
            </button>
        </form>
    </div>
</div>

@endsection
