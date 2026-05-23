@extends('campus.teacher.layouts.app')

@section('title', 'Crear nou curs · Pas 2')

@section('content')

{{-- Indicador de passos --}}
@include('campus.lms.teacher.wizard.partials.steps', ['current' => 2])

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:2rem;max-width:42rem;margin:0 auto;">

    {{-- Recordatori del curs --}}
    <div style="background:#f5f3ff;border:1px solid #ddd6fe;border-radius:0.5rem;padding:0.625rem 1rem;margin-bottom:1.5rem;font-size:0.875rem;color:#5b21b6;">
        📘 {{ $draft['title'] }}
        <span style="color:#7c3aed;margin-left:0.5rem;">· {{ $draft['sessions_count'] }} sessions</span>
    </div>

    <h1 style="font-size:1.25rem;font-weight:700;color:#111827;margin-bottom:0.25rem;">Títols de les sessions</h1>
    <p style="font-size:0.875rem;color:#6b7280;margin-bottom:1.75rem;">Posa un títol descriptiu a cada sessió. Podràs editar el contingut complet després.</p>

    @if ($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.875rem;">
            <ul style="margin:0;padding-left:1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.lms.wizard.store2') }}">
        @csrf

        <div style="display:flex;flex-direction:column;gap:0.75rem;margin-bottom:2rem;">
            @foreach ($sessionSlots as $i)
                <div style="display:flex;align-items:center;gap:0.75rem;">
                    <span style="min-width:1.5rem;text-align:center;font-size:0.8125rem;font-weight:700;color:#6b7280;">{{ $i }}</span>
                    <input type="text"
                           name="sessions[{{ $i }}][title]"
                           value="{{ old("sessions.{$i}.title", $sessionTitles[$i]['title'] ?? '') }}"
                           placeholder="Títol de la sessió {{ $i }}"
                           style="flex:1;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.9375rem;color:#111827;"
                           required>
                </div>
            @endforeach
        </div>

        <div style="display:flex;justify-content:space-between;">
            <a href="{{ route('teacher.lms.wizard.step1') }}"
               style="font-size:0.9375rem;color:#6b7280;text-decoration:none;padding:0.6rem 1rem;border:1px solid #d1d5db;border-radius:0.5rem;"
               onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='transparent'">
                ← Anterior
            </a>
            <button type="submit"
                    style="background:#4f46e5;color:#fff;font-size:0.9375rem;font-weight:600;padding:0.6rem 1.5rem;border:none;border-radius:0.5rem;cursor:pointer;"
                    onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                Revisar →
            </button>
        </div>
    </form>
</div>

@endsection
