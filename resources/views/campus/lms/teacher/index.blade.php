@extends('campus.teacher.layouts.app')

@section('title', 'Lliçons · ' . $course->title)

@section('content')

<div style="margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
    <a href="{{ route('teacher.portal.course', $course->slug) }}"
       style="font-size:0.875rem;color:#4f46e5;text-decoration:none;">&larr; Tornar al curs</a>

    <a href="{{ route('teacher.lms.lesson.create', $course->slug) }}"
       style="display:inline-flex;align-items:center;gap:0.4rem;background:#4f46e5;color:#fff;font-size:0.8125rem;font-weight:600;padding:0.45rem 1rem;border-radius:0.5rem;text-decoration:none;"
       onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
        + Nova sessió
    </a>
</div>

<div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1.5rem;">
    <span style="font-size:0.75rem;font-family:monospace;color:#9ca3af;">{{ $course->code }}</span>
    <h1 style="font-size:1.5rem;font-weight:700;color:#111827;margin:0.25rem 0 0.5rem;">
        {{ $course->title }}
    </h1>
    <p style="font-size:0.875rem;color:#6b7280;">Lliçons LMS — Vista del professorat</p>
</div>

@if (session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.875rem;">
        {{ session('success') }}
    </div>
@endif

{{-- Llista --}}
@if($lessons->isEmpty())
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:2rem;text-align:center;color:#6b7280;font-size:0.875rem;">
        Encara no hi ha lliçons. Crea la primera sessió amb el botó de dalt.
    </div>
@else
    <div style="display:flex;flex-direction:column;gap:0.625rem;">
        @foreach($lessons as $lesson)
        <div style="display:flex;align-items:center;gap:0.75rem;background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1rem 1.25rem;">

            {{-- Número --}}
            <div style="flex-shrink:0;width:2.25rem;height:2.25rem;background:#f3f4f6;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;">
                <span style="font-size:0.875rem;font-weight:700;color:#6b7280;">{{ $lesson->session_number }}</span>
            </div>

            {{-- Info (clicable a la previsualització) --}}
            <a href="{{ route('teacher.lms.lesson', [$course->slug, $lesson->id]) }}"
               style="flex:1;min-width:0;text-decoration:none;">
                <p style="font-size:0.9375rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $lesson->title }}
                </p>
                @if($lesson->subtitle)
                    <p style="font-size:0.8125rem;color:#9ca3af;margin-top:0.1rem;">{{ $lesson->subtitle }}</p>
                @endif
            </a>

            {{-- Durada --}}
            @if($lesson->duration)
                <span style="flex-shrink:0;font-size:0.8125rem;color:#9ca3af;background:#f9fafb;padding:0.2rem 0.625rem;border-radius:9999px;border:1px solid #f3f4f6;">
                    {{ $lesson->duration }}
                </span>
            @endif

            {{-- Estat --}}
            <span style="flex-shrink:0;font-size:0.75rem;padding:0.2rem 0.625rem;border-radius:9999px;font-weight:500;
                {{ $lesson->status === 'published' ? 'background:#dcfce7;color:#16a34a;' : 'background:#fef3c7;color:#d97706;' }}">
                {{ $lesson->status === 'published' ? 'Publicada' : 'Esborrany' }}
            </span>

            {{-- Botó editar --}}
            <a href="{{ route('teacher.lms.lesson.edit', [$course->slug, $lesson->id]) }}"
               style="flex-shrink:0;font-size:0.8rem;color:#4f46e5;border:1px solid #c7d2fe;border-radius:0.4rem;padding:0.25rem 0.625rem;text-decoration:none;font-weight:500;"
               onmouseover="this.style.background='#eef2ff'" onmouseout="this.style.background='transparent'">
                Editar
            </a>

            {{-- Botó eliminar --}}
            <form method="POST"
                  action="{{ route('teacher.lms.lesson.destroy', [$course->slug, $lesson->id]) }}"
                  onsubmit="return confirm('Eliminar «{{ addslashes($lesson->title) }}»? Aquesta acció no es pot desfer.')">
                @csrf @method('DELETE')
                <button type="submit"
                        style="font-size:0.8rem;color:#dc2626;border:1px solid #fecaca;background:transparent;border-radius:0.4rem;padding:0.25rem 0.625rem;cursor:pointer;font-weight:500;"
                        onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                    Eliminar
                </button>
            </form>
        </div>
        @endforeach
    </div>
@endif

@endsection
