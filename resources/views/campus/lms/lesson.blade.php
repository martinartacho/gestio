@extends('campus.layouts.app')

@section('title', 'Sessió ' . $lesson->session_number . ' · ' . $lesson->title)

@section('content')

<div style="max-width:42rem;margin:0 auto;">

{{-- Banner mode previsualització --}}
@if($isPreview ?? false)
<div style="background:#fef9c3;border:1px solid #fde047;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1.5rem;font-size:0.875rem;color:#713f12;display:flex;align-items:center;gap:0.5rem;">
    <span>👁</span>
    <strong>Mode previsualització</strong> — Aquesta és la vista de l'alumne. Les accions de progrés estan desactivades.
</div>
@endif

{{-- ═══════════════════════════════════════════════════
     BLOC 1 — CAPÇALERA
═══════════════════════════════════════════════════ --}}
<div style="border-bottom:1px solid #e5e7eb;padding-bottom:1.5rem;margin-bottom:2rem;">
    <a href="{{ isset($isPreview) && $isPreview ? 'javascript:history.back()' : route('campus.lms.course', $course->slug) }}"
       style="font-size:0.875rem;color:#4f46e5;text-decoration:none;">&larr; Tornar al curs</a>

    <div style="display:flex;align-items:center;gap:0.75rem;margin-top:1.25rem;margin-bottom:0.5rem;">
        <span style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.1em;background:#e0e7ff;color:#4338ca;padding:0.25rem 0.75rem;border-radius:9999px;font-weight:500;">
            Sessió {{ $lesson->session_number }}
            @if(isset($totalLessons) && $totalLessons > 1) · de {{ $totalLessons }} @endif
        </span>
        @if($isCompleted ?? false)
            <span style="font-size:0.6875rem;background:#dcfce7;color:#16a34a;padding:0.25rem 0.75rem;border-radius:9999px;font-weight:500;">✓ Completada</span>
        @endif
    </div>

    <h1 style="font-size:1.875rem;font-weight:700;color:#111827;line-height:1.25;margin-bottom:0.375rem;">
        {{ $lesson->title }}
    </h1>

    @if($lesson->subtitle)
        <p style="font-size:1rem;color:#6b7280;">{{ $lesson->subtitle }}</p>
    @endif

    @if($lesson->duration)
        <span style="display:inline-block;margin-top:0.75rem;font-size:0.8125rem;color:#6b7280;background:#f3f4f6;padding:0.25rem 0.75rem;border-radius:9999px;">
            ⏱ {{ $lesson->duration }}
        </span>
    @endif
</div>

{{-- ═══════════════════════════════════════════════════
     BLOC 2 — INTRODUCCIÓ (cita + text)
═══════════════════════════════════════════════════ --}}
@include('campus.lms.partials.cover-image', ['position' => 'after_quote'])

@if($lesson->quote_text)
<p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.12em;color:#9ca3af;margin-bottom:0.75rem;">Introducció</p>
<blockquote style="border-left:3px solid #4f46e5;padding:0.875rem 1.25rem;background:#f5f3ff;border-radius:0 0.5rem 0.5rem 0;margin:0 0 1.5rem;">
    <p style="font-style:italic;font-size:1.0625rem;color:#1e1b4b;line-height:1.7;">
        «{{ $lesson->quote_text }}»
    </p>
    @if($lesson->quote_author)
        <footer style="margin-top:0.5rem;font-size:0.8125rem;color:#6b7280;">
            — {{ $lesson->quote_author }}@if($lesson->quote_work), <em>{{ $lesson->quote_work }}</em>@endif
        </footer>
    @endif
</blockquote>
@endif

@if($lesson->intro_text)
    <p style="font-size:1rem;line-height:1.8;color:#374151;margin-bottom:2rem;">{{ $lesson->intro_text }}</p>
@endif

@include('campus.lms.partials.cover-image', ['position' => 'after_intro'])

{{-- ═══════════════════════════════════════════════════
     BLOC 3 — EL TEMA D'AVUI
═══════════════════════════════════════════════════ --}}
@if($lesson->topic_text)
<div style="margin-bottom:2rem;">
    <p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.12em;color:#9ca3af;margin-bottom:0.75rem;">El tema d'avui</p>
    <p style="font-size:1rem;line-height:1.8;color:#374151;">{{ $lesson->topic_text }}</p>
</div>
@endif

@include('campus.lms.partials.cover-image', ['position' => 'after_topic'])

@include('campus.lms.partials.cover-image', ['position' => 'before_concepts'])

