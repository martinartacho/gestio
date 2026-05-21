@extends('campus.teacher.layouts.app')

@section('title', 'Editar document')

@section('content')

<div style="margin-bottom:1.5rem;">
    <a href="{{ route('teacher.portal.documents') }}"
       style="font-size:0.875rem;color:#4f46e5;text-decoration:none;">&larr; Tornar als documents</a>
</div>

<div style="max-width:42rem;">
    <h1 style="font-size:1.25rem;font-weight:700;color:#111827;margin-bottom:0.25rem;">Editar document</h1>
    <p style="font-size:0.875rem;color:#6b7280;margin-bottom:1.5rem;">
        Tipus: <strong>{{ \App\Models\CampusDocument::TYPES[$document->type] ?? $document->type }}</strong>
        @if ($document->file_name)
            · {{ $document->file_name }}
            @if ($document->file_size_formatted) ({{ $document->file_size_formatted }}) @endif
        @endif
    </p>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;">

        @if ($errors->any())
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1.25rem;font-size:0.875rem;">
                <ul style="margin:0;padding-left:1rem;">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('teacher.portal.documents.update', $document->id) }}"
              method="POST" enctype="multipart/form-data">
            @csrf

            {{-- Títol --}}
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                    Títol <span style="color:#dc2626;">*</span>
                </label>
                <input type="text" name="title" value="{{ old('title', $document->title) }}" required
                       style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;box-sizing:border-box;">
            </div>

            {{-- Descripció --}}
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Descripció</label>
                <textarea name="description" rows="3"
                          style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;box-sizing:border-box;resize:vertical;">{{ old('description', $document->description) }}</textarea>
            </div>

            {{-- URL (si és tipus url) --}}
            @if ($document->type === 'url')
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                    URL <span style="color:#dc2626;">*</span>
                </label>
                <input type="url" name="url" value="{{ old('url', $document->url) }}" required
                       placeholder="https://..."
                       style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;box-sizing:border-box;">
            </div>
            @endif

            {{-- Substituir fitxer (si és tipus file) --}}
            @if ($document->type === 'file')
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                    Substituir fitxer
                    <span style="font-weight:400;color:#9ca3af;">(opcional)</span>
                </label>
                <input type="file" name="file"
                       accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.mp4,.mp3,.zip"
                       style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;background:#fff;box-sizing:border-box;">
                <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">
                    Deixa buit per conservar el fitxer actual.
                    PDF, Office, imatges, ZIP · màx. 10 MB
                </p>
                @if ($document->download_url)
                    <a href="{{ $document->download_url }}" target="_blank"
                       style="font-size:0.75rem;color:#4f46e5;text-decoration:none;">
                        ↗ Veure fitxer actual
                    </a>
                @endif
            </div>
            @endif

            {{-- Curs --}}
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Curs associat</label>
                <select name="course_id"
                        style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;background:#fff;">
                    <option value="">— Sense curs —</option>
                    @foreach ($courses as $c)
                        <option value="{{ $c->id }}"
                            {{ old('course_id', $document->course_id) == $c->id ? 'selected' : '' }}>
                            [{{ $c->code }}] {{ $c->title }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Condicions d'accés ──────────────────────────────────────────── --}}
            <div style="border:1px solid #e5e7eb;border-radius:0.5rem;padding:1rem;margin-bottom:1rem;background:#fafafa;">
                <p style="font-size:0.8125rem;font-weight:600;color:#374151;margin:0 0 0.125rem;">Condicions d'accés</p>
                <p style="font-size:0.75rem;color:#9ca3af;margin:0 0 0.875rem;">
                    Si s'especifica <strong>alguna</strong> condició, el document s'activa en complir-se'n
                    <strong>qualsevol</strong> (OR). Sense condicions: accés immediat.
                </p>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;">
                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                            Disponible a partir de
                        </label>
                        <input type="datetime-local" name="available_from"
                               value="{{ old('available_from', $document->available_from?->format('Y-m-d\TH:i')) }}"
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;box-sizing:border-box;background:#fff;">
                        <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">Deixa buit per a accés immediat per data.</p>
                    </div>

                    <div>
                        <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                            A partir de la sessió #
                        </label>
                        <input type="number" name="session_number"
                               value="{{ old('session_number', $document->session_number) }}"
                               min="1"
                               @if($document->course) max="{{ $document->course->sessions }}" @endif
                               style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;box-sizing:border-box;background:#fff;">
                        <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">
                            Deixa buit per a accés immediat per sessió.
                            @if($document->course && $document->course->sessions)
                                <br>El curs té <strong>{{ $document->course->sessions }}</strong> sessions
                                @php $done = $document->course->sessionsPast(); @endphp
                                @if($done !== null)
                                    · <strong>{{ $done }}</strong> fetes fins avui.
                                @endif
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            {{-- Ordre de visualització --}}
            <div style="margin-bottom:1rem;">
                <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">Ordre de visualització</label>
                <input type="number" name="sort_order"
                       value="{{ old('sort_order', $document->sort_order ?? 0) }}"
                       min="0"
                       style="width:8rem;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;box-sizing:border-box;">
                <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">Número més petit = apareix abans.</p>
            </div>

            {{-- Visibilitat + Estat (2 columnes) --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:1.5rem;">
                <div>
                    <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                        Visibilitat <span style="color:#dc2626;">*</span>
                    </label>
                    <select name="visibility"
                            style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;background:#fff;">
                        @foreach (\App\Models\CampusDocument::VISIBILITIES as $k => $v)
                            <option value="{{ $k }}"
                                {{ old('visibility', $document->visibility) === $k ? 'selected' : '' }}>
                                {{ $v }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:0.8125rem;font-weight:500;color:#374151;margin-bottom:0.375rem;">
                        Estat <span style="color:#dc2626;">*</span>
                    </label>
                    <select name="status"
                            style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;background:#fff;">
                        @foreach (\App\Models\CampusDocument::STATUSES as $k => $v)
                            <option value="{{ $k }}"
                                {{ old('status', $document->status) === $k ? 'selected' : '' }}>
                                {{ $v }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Botons --}}
            <div style="display:flex;align-items:center;justify-content:space-between;border-top:1px solid #f3f4f6;padding-top:1.25rem;">
                <a href="{{ route('teacher.portal.documents') }}"
                   style="font-size:0.875rem;color:#6b7280;text-decoration:none;">
                    Cancel·lar
                </a>

                <div style="display:flex;gap:0.75rem;align-items:center;">
                    {{-- Eliminar --}}
                    <form action="{{ route('teacher.portal.documents.destroy', $document->id) }}"
                          method="POST"
                          onsubmit="return confirm('Eliminar «{{ addslashes($document->title) }}»?')">
                        @csrf @method('DELETE')
                        <button type="submit"
                                style="font-size:0.875rem;color:#dc2626;background:none;border:none;cursor:pointer;padding:0;">
                            Eliminar document
                        </button>
                    </form>

                    <button type="submit"
                            style="background:#4f46e5;color:#fff;border:none;border-radius:0.375rem;padding:0.5rem 1.25rem;font-size:0.875rem;font-weight:500;cursor:pointer;">
                        Desar canvis
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
