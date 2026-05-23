{{--
  Partial: question-form
  Variables:
    $question  array  — definició de la pregunta (de questions[])
    $response  LmsLessonResponse|null  — resposta guardada (si n'hi ha)
    $lesson    LmsLesson
--}}
@php
    $qIndex   = $question['index'];
    $qType    = $question['type'] ?? 'open_text';
    $qText    = $question['text'] ?? '';
    $qOptions = $question['options'] ?? [];
    $qPoints  = (int)($question['points'] ?? 0);
    $qReq     = $question['required'] ?? false;
    $hasResp  = $response && $response->hasResponse();
@endphp

<div id="q-{{ $qIndex }}" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:0.625rem;padding:1rem 1.25rem;margin-bottom:1rem;scroll-margin-top:1.5rem;">

    {{-- Enunciat --}}
    <p style="font-size:0.9rem;font-weight:600;color:#1e293b;margin:0 0 0.75rem 0;">
        {{ $qText }}
        @if($qReq)<span style="color:#ef4444;margin-left:0.2rem;">*</span>@endif
        @if($qPoints > 0)<span style="font-size:0.75rem;font-weight:400;color:#64748b;margin-left:0.5rem;">({{ $qPoints }} {{ $qPoints === 1 ? 'punt' : 'punts' }})</span>@endif
    </p>

    {{-- Resposta guardada --}}
    @if($hasResp)
        <div style="font-size:0.82rem;background:#ecfdf5;border:1px solid #a7f3d0;border-radius:0.4rem;padding:0.5rem 0.75rem;margin-bottom:0.75rem;display:flex;align-items:center;gap:0.5rem;">
            <span style="color:#10b981;font-weight:700;">✓</span>
            <span style="color:#065f46;">
                Resposta desada: <em>{{ $response->getDisplayValue() }}</em>
            </span>
            @if($response->auto_graded && $response->score !== null)
                @if($response->isCorrect())
                    <span style="margin-left:auto;color:#059669;font-weight:600;font-size:0.8rem;">✓ Correcte</span>
                @else
                    <span style="margin-left:auto;color:#dc2626;font-weight:600;font-size:0.8rem;">✗ Incorrecte</span>
                @endif
            @endif
        </div>
    @endif

    {{-- Formulari --}}
    <form method="POST"
          action="{{ route('campus.lms.lesson.response.save', ['lesson' => $lesson->id, 'questionIndex' => $qIndex]) }}"
          style="margin:0;">
        @csrf

        @if($qType === 'open_text')
            <textarea name="answer"
                      rows="4"
                      placeholder="Escriu la teva resposta..."
                      style="width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:0.4rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#1e293b;resize:vertical;font-family:inherit;">{{ old('answer', $hasResp ? $response->response_text : '') }}</textarea>

        @elseif($qType === 'select_from_examples')
            <div style="display:flex;flex-direction:column;gap:0.4rem;margin-bottom:0.5rem;">
                @foreach($qOptions as $opt)
                    <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;color:#334155;cursor:pointer;">
                        <input type="radio" name="answer" value="{{ $opt }}"
                               {{ ($hasResp && $response->response_text === $opt) ? 'checked' : '' }}
                               onchange="document.getElementById('custom-text-{{ $qIndex }}').style.display='none';"
                               style="accent-color:#4f46e5;">
                        {{ $opt }}
                    </label>
                @endforeach
                @if($question['allow_custom'] ?? false)
                    <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;color:#334155;cursor:pointer;">
                        <input type="radio" name="answer" value="__custom__"
                               id="custom-radio-{{ $qIndex }}"
                               {{ ($hasResp && !in_array($response->response_text, $qOptions)) ? 'checked' : '' }}
                               onchange="document.getElementById('custom-text-{{ $qIndex }}').style.display='block';"
                               style="accent-color:#4f46e5;">
                        Altra opció (escriu la teva)
                    </label>
                    <div id="custom-text-{{ $qIndex }}"
                         style="display:{{ ($hasResp && !in_array($response->response_text, $qOptions)) ? 'block' : 'none' }};margin-left:1.5rem;">
                        <input type="text"
                               name="answer_custom"
                               value="{{ ($hasResp && !in_array($response->response_text, $qOptions)) ? $response->response_text : '' }}"
                               placeholder="Escriu la teva opció..."
                               style="width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:0.4rem;padding:0.4rem 0.6rem;font-size:0.875rem;color:#1e293b;">
                    </div>
                @endif
            </div>

        @elseif($qType === 'choice_one')
            <div style="display:flex;flex-direction:column;gap:0.4rem;margin-bottom:0.5rem;">
                @foreach($qOptions as $opt)
                    <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;color:#334155;cursor:pointer;">
                        <input type="radio" name="answer" value="{{ $opt }}"
                               {{ ($hasResp && is_array($response->response_choices) && in_array($opt, $response->response_choices)) ? 'checked' : '' }}
                               style="accent-color:#4f46e5;">
                        {{ $opt }}
                    </label>
                @endforeach
            </div>

        @elseif($qType === 'choice_many')
            <div style="display:flex;flex-direction:column;gap:0.4rem;margin-bottom:0.5rem;">
                @foreach($qOptions as $opt)
                    <label style="display:flex;align-items:center;gap:0.5rem;font-size:0.875rem;color:#334155;cursor:pointer;">
                        <input type="checkbox" name="answer[]" value="{{ $opt }}"
                               {{ ($hasResp && is_array($response->response_choices) && in_array($opt, $response->response_choices)) ? 'checked' : '' }}
                               style="accent-color:#4f46e5;">
                        {{ $opt }}
                    </label>
                @endforeach
            </div>

        @elseif($qType === 'yes_no')
            <div style="display:flex;gap:0.75rem;margin-bottom:0.5rem;">
                <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.875rem;font-weight:500;color:#334155;cursor:pointer;background:#fff;border:1px solid #cbd5e1;border-radius:0.4rem;padding:0.4rem 1rem;">
                    <input type="radio" name="answer" value="1"
                           {{ ($hasResp && $response->response_bool === true) ? 'checked' : '' }}
                           style="accent-color:#4f46e5;">
                    Sí
                </label>
                <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.875rem;font-weight:500;color:#334155;cursor:pointer;background:#fff;border:1px solid #cbd5e1;border-radius:0.4rem;padding:0.4rem 1rem;">
                    <input type="radio" name="answer" value="0"
                           {{ ($hasResp && $response->response_bool === false) ? 'checked' : '' }}
                           style="accent-color:#4f46e5;">
                    No
                </label>
            </div>
        @endif

        {{-- Errors de validació --}}
        @error('answer')
            <p style="font-size:0.8rem;color:#dc2626;margin:0.25rem 0 0.5rem 0;">{{ $message }}</p>
        @enderror

        <button type="submit"
                style="background:#4f46e5;color:#fff;font-size:0.8rem;font-weight:600;padding:0.4rem 1rem;border:none;border-radius:0.4rem;cursor:pointer;margin-top:0.5rem;"
                onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            {{ $hasResp ? 'Actualitzar resposta' : 'Desar resposta' }}
        </button>
    </form>
</div>

{{-- Script per al select_from_examples amb opció personalitzada --}}
@if($qType === 'select_from_examples' && ($question['allow_custom'] ?? false))
<script>
(function() {
    var radio = document.getElementById('custom-radio-{{ $qIndex }}');
    var box   = document.getElementById('custom-text-{{ $qIndex }}');
    if (!radio || !box) return;

    // Si hi ha una resposta custom guardada, mostrar el camp
    var form = radio.closest('form');
    var radios = form ? form.querySelectorAll('input[type=radio][name=answer]') : [];
    radios.forEach(function(r) {
        if (r !== radio) {
            r.addEventListener('change', function() { box.style.display = 'none'; });
        }
    });
})();
</script>
@endif
