@extends('campus.teacher.layouts.app')

@section('title', $lesson->exists ? 'Editar · ' . $lesson->title : 'Nova sessió · ' . $course->title)

@section('content')

@php
    $isEdit    = $lesson->exists;
    $formRoute = $isEdit
        ? route('teacher.lms.lesson.update', [$course->slug, $lesson->id])
        : route('teacher.lms.lesson.store',  $course->slug);
@endphp

{{-- Capçalera --}}
<div style="margin-bottom:1.5rem;display:flex;align-items:center;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
    <a href="{{ route('teacher.lms.course', $course->slug) }}"
       style="font-size:0.875rem;color:#4f46e5;text-decoration:none;">&larr; Tornar a les sessions</a>
    @if($isEdit)
    <a href="{{ route('teacher.lms.lesson', [$course->slug, $lesson->id]) }}"
       style="font-size:0.8125rem;color:#6b7280;border:1px solid #e5e7eb;border-radius:0.4rem;padding:0.3rem 0.75rem;text-decoration:none;"
       target="_blank">👁 Previsualitzar</a>
    @endif
</div>

<h1 style="font-size:1.5rem;font-weight:700;color:#111827;margin-bottom:0.25rem;">
    {{ $isEdit ? 'Editar sessió' : 'Nova sessió' }}
</h1>
<p style="font-size:0.875rem;color:#6b7280;margin-bottom:1.75rem;">{{ $course->title }}</p>