{{-- ═══════════════════════════════════════════════════
     BLOCS 4–7 — CONCEPTES I TEXTOS (intercalats per ordre)
═══════════════════════════════════════════════════ --}}
@php
    $concepts  = $lesson->concepts  ?? [];
    $textCards = $lesson->text_cards ?? [];
    $maxBlocks = max(count($concepts), count($textCards));
    // Intercalem: concepte[0], text[0], concepte[1], text[1]...
@endphp

@for($i = 0; $i < $maxBlocks; $i++)

    {{-- Concepte clau --}}
    @if(isset($concepts[$i]))
    @php $c = $concepts[$i]; @endphp
    <div style="display:flex;gap:1rem;align-items:flex-start;padding:1.25rem;border:1px solid #e0e7ff;border-radius:0.75rem;background:#fafafa;margin-bottom:1.25rem;">
        <div style="flex-shrink:0;width:2.25rem;height:2.25rem;background:#4f46e5;border-radius:0.5rem;display:flex;align-items:center;justify-content:center;">
            <span style="color:#fff;font-size:1rem;">◆</span>
        </div>
        <div>
            <p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.1em;color:#6366f1;font-weight:600;margin-bottom:0.25rem;">
                Concepte clau
            </p>
            <p style="font-size:0.9375rem;font-weight:600;color:#111827;margin-bottom:0.25rem;">
                {{ $c['title'] ?? '' }}
            </p>
            <p style="font-size:0.875rem;color:#6b7280;line-height:1.65;">
                {{ $c['description'] ?? '' }}
            </p>
        </div>
    </div>
    @endif

    {{-- Targeta de text --}}
    @if(isset($textCards[$i]))
    @php $card = $textCards[$i]; $isProject = ($card['type'] ?? '') === 'project'; @endphp
    <div style="border:1px solid {{ $isProject ? '#ddd6fe' : '#e5e7eb' }};border-radius:0.75rem;padding:1.25rem 1.5rem;margin-bottom:1.75rem;background:#fff;">
        <div style="display:flex;align-items:flex-start;justify-content:space-between;gap:0.75rem;margin-bottom:0.875rem;flex-wrap:wrap;">
            <div>
                <p style="font-size:1rem;font-weight:600;color:#111827;line-height:1.3;">
                    {{ $card['title'] ?? '' }}
                </p>
                @if(!empty($card['author']))
                    <p style="font-size:0.8125rem;color:#9ca3af;margin-top:0.2rem;">
                        {{ $card['author'] }}@if(!empty($card['year'])) · {{ $card['year'] }}@endif
                    </p>
                @endif
            </div>
            <span style="flex-shrink:0;font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.08em;padding:0.25rem 0.75rem;border-radius:9999px;font-weight:500;
                {{ $isProject ? 'background:#ede9fe;color:#5b21b6;' : 'background:#dcfce7;color:#166534;' }}">
                {{ $isProject ? 'Projecte' : 'Referència' }}
            </span>
        </div>

        @if(!empty($card['analysis']))
            <p style="font-size:0.875rem;line-height:1.75;color:#4b5563;margin-bottom:0.875rem;">
                {!! nl2br(e($card['analysis'])) !!}
            </p>
        @endif

        @if(!empty($card['extract']))
            <blockquote style="border-top:1px solid #f3f4f6;padding-top:0.875rem;font-style:italic;font-size:0.9375rem;color:#6b7280;line-height:1.75;">
                {{ $card['extract'] }}
            </blockquote>
        @endif
    </div>
    @endif

@endfor

{{-- ═══════════════════════════════════════════════════
     BLOC 8 — COMPARACIÓ
═══════════════════════════════════════════════════ --}}
@if($lesson->comparison && !empty($lesson->comparison['left_label']))
@php $cmp = $lesson->comparison; @endphp
<div style="margin:2rem 0;">
    <p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.12em;color:#9ca3af;margin-bottom:0.875rem;">Comparació</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
        <div style="background:#f5f3ff;border-radius:0.75rem;padding:1.25rem;">
            <p style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:#6366f1;font-weight:600;margin-bottom:0.75rem;">
                {{ $cmp['left_label'] ?? '' }}
            </p>
            @foreach($cmp['left_points'] ?? [] as $point)
                <p style="font-size:0.875rem;color:#312e81;padding:0.3rem 0;border-bottom:1px solid rgba(99,102,241,0.15);">
                    {{ is_array($point) ? ($point['point'] ?? '') : $point }}
                </p>
            @endforeach
        </div>
        <div style="background:#f0fdf4;border-radius:0.75rem;padding:1.25rem;">
            <p style="font-size:0.75rem;text-transform:uppercase;letter-spacing:0.08em;color:#16a34a;font-weight:600;margin-bottom:0.75rem;">
                {{ $cmp['right_label'] ?? '' }}
            </p>
            @foreach($cmp['right_points'] ?? [] as $point)
                <p style="font-size:0.875rem;color:#14532d;padding:0.3rem 0;border-bottom:1px solid rgba(22,163,74,0.15);">
                    {{ is_array($point) ? ($point['point'] ?? '') : $point }}
                </p>
            @endforeach
        </div>
    </div>
