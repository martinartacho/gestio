@extends('campus.layouts.app')

@section('title', 'Lliçons · ' . $course->title)

@section('content')

{{-- Capçalera del curs --}}
<div style="margin-bottom:2rem;">
    <a href="{{ route('campus.portal.courses') }}"
       style="font-size:0.875rem;color:#4f46e5;text-decoration:none;">&larr; Els meus cursos</a>
    <h1 style="font-size:1.75rem;font-weight:700;color:#111827;margin:0.75rem 0 0.25rem;">
        {{ $course->title }}
    </h1>
    <p style="font-size:0.875rem;color:#6b7280;">Lliçons del curs</p>
</div>

{{-- Barra de progrés --}}
<div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.25rem 1.5rem;margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
        <span style="font-size:0.875rem;font-weight:500;color:#374151;">Progrés del curs</span>
        <span style="font-size:0.875rem;color:#6b7280;">{{ $done }} de {{ $total }} lliçons completades</span>
    </div>
    <div style="background:#f3f4f6;border-radius:9999px;height:0.625rem;overflow:hidden;">
        <div style="background:#4f46e5;height:100%;border-radius:9999px;width:{{ $progress }}%;transition:width 0.3s ease;"></div>
    </div>
    @if($progress === 100)
        <p style="font-size:0.8125rem;color:#16a34a;font-weight:500;margin-top:0.5rem;">🎉 Curs completat!</p>
    @endif
</div>

{{-- Certificat --}}
@if($certificate ?? null)
<div style="background:#ecfdf5;border:1px solid #a7f3d0;border-radius:0.75rem;padding:1.25rem 1.5rem;margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
    <div style="display:flex;align-items:center;gap:0.75rem;">
        <span style="font-size:1.5rem;">🎓</span>
        <div>
            <p style="font-size:0.9375rem;font-weight:600;color:#065f46;margin:0;">Certificat d'aprofitament</p>
            <p style="font-size:0.8125rem;color:#6b7280;margin:0.125rem 0 0;">
                Emès el {{ $certificate->issued_at->format('d/m/Y') }} · Núm. {{ $certificate->certificate_number }}
            </p>
        </div>
    </div>
    <a href="{{ route('campus.lms.course.certificate', $course->slug) }}"
       style="display:inline-flex;align-items:center;gap:0.4rem;background:#059669;color:#fff;font-size:0.8125rem;font-weight:600;padding:0.5rem 1.125rem;border-radius:0.5rem;text-decoration:none;"
       onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
        ↓ Veure el certificat
    </a>
</div>
@endif

{{-- Llista de lliçons --}}
@if($lessons->isEmpty())
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:2rem;text-align:center;color:#6b7280;">
        Encara no hi ha lliçons publicades per a aquest curs.
    </div>
@else
    <div style="display:flex;flex-direction:column;gap:0.75rem;">
        @foreach($lessons as $lesson)
        @php $completed = in_array($lesson->id, $completedIds); @endphp
        <a href="{{ route('campus.lms.lesson', [$course->slug, $lesson->id]) }}"
           style="display:flex;align-items:center;gap:1.25rem;background:#fff;border:1px solid {{ $completed ? '#bbf7d0' : '#e5e7eb' }};border-radius:0.75rem;padding:1.25rem 1.5rem;text-decoration:none;transition:box-shadow 0.15s;"
           onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">

            {{-- Indicador completat --}}
            <div style="flex-shrink:0;width:2.5rem;height:2.5rem;border-radius:50%;display:flex;align-items:center;justify-content:center;
                {{ $completed ? 'background:#dcfce7;border:2px solid #16a34a;' : 'background:#f3f4f6;border:2px solid #d1d5db;' }}">
                @if($completed)
                    <span style="color:#16a34a;font-size:1rem;font-weight:700;">✓</span>
                @else
                    <span style="color:#9ca3af;font-size:0.875rem;font-weight:600;">{{ $lesson->session_number }}</span>
                @endif
            </div>

            {{-- Info --}}
            <div style="flex:1;min-width:0;">
                <div style="display:flex;align-items:center;gap:0.5rem;margin-bottom:0.125rem;">
                    <span style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.08em;color:#9ca3af;">
                        Sessió {{ $lesson->session_number }}
                    </span>
                    @if($completed)
                        <span style="font-size:0.6875rem;background:#dcfce7;color:#16a34a;padding:0.125rem 0.5rem;border-radius:9999px;font-weight:500;">
                            Completada
                        </span>
                    @endif
                </div>
                <p style="font-size:1rem;font-weight:600;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $lesson->title }}
                </p>
                @if($lesson->subtitle)
                    <p style="font-size:0.8125rem;color:#6b7280;margin-top:0.125rem;">{{ $lesson->subtitle }}</p>
                @endif
            </div>

            {{-- Durada --}}
            @if($lesson->duration)
            <div style="flex-shrink:0;text-align:right;">
                <span style="font-size:0.8125rem;color:#9ca3af;background:#f9fafb;border:1px solid #f3f4f6;padding:0.25rem 0.75rem;border-radius:9999px;">
                    {{ $lesson->duration }}
                </span>
            </div>
            @endif

            {{-- Arrow --}}
            <div style="flex-shrink:0;color:#9ca3af;">›</div>
        </a>
        @endforeach
    </div>
@endif

@endsection