@if (session('success'))
    <div style="background:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.875rem;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fecaca;color:#dc2626;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.875rem;">
        <strong>Hi ha errors al formulari:</strong>
        <ul style="margin:0.4rem 0 0 1rem;padding:0;">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ $formRoute }}" id="lesson-form">
    @csrf
    @if($isEdit) @method('PATCH') @endif

    {{-- ── BLOC 1: Capçalera ─────────────────────────────────────────────── --}}
    @include('campus.lms.teacher.partials.form-section', [
        'title' => 'Capçalera de la sessió',
        'open'  => true,
        'slot'  => '
    '])
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;margin-bottom:1rem;">
        <p style="font-size:0.75rem;font-weight:600;color:#4f46e5;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:1rem;">Capçalera</p>
        <div style="display:grid;grid-template-columns:1fr 2fr 1fr 1fr;gap:1rem;margin-bottom:1rem;">
            @include('campus.lms.teacher.partials.field', ['name'=>'session_number','label'=>'Núm. sessió','type'=>'number','value'=>old('session_number',$lesson->session_number),'required'=>true,'min'=>1])
            @include('campus.lms.teacher.partials.field', ['name'=>'title','label'=>'Títol','type'=>'text','value'=>old('title',$lesson->title),'required'=>true])
            @include('campus.lms.teacher.partials.field', ['name'=>'duration','label'=>'Durada','type'=>'text','value'=>old('duration',$lesson->duration),'placeholder'=>'ex. 45–60 min'])
            <div>
                <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Estat *</label>
                <select name="status" style="width:100%;border:1px solid #d1d5db;border-radius:0.4rem;padding:0.45rem 0.6rem;font-size:0.875rem;color:#1e293b;background:#fff;">
                    <option value="draft"     {{ old('status',$lesson->status) === 'draft'     ? 'selected' : '' }}>Esborrany</option>
                    <option value="published" {{ old('status',$lesson->status) === 'published' ? 'selected' : '' }}>Publicada</option>
                </select>
            </div>
        </div>
        @include('campus.lms.teacher.partials.field', ['name'=>'subtitle','label'=>'Subtítol','type'=>'text','value'=>old('subtitle',$lesson->subtitle),'full'=>true])
        @include('campus.lms.teacher.partials.field', ['name'=>'sort_order','label'=>'Ordre de visualització','type'=>'number','value'=>old('sort_order',$lesson->sort_order ?? 0)])
    </div>

    {{-- ── BLOC 2: Introducció ───────────────────────────────────────────── --}}
    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;margin-bottom:1rem;overflow:hidden;">
        <summary style="padding:1rem 1.5rem;cursor:pointer;font-size:0.75rem;font-weight:600;color:#4f46e5;text-transform:uppercase;letter-spacing:0.08em;list-style:none;display:flex;justify-content:space-between;align-items:center;">
            Introducció (cita + text) <span style="font-size:1rem;color:#9ca3af;">▾</span>
        </summary>
        <div style="padding:0 1.5rem 1.5rem;">
            @include('campus.lms.teacher.partials.field', ['name'=>'quote_text','label'=>'Cita','type'=>'textarea','value'=>old('quote_text',$lesson->quote_text),'rows'=>2,'full'=>true,'placeholder'=>'"Lo bueno, si breve, dos veces bueno."'])
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1rem;">
                @include('campus.lms.teacher.partials.field', ['name'=>'quote_author','label'=>'Autor/a','type'=>'text','value'=>old('quote_author',$lesson->quote_author)])
                @include('campus.lms.teacher.partials.field', ['name'=>'quote_work','label'=>'Obra','type'=>'text','value'=>old('quote_work',$lesson->quote_work)])
            </div>
            @include('campus.lms.teacher.partials.field', ['name'=>'intro_text','label'=>'Paràgraf introductori','type'=>'textarea','value'=>old('intro_text',$lesson->intro_text),'rows'=>4,'full'=>true])
        </div>
    </details>

    {{-- ── BLOC 3: El tema d'avui ────────────────────────────────────────── --}}
    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;margin-bottom:1rem;overflow:hidden;">
        <summary style="padding:1rem 1.5rem;cursor:pointer;font-size:0.75rem;font-weight:600;color:#4f46e5;text-transform:uppercase;letter-spacing:0.08em;list-style:none;display:flex;justify-content:space-between;align-items:center;">
            El tema d'avui <span style="font-size:1rem;color:#9ca3af;">▾</span>
        </summary>
        <div style="padding:0 1.5rem 1.5rem;">
            @include('campus.lms.teacher.partials.field', ['name'=>'topic_text','label'=>'Explicació del tema','type'=>'textarea','value'=>old('topic_text',$lesson->topic_text),'rows'=>5,'full'=>true])
        </div>
    </details>

    {{-- ── BLOC 4: Reflexió ─────────────────────────────────────────────── --}}
    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;margin-bottom:1rem;overflow:hidden;">
        <summary style="padding:1rem 1.5rem;cursor:pointer;font-size:0.75rem;font-weight:600;color:#4f46e5;text-transform:uppercase;letter-spacing:0.08em;list-style:none;display:flex;justify-content:space-between;align-items:center;">
            Reflexió (preguntes per al grup) <span style="font-size:1rem;color:#9ca3af;">▾</span>
        </summary>
        <div style="padding:0 1.5rem 1.5rem;" id="refl-container">
            @php
                $reflQs = old('reflection_questions',
                    array_map(fn($q) => is_array($q) ? ($q['question'] ?? '') : $q,
                        $lesson->reflection_questions ?? []));
                if (empty($reflQs)) $reflQs = [''];
            @endphp
            @foreach($reflQs as $rq)
                <div class="refl-item" style="display:flex;align-items:flex-start;gap:0.5rem;margin-bottom:0.625rem;">
                    <textarea name="reflection_questions[]" rows="2"
                              placeholder="Escriu una pregunta per debatre..."
                              style="flex:1;border:1px solid #d1d5db;border-radius:0.4rem;padding:0.45rem 0.6rem;font-size:0.875rem;color:#1e293b;resize:vertical;font-family:inherit;">{{ $rq }}</textarea>
                    <button type="button" onclick="this.closest('.refl-item').remove()"
                            style="flex-shrink:0;color:#9ca3af;border:none;background:transparent;font-size:1.2rem;cursor:pointer;padding:0.3rem;line-height:1;">×</button>
                </div>
            @endforeach
            <button type="button" onclick="addReflItem()"
                    style="font-size:0.8125rem;color:#4f46e5;border:1px dashed #c7d2fe;background:transparent;border-radius:0.4rem;padding:0.375rem 0.875rem;cursor:pointer;width:100%;margin-top:0.25rem;">
                + Afegir pregunta
            </button>
        </div>
    </details>

    {{-- ── BLOC 5: Exercici pràctic ─────────────────────────────────────── --}}
    @php $ex = $lesson->exercise ?? []; @endphp
    <details style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;margin-bottom:1rem;overflow:hidden;">
        <summary style="padding:1rem 1.5rem;cursor:pointer;font-size:0.75rem;font-weight:600;color:#4f46e5;text-transform:uppercase;letter-spacing:0.08em;list-style:none;display:flex;justify-content:space-between;align-items:center;">
            Exercici pràctic <span style="font-size:1rem;color:#9ca3af;">▾</span>
        </summary>
        <div style="padding:0 1.5rem 1.5rem;">
            <div style="display:grid;grid-template-columns:2fr 1fr;gap:1rem;margin-bottom:1rem;">
                @include('campus.lms.teacher.partials.field', ['name'=>'exercise_title','label'=>'Títol de l\'exercici','type'=>'text','value'=>old('exercise_title',$ex['title'] ?? '')])
                @include('campus.lms.teacher.partials.field', ['name'=>'exercise_duration','label'=>'Durada estimada','type'=>'text','value'=>old('exercise_duration',$ex['duration'] ?? ''),'placeholder'=>'ex. 20 min'])
            </div>
            @include('campus.lms.teacher.partials.field', ['name'=>'exercise_statement','label'=>'Enunciat','type'=>'textarea','value'=>old('exercise_statement',$ex['statement'] ?? ''),'rows'=>4,'full'=>true])

            {{-- Exemples --}}
            <p style="font-size:0.8125rem;font-weight:500;color:#374151;margin:1rem 0 0.5rem;">Opcions / Exemples</p>
            <div id="ex-examples-container">
                @php $examples = old('exercise_examples', $ex['examples'] ?? []); if(empty($examples)) $examples=[''];  @endphp
                @foreach($examples as $eg)
                <div class="ex-example-item" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
                    <input type="text" name="exercise_examples[]" value="{{ $eg }}"
                           placeholder="Exemple d'opció..."
                           style="flex:1;border:1px solid #d1d5db;border-radius:0.4rem;padding:0.4rem 0.6rem;font-size:0.875rem;color:#1e293b;">
                    <button type="button" onclick="this.closest('.ex-example-item').remove()"
                            style="color:#9ca3af;border:none;background:transparent;font-size:1.2rem;cursor:pointer;padding:0.2rem;">×</button>
                </div>
                @endforeach
            </div>
            <button type="button" onclick="addItem('ex-examples-container','ex-example-item','exercise_examples[]','Exemple d\'opció...')"
                    style="font-size:0.8125rem;color:#4f46e5;border:1px dashed #c7d2fe;background:transparent;border-radius:0.4rem;padding:0.3rem 0.75rem;cursor:pointer;margin-bottom:1rem;">
                + Afegir exemple
            </button>

            {{-- Consells --}}
            <p style="font-size:0.8125rem;font-weight:500;color:#374151;margin:0.5rem 0;">Consells</p>
            <div id="ex-tips-container">
                @php $tips = old('exercise_tips', $ex['tips'] ?? []); if(empty($tips)) $tips=[''];  @endphp
                @foreach($tips as $tip)
                <div class="ex-tip-item" style="display:flex;gap:0.5rem;margin-bottom:0.5rem;">
                    <input type="text" name="exercise_tips[]" value="{{ $tip }}"
                           placeholder="Consell..."
                           style="flex:1;border:1px solid #d1d5db;border-radius:0.4rem;padding:0.4rem 0.6rem;font-size:0.875rem;color:#1e293b;">
                    <button type="button" onclick="this.closest('.ex-tip-item').remove()"
                            style="color:#9ca3af;border:none;background:transparent;font-size:1.2rem;cursor:pointer;padding:0.2rem;">×</button>
                </div>
                @endforeach
            </div>
            <button type="button" onclick="addItem('ex-tips-container','ex-tip-item','exercise_tips[]','Consell...')"
                    style="font-size:0.8125rem;color:#4f46e5;border:1px dashed #c7d2fe;background:transparent;border-radius:0.4rem;padding:0.3rem 0.75rem;cursor:pointer;margin-bottom:1rem;">
                + Afegir consell
            </button>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem;">
                @include('campus.lms.teacher.partials.field', ['name'=>'exercise_demo_first','label'=>'Demo 1a persona (opcional)','type'=>'textarea','value'=>old('exercise_demo_first',$ex['demo_first_person'] ?? ''),'rows'=>3])
                @include('campus.lms.teacher.partials.field', ['name'=>'exercise_demo_third','label'=>'Demo 3a persona (opcional)','type'=>'textarea','value'=>old('exercise_demo_third',$ex['demo_third_person'] ?? ''),'rows'=>3])
            </div>
        </div>
    </details>

    {{-- ── BLOC 6: Preguntes interactives ──────────────────────────────── --}}
    <details style="background:#fff;border:1px solid #c7d2fe;border-radius:0.75rem;margin-bottom:1.5rem;overflow:hidden;">
        <summary style="padding:1rem 1.5rem;cursor:pointer;font-size:0.75rem;font-weight:600;color:#4338ca;text-transform:uppercase;letter-spacing:0.08em;list-style:none;display:flex;justify-content:space-between;align-items:center;background:#f8f9ff;">
            Preguntes interactives (qüestionari / exercici) <span style="font-size:1rem;color:#9ca3af;">▾</span>
        </summary>
        <div style="padding:1rem 1.5rem 1.5rem;">
            <p style="font-size:0.8125rem;color:#6b7280;margin-bottom:1rem;">
                Afegeix preguntes que els alumnes responen en línia. L'índex determina l'ordre (0, 1, 2…).
            </p>

            <div id="questions-list">
                {{-- Les targetes de pregunta es renderitzen via JS --}}
            </div>

            <button type="button" onclick="addQuestion()"
                    style="font-size:0.8125rem;color:#4338ca;border:1px dashed #a5b4fc;background:transparent;border-radius:0.4rem;padding:0.4rem 1rem;cursor:pointer;width:100%;margin-top:0.5rem;">
                + Afegir pregunta
            </button>

            {{-- Camp ocult on s'emmagatzema el JSON de preguntes --}}
            <input type="hidden" name="questions_json" id="questions-json"
                   value="{{ old('questions_json', json_encode($lesson->questions ?? [])) }}">
        </div>
    </details>

    {{-- Botons d'acció --}}
    <div style="display:flex;gap:0.75rem;align-items:center;flex-wrap:wrap;">
        <button type="submit"
                style="background:#4f46e5;color:#fff;border:none;border-radius:0.5rem;padding:0.625rem 1.75rem;font-size:0.9375rem;font-weight:600;cursor:pointer;"
                onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
            {{ $isEdit ? 'Desar canvis' : 'Crear sessió' }}
        </button>
        <a href="{{ route('teacher.lms.course', $course->slug) }}"
           style="font-size:0.875rem;color:#6b7280;text-decoration:none;">Cancel·lar</a>
    </div>
</form>

{{-- ── JavaScript: repeaters dinàmics ──────────────────────────────────── --}}
<script>
// ── Reflexió: afegir ítem ──────────────────────────────────────────────────
function addReflItem() {
    const c = document.getElementById('refl-container');
    const d = document.createElement('div');
    d.className = 'refl-item';
    d.style.cssText = 'display:flex;align-items:flex-start;gap:0.5rem;margin-bottom:0.625rem;';
    d.innerHTML = `<textarea name="reflection_questions[]" rows="2" placeholder="Escriu una pregunta per debatre..."
        style="flex:1;border:1px solid #d1d5db;border-radius:0.4rem;padding:0.45rem 0.6rem;font-size:0.875rem;color:#1e293b;resize:vertical;font-family:inherit;"></textarea>
        <button type="button" onclick="this.closest('.refl-item').remove()"
            style="flex-shrink:0;color:#9ca3af;border:none;background:transparent;font-size:1.2rem;cursor:pointer;padding:0.3rem;line-height:1;">×</button>`;
    const btn = c.querySelector('button[onclick="addReflItem()"]');
    c.insertBefore(d, btn);
}

// ── Genèric: afegir ítem a un contenidor ──────────────────────────────────
function addItem(containerId, itemClass, inputName, placeholder) {
    const c = document.getElementById(containerId);
    const d = document.createElement('div');
    d.className = itemClass;
    d.style.cssText = 'display:flex;gap:0.5rem;margin-bottom:0.5rem;';
    d.innerHTML = `<input type="text" name="${inputName}" placeholder="${placeholder}"
        style="flex:1;border:1px solid #d1d5db;border-radius:0.4rem;padding:0.4rem 0.6rem;font-size:0.875rem;color:#1e293b;">
        <button type="button" onclick="this.closest('.${itemClass}').remove()"
            style="color:#9ca3af;border:none;background:transparent;font-size:1.2rem;cursor:pointer;padding:0.2rem;">×</button>`;
    c.appendChild(d);
}

// ── Preguntes interactives ────────────────────────────────────────────────
var questions = JSON.parse(document.getElementById('questions-json').value || '[]');

const TYPES = {
    open_text:            'Text obert',
    select_from_examples: 'Selecció d\'exemples',
    choice_one:           'Opció única (avaluable)',
    choice_many:          'Múltiple opcions (avaluable)',
    yes_no:               'Sí / No (avaluable)',
};
const BLOCKS = { reflection: 'Reflexió', exercise: 'Exercici', quiz: 'Qüestionari' };

function renderQuestions() {
    const list = document.getElementById('questions-list');
    list.innerHTML = '';
    questions.forEach((q, i) => {
        const card = document.createElement('div');
        card.style.cssText = 'border:1px solid #e0e7ff;border-radius:0.5rem;padding:1rem;margin-bottom:0.75rem;background:#fafafa;';
        card.innerHTML = buildQuestionCard(q, i);
        list.appendChild(card);
    });
    syncJson();
}

function buildQuestionCard(q, i) {
    const typeOpts = Object.entries(TYPES).map(([v,l]) =>
        `<option value="${v}" ${q.type===v?'selected':''}>${l}</option>`).join('');
    const blockOpts = Object.entries(BLOCKS).map(([v,l]) =>
        `<option value="${v}" ${q.block===v?'selected':''}>${l}</option>`).join('');

    const hasOptions = ['choice_one','choice_many','select_from_examples'].includes(q.type);
    const hasCorrectOne = ['choice_one','yes_no'].includes(q.type);
    const hasCorrectMany = q.type === 'choice_many';
    const hasAllowCustom = q.type === 'select_from_examples';

    const optionsHtml = hasOptions ? `
        <div style="margin-top:0.75rem;">
            <p style="font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.3rem;">Opcions</p>
            <div id="q-opts-${i}">
                ${(q.options||[]).map(o=>`
                <div class="q-opt" style="display:flex;gap:0.4rem;margin-bottom:0.4rem;">
                    <input type="text" value="${escHtml(o)}" onchange="updateOpt(${i},this)"
                        style="flex:1;border:1px solid #d1d5db;border-radius:0.35rem;padding:0.35rem 0.5rem;font-size:0.8125rem;">
                    <button type="button" onclick="removeOpt(${i},this)" style="color:#9ca3af;border:none;background:transparent;font-size:1rem;cursor:pointer;">×</button>
                </div>`).join('')}
            </div>
            <button type="button" onclick="addOpt(${i})"
                style="font-size:0.75rem;color:#4f46e5;border:1px dashed #c7d2fe;background:transparent;border-radius:0.35rem;padding:0.2rem 0.6rem;cursor:pointer;margin-top:0.25rem;">
                + Afegir opció
            </button>
        </div>` : '';

    const correctOneHtml = hasCorrectOne ? `
        <div style="margin-top:0.75rem;">
            <label style="font-size:0.75rem;font-weight:500;color:#374151;">Resposta correcta</label>
            <input type="text" value="${escHtml(q.correct_answer??'')}"
                   placeholder="${q.type==='yes_no'?'true o false':'Text exacte de l\'opció'}"
                   onchange="questions[${i}].correct_answer=this.value;syncJson()"
                   style="width:100%;border:1px solid #d1d5db;border-radius:0.35rem;padding:0.35rem 0.5rem;font-size:0.8125rem;margin-top:0.25rem;">
        </div>` : '';

    const correctManyHtml = hasCorrectMany ? `
        <div style="margin-top:0.75rem;">
            <p style="font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.3rem;">Respostes correctes</p>
            <div id="q-corr-${i}">
                ${(q.correct_answers||[]).map(a=>`
                <div class="q-corr" style="display:flex;gap:0.4rem;margin-bottom:0.4rem;">
                    <input type="text" value="${escHtml(a)}" onchange="updateCorr(${i},this)"
                        style="flex:1;border:1px solid #d1d5db;border-radius:0.35rem;padding:0.35rem 0.5rem;font-size:0.8125rem;">
                    <button type="button" onclick="removeCorr(${i},this)" style="color:#9ca3af;border:none;background:transparent;font-size:1rem;cursor:pointer;">×</button>
                </div>`).join('')}
            </div>
            <button type="button" onclick="addCorr(${i})"
                style="font-size:0.75rem;color:#4f46e5;border:1px dashed #c7d2fe;background:transparent;border-radius:0.35rem;padding:0.2rem 0.6rem;cursor:pointer;margin-top:0.25rem;">
                + Afegir resposta correcta
            </button>
        </div>` : '';

    const allowCustomHtml = hasAllowCustom ? `
        <label style="display:flex;align-items:center;gap:0.4rem;font-size:0.8125rem;color:#374151;margin-top:0.5rem;cursor:pointer;">
            <input type="checkbox" ${q.allow_custom?'checked':''} onchange="questions[${i}].allow_custom=this.checked;syncJson()">
            Permetre opció personalitzada (text lliure)
        </label>` : '';

    return `
        <div style="display:flex;align-items:center;justify-content:space-between;gap:0.5rem;margin-bottom:0.75rem;flex-wrap:wrap;">
            <span style="font-size:0.75rem;font-weight:600;color:#6366f1;">Pregunta #${i}</span>
            <div style="display:flex;gap:0.5rem;">
                ${i>0?`<button type="button" onclick="moveQ(${i},-1)" style="font-size:0.75rem;border:1px solid #e5e7eb;background:#fff;border-radius:0.35rem;padding:0.15rem 0.5rem;cursor:pointer;">↑</button>`:''}
                ${i<questions.length-1?`<button type="button" onclick="moveQ(${i},1)" style="font-size:0.75rem;border:1px solid #e5e7eb;background:#fff;border-radius:0.35rem;padding:0.15rem 0.5rem;cursor:pointer;">↓</button>`:''}
                <button type="button" onclick="removeQ(${i})" style="font-size:0.75rem;color:#dc2626;border:1px solid #fecaca;background:#fff;border-radius:0.35rem;padding:0.15rem 0.5rem;cursor:pointer;">Eliminar</button>
            </div>
        </div>
        <div style="display:grid;grid-template-columns:2fr 1fr;gap:0.75rem;margin-bottom:0.5rem;">
            <div>
                <label style="font-size:0.75rem;font-weight:500;color:#374151;">Tipus</label>
                <select onchange="changeType(${i},this.value)"
                    style="width:100%;border:1px solid #d1d5db;border-radius:0.35rem;padding:0.35rem 0.5rem;font-size:0.8125rem;background:#fff;margin-top:0.25rem;">
                    ${typeOpts}
                </select>
            </div>
            <div>
                <label style="font-size:0.75rem;font-weight:500;color:#374151;">Bloc</label>
                <select onchange="questions[${i}].block=this.value;syncJson()"
                    style="width:100%;border:1px solid #d1d5db;border-radius:0.35rem;padding:0.35rem 0.5rem;font-size:0.8125rem;background:#fff;margin-top:0.25rem;">
                    ${blockOpts}
                </select>
            </div>
        </div>
        <textarea rows="2" placeholder="Enunciat de la pregunta..." onchange="questions[${i}].text=this.value;syncJson()"
            style="width:100%;box-sizing:border-box;border:1px solid #d1d5db;border-radius:0.35rem;padding:0.4rem 0.6rem;font-size:0.8125rem;resize:vertical;font-family:inherit;">${escHtml(q.text||'')}</textarea>
        ${optionsHtml}${correctOneHtml}${correctManyHtml}${allowCustomHtml}
        <div style="display:flex;gap:1rem;margin-top:0.5rem;align-items:center;">
            <label style="display:flex;align-items:center;gap:0.35rem;font-size:0.8125rem;color:#374151;cursor:pointer;">
                <input type="checkbox" ${q.required?'checked':''} onchange="questions[${i}].required=this.checked;syncJson()"> Obligatòria
            </label>
            <label style="font-size:0.8125rem;color:#374151;">
                Punts: <input type="number" min="0" value="${q.points||0}" onchange="questions[${i}].points=parseFloat(this.value)||0;syncJson()"
                    style="width:4rem;border:1px solid #d1d5db;border-radius:0.35rem;padding:0.2rem 0.4rem;font-size:0.8125rem;">
            </label>
        </div>
    `;
}

function escHtml(s) {
    return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function syncJson() {
    // Reasignar índexs
    questions.forEach((q,i) => q.index = i);
    document.getElementById('questions-json').value = JSON.stringify(questions);
}

function addQuestion() {
    questions.push({ index: questions.length, type: 'open_text', block: 'reflection', text: '', required: false, points: 0 });
    renderQuestions();
}

function removeQ(i) {
    questions.splice(i, 1);
    renderQuestions();
}

function moveQ(i, dir) {
    const j = i + dir;
    if (j < 0 || j >= questions.length) return;
    [questions[i], questions[j]] = [questions[j], questions[i]];
    renderQuestions();
}

function changeType(i, val) {
    questions[i].type = val;
    if (!['choice_one','yes_no'].includes(val)) delete questions[i].correct_answer;
    if (val !== 'choice_many') delete questions[i].correct_answers;
    if (val !== 'select_from_examples') delete questions[i].allow_custom;
    if (!['choice_one','choice_many','select_from_examples'].includes(val)) delete questions[i].options;
    renderQuestions();
}

// Helpers per opcions
function updateOpt(i, el) {
    const opts = document.querySelectorAll(`#q-opts-${i} input`);
    questions[i].options = Array.from(opts).map(o => o.value);
    syncJson();
}
function removeOpt(i, btn) {
    btn.closest('.q-opt').remove();
    updateOpt(i, null);
}
function addOpt(i) {
    const c = document.getElementById(`q-opts-${i}`);
    const d = document.createElement('div');
    d.className='q-opt'; d.style.cssText='display:flex;gap:0.4rem;margin-bottom:0.4rem;';
    d.innerHTML=`<input type="text" placeholder="Opció..." onchange="updateOpt(${i},this)"
        style="flex:1;border:1px solid #d1d5db;border-radius:0.35rem;padding:0.35rem 0.5rem;font-size:0.8125rem;">
        <button type="button" onclick="removeOpt(${i},this)" style="color:#9ca3af;border:none;background:transparent;font-size:1rem;cursor:pointer;">×</button>`;
    c.appendChild(d);
    if (!questions[i].options) questions[i].options = [];
    questions[i].options.push('');
    syncJson();
}

// Helpers per respostes correctes (choice_many)
function updateCorr(i, el) {
    const corrs = document.querySelectorAll(`#q-corr-${i} input`);
    questions[i].correct_answers = Array.from(corrs).map(o => o.value);
    syncJson();
}
function removeCorr(i, btn) { btn.closest('.q-corr').remove(); updateCorr(i, null); }
function addCorr(i) {
    const c = document.getElementById(`q-corr-${i}`);
    const d = document.createElement('div');
    d.className='q-corr'; d.style.cssText='display:flex;gap:0.4rem;margin-bottom:0.4rem;';
    d.innerHTML=`<input type="text" placeholder="Resposta correcta..." onchange="updateCorr(${i},this)"
        style="flex:1;border:1px solid #d1d5db;border-radius:0.35rem;padding:0.35rem 0.5rem;font-size:0.8125rem;">
        <button type="button" onclick="removeCorr(${i},this)" style="color:#9ca3af;border:none;background:transparent;font-size:1rem;cursor:pointer;">×</button>`;
    c.appendChild(d);
    if (!questions[i].correct_answers) questions[i].correct_answers = [];
    questions[i].correct_answers.push('');
    syncJson();
}

// Init
renderQuestions();

// Sincronitzar JSON abans d'enviar el formulari
document.getElementById('lesson-form').addEventListener('submit', function() {
    syncJson();
});
</script>

@endsection