</div>
@endif

<hr style="border:none;border-top:1px solid #f3f4f6;margin:2rem 0;">

@include('campus.lms.partials.cover-image', ['position' => 'before_reflection'])

{{-- ═══════════════════════════════════════════════════
     BLOC 9 — REFLEXIÓ
═══════════════════════════════════════════════════ --}}
@if($lesson->reflection_questions)
<div style="background:#fffbeb;border:1px solid #fde68a;border-radius:0.75rem;padding:1.5rem;margin-bottom:2rem;">
    <p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.12em;color:#92400e;font-weight:600;margin-bottom:1rem;">
        Reflexió · Per parlar en grup
    </p>
    @foreach($lesson->reflection_questions as $rq)
        <div style="display:flex;gap:0.75rem;padding:0.5rem 0;border-bottom:1px solid rgba(251,191,36,0.2);">
            <span style="color:#d97706;flex-shrink:0;font-weight:700;">—</span>
            <p style="font-size:0.9375rem;line-height:1.7;color:#78350f;">
                {{ is_array($rq) ? ($rq['question'] ?? '') : $rq }}
            </p>
        </div>
    @endforeach

    {{-- Preguntes interactives del bloc 'reflection' --}}
    @if(!($isPreview ?? false))
        @foreach($lesson->getQuestionsOfBlock('reflection') as $q)
            @include('campus.lms.partials.question-form', [
                'question'  => $q,
                'response'  => $studentResponses[$q['index']] ?? null,
                'lesson'    => $lesson,
            ])
        @endforeach
    @endif
</div>
@endif

@include('campus.lms.partials.cover-image', ['position' => 'before_exercise'])

{{-- ═══════════════════════════════════════════════════
     BLOC 10 — EXERCICI PRÀCTIC
═══════════════════════════════════════════════════ --}}
@if($lesson->exercise)
@php $ex = $lesson->exercise; @endphp
<div style="border:2px solid #4f46e5;border-radius:0.75rem;padding:1.5rem;margin-bottom:2rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;gap:0.75rem;margin-bottom:1rem;flex-wrap:wrap;">
        <div style="display:flex;align-items:center;gap:0.75rem;">
            <span style="font-size:1.25rem;">✏️</span>
            <p style="font-size:1rem;font-weight:700;color:#4f46e5;">
                {{ $ex['title'] ?? 'Exercici pràctic' }}
            </p>
        </div>
        @if(!empty($ex['duration']))
            <span style="font-size:0.8125rem;background:#e0e7ff;color:#4338ca;padding:0.25rem 0.875rem;border-radius:9999px;">
                ⏱ {{ $ex['duration'] }}
            </span>
        @endif
    </div>

    @if(!empty($ex['statement']))
        <p style="font-size:0.9375rem;line-height:1.8;color:#374151;margin-bottom:1rem;">{{ $ex['statement'] }}</p>
    @endif

    @if(!empty($ex['examples']))
        <div style="background:#f5f3ff;border-radius:0.5rem;padding:1rem;margin-bottom:1rem;">
            <p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.08em;color:#6366f1;font-weight:600;margin-bottom:0.5rem;">
                Opcions possibles
            </p>
            @foreach($ex['examples'] as $example)
                <p style="font-size:0.875rem;color:#374151;padding:0.2rem 0;">
                    · {{ is_array($example) ? ($example['item'] ?? '') : $example }}
                </p>
            @endforeach
        </div>
    @endif

    {{-- Demo 2 columnes --}}
    @if(!empty($ex['demo_first_person']) || !empty($ex['demo_third_person']))
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.875rem;margin-bottom:1rem;">
            @if(!empty($ex['demo_first_person']))
            <div style="background:#f9fafb;border-radius:0.5rem;padding:1rem;">
                <p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.08em;color:#9ca3af;font-weight:600;margin-bottom:0.5rem;">
                    1a persona
                </p>
                <p style="font-style:italic;font-size:0.875rem;color:#4b5563;line-height:1.65;">
                    {{ $ex['demo_first_person'] }}
                </p>
            </div>
            @endif
            @if(!empty($ex['demo_third_person']))
            <div style="background:#f9fafb;border-radius:0.5rem;padding:1rem;">
                <p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.08em;color:#9ca3af;font-weight:600;margin-bottom:0.5rem;">
                    3a persona
                </p>
                <p style="font-style:italic;font-size:0.875rem;color:#4b5563;line-height:1.65;">
                    {{ $ex['demo_third_person'] }}
                </p>
            </div>
            @endif
        </div>
    @endif

    @if(!empty($ex['tips']))
        <div style="border-top:1px solid #e0e7ff;padding-top:0.875rem;margin-top:0.25rem;">
            <p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.08em;color:#9ca3af;font-weight:600;margin-bottom:0.5rem;">
                Consells
            </p>
            @foreach($ex['tips'] as $tip)
                <p style="font-size:0.875rem;color:#6b7280;padding:0.2rem 0;">
                    · {{ is_array($tip) ? ($tip['tip'] ?? '') : $tip }}
                </p>
            @endforeach
        </div>
    @endif

    {{-- Preguntes interactives del bloc 'exercise' --}}
    @if(!($isPreview ?? false))
        @foreach($lesson->getQuestionsOfBlock('exercise') as $q)
            @include('campus.lms.partials.question-form', [
                'question'  => $q,
                'response'  => $studentResponses[$q['index']] ?? null,
                'lesson'    => $lesson,
            ])
        @endforeach
    @endif
