@extends('campus.teacher.layouts.app')

@section('title', 'Crear nou curs · Pas 1')

@section('content')

{{-- Indicador de passos --}}
@include('campus.lms.teacher.wizard.partials.steps', ['current' => 1])

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:2rem;max-width:42rem;margin:0 auto;">
    <h1 style="font-size:1.25rem;font-weight:700;color:#111827;margin-bottom:0.25rem;">Dades bàsiques del curs</h1>
    <p style="font-size:0.875rem;color:#6b7280;margin-bottom:1.75rem;">Defineix la informació principal. Podràs ampliar-la més endavant.</p>

    @if ($errors->any())
        <div style="background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.875rem;">
            <ul style="margin:0;padding-left:1.25rem;">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('teacher.lms.wizard.store1') }}">
        @csrf

        {{-- Títol --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.375rem;">
                Títol del curs <span style="color:#ef4444;">*</span>
            </label>
            <input type="text" name="title" value="{{ old('title', $draft['title'] ?? '') }}"
                   placeholder="p.ex. Introducció a la fotografia analògica"
                   style="width:100%;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.9375rem;color:#111827;box-sizing:border-box;"
                   required>
        </div>

        {{-- Format i Temporada (2 columnes) --}}
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.25rem;">
            <div>
                <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.375rem;">
                    Format <span style="color:#ef4444;">*</span>
                </label>
                <select name="format"
                        style="width:100%;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.9375rem;color:#111827;background:#fff;box-sizing:border-box;"
                        required>
                    @foreach (\App\Models\CampusCourse::FORMATS as $key => $label)
                        <option value="{{ $key }}" {{ old('format', $draft['format'] ?? 'online') === $key ? 'selected' : '' }}>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div>
                <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.375rem;">
                    Temporada <span style="color:#ef4444;">*</span>
                </label>
                <select name="season_id"
                        style="width:100%;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.9375rem;color:#111827;background:#fff;box-sizing:border-box;"
                        required>
                    <option value="">-- Selecciona --</option>
                    @foreach ($seasons as $season)
                        <option value="{{ $season->id }}" {{ old('season_id', $draft['season_id'] ?? '') == $season->id ? 'selected' : '' }}>
                            {{ $season->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Nombre de sessions --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.375rem;">
                Nombre de sessions <span style="color:#ef4444;">*</span>
            </label>
            <input type="number" name="sessions_count" min="1" max="30"
                   value="{{ old('sessions_count', $draft['sessions_count'] ?? 6) }}"
                   style="width:8rem;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.9375rem;color:#111827;"
                   required>
            <p style="font-size:0.8rem;color:#9ca3af;margin:0.3rem 0 0;">Entre 1 i 30 sessions.</p>
        </div>

        {{-- Descripció --}}
        <div style="margin-bottom:1.25rem;">
            <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.375rem;">
                Descripció breu
            </label>
            <textarea name="description" rows="3"
                      placeholder="De qué tracta el curs? A qui va dirigit?"
                      style="width:100%;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.9375rem;color:#111827;resize:vertical;box-sizing:border-box;">{{ old('description', $draft['description'] ?? '') }}</textarea>
        </div>

        {{-- Objectius --}}
        <div style="margin-bottom:2rem;">
            <label style="display:block;font-size:0.8125rem;font-weight:600;color:#374151;margin-bottom:0.375rem;">
                Objectius del curs
            </label>
            <textarea name="objectives" rows="3"
                      placeholder="Què aprendran els alumnes en acabar el curs?"
                      style="width:100%;border:1px solid #d1d5db;border-radius:0.5rem;padding:0.5rem 0.75rem;font-size:0.9375rem;color:#111827;resize:vertical;box-sizing:border-box;">{{ old('objectives', $draft['objectives'] ?? '') }}</textarea>
        </div>

        <div style="display:flex;justify-content:flex-end;">
            <button type="submit"
                    style="background:#4f46e5;color:#fff;font-size:0.9375rem;font-weight:600;padding:0.6rem 1.5rem;border:none;border-radius:0.5rem;cursor:pointer;"
                    onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                Següent → Definir sessions
            </button>
        </div>
    </form>
</div>

@endsection