</div>
@endif

{{-- ═══════════════════════════════════════════════════
     BLOC 11 — QÜESTIONARI (preguntes avaluables)
═══════════════════════════════════════════════════ --}}
@php $quizQuestions = $lesson->getQuestionsOfBlock('quiz'); @endphp
@if(!($isPreview ?? false) && count($quizQuestions) > 0)
<div style="border:1px solid #c7d2fe;border-radius:0.75rem;padding:1.5rem;margin-bottom:2rem;background:#f8f9ff;">
    <p style="font-size:0.6875rem;text-transform:uppercase;letter-spacing:0.12em;color:#4338ca;font-weight:600;margin-bottom:0.25rem;">
        Qüestionari
    </p>
    <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1.25rem;">
        Comprova el que has après en aquesta sessió.
    </p>
    @foreach($quizQuestions as $q)
        @include('campus.lms.partials.question-form', [
            'question'  => $q,
            'response'  => $studentResponses[$q['index']] ?? null,
            'lesson'    => $lesson,
        ])
    @endforeach
</div>
@endif

{{-- ═══════════════════════════════════════════════════
     NAVEGACIÓ + BOTÓ "MARCAR COMPLETADA"
═══════════════════════════════════════════════════ --}}
<div style="display:flex;align-items:center;justify-content:space-between;padding-top:1.5rem;border-top:1px solid #e5e7eb;margin-top:1rem;gap:1rem;flex-wrap:wrap;">

    {{-- Anterior --}}
    @if($prevLesson)
        <a href="{{ route('campus.lms.lesson', [$course->slug, $prevLesson->id]) }}"
           style="font-size:0.875rem;color:#4f46e5;text-decoration:none;display:flex;align-items:center;gap:0.25rem;">
            ← <span>Sessió {{ $prevLesson->session_number }}</span>
        </a>
    @else
        <span></span>
    @endif

    {{-- Completar / completat --}}
    @if(!($isPreview ?? false))
        @if($isCompleted)
            <span style="font-size:0.875rem;color:#16a34a;font-weight:600;display:flex;align-items:center;gap:0.375rem;">
                ✓ Completada
            </span>
        @else
            <form method="POST" action="{{ route('campus.lms.lesson.complete', $lesson->id) }}">
                @csrf
                <button type="submit"
                        style="background:#4f46e5;color:#fff;border:none;border-radius:0.5rem;padding:0.625rem 1.5rem;font-size:0.875rem;font-weight:600;cursor:pointer;">
                    Marcar com a completada
                </button>
            </form>
        @endif
    @else
        <span style="font-size:0.8125rem;color:#9ca3af;font-style:italic;">Previsualització</span>
    @endif

    {{-- Següent --}}
    @if($nextLesson)
        <a href="{{ route('campus.lms.lesson', [$course->slug, $nextLesson->id]) }}"
           style="font-size:0.875rem;color:#4f46e5;text-decoration:none;display:flex;align-items:center;gap:0.25rem;">
            <span>Sessió {{ $nextLesson->session_number }}</span> →
        </a>
    @else
        <span></span>
    @endif

</div>

</div>{{-- /max-width --}}
@endsection
