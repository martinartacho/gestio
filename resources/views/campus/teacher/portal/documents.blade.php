@extends('campus.teacher.layouts.app')

@section('title', 'Documents')

@section('content')

<div style="margin-bottom:1.5rem;display:flex;align-items:flex-start;justify-content:space-between;gap:1rem;flex-wrap:wrap;">
    <div>
        <h1 style="font-size:1.5rem;font-weight:700;color:#111827;">Documents</h1>
        <p style="font-size:0.875rem;color:#6b7280;margin-top:0.25rem;">
            Els meus documents i els documents del professorat.
        </p>
    </div>
    <a href="{{ route('teacher.portal.courses') }}"
       style="font-size:0.875rem;color:#4f46e5;text-decoration:none;">&larr; Cursos</a>
</div>

{{-- ── Filtres ──────────────────────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('teacher.portal.documents') }}"
      style="background:#ffffff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1rem 1.25rem;margin-bottom:1.5rem;display:flex;gap:0.75rem;flex-wrap:wrap;align-items:flex-end;">

    <div style="flex:1;min-width:10rem;">
        <label style="display:block;font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Curs</label>
        <select name="course_id"
                style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.4rem 0.75rem;font-size:0.875rem;color:#111827;background:#fff;">
            <option value="">Tots els cursos</option>
            @foreach ($myCourses as $c)
                <option value="{{ $c->id }}" {{ request('course_id') == $c->id ? 'selected' : '' }}>
                    [{{ $c->code }}] {{ $c->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div style="flex:0 0 9rem;">
        <label style="display:block;font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Tipus</label>
        <select name="type"
                style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.4rem 0.75rem;font-size:0.875rem;color:#111827;background:#fff;">
            <option value="">Tots</option>
            @foreach (\App\Models\CampusDocument::TYPES as $k => $v)
                <option value="{{ $k }}" {{ request('type') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div style="flex:0 0 10rem;">
        <label style="display:block;font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Visibilitat</label>
        <select name="visibility"
                style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.4rem 0.75rem;font-size:0.875rem;color:#111827;background:#fff;">
            <option value="">Totes</option>
            @foreach (\App\Models\CampusDocument::VISIBILITIES as $k => $v)
                <option value="{{ $k }}" {{ request('visibility') === $k ? 'selected' : '' }}>{{ $v }}</option>
            @endforeach
        </select>
    </div>

    <div style="display:flex;gap:0.5rem;">
        <button type="submit"
                style="background:#4f46e5;color:#fff;border:none;border-radius:0.375rem;padding:0.45rem 1rem;font-size:0.875rem;cursor:pointer;">
            Filtrar
        </button>
        @if(request()->hasAny(['course_id','type','visibility']))
            <a href="{{ route('teacher.portal.documents') }}"
               style="background:#f3f4f6;color:#374151;border:none;border-radius:0.375rem;padding:0.45rem 0.75rem;font-size:0.875rem;text-decoration:none;display:inline-flex;align-items:center;">
                ✕ Netejar
            </a>
        @endif
    </div>
</form>

{{-- ── Els meus documents ───────────────────────────────────────────────────── --}}
<div style="margin-bottom:2rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
        <h2 style="font-size:1rem;font-weight:600;color:#111827;">
            Els meus documents
            <span style="font-weight:400;color:#9ca3af;font-size:0.875rem;">({{ $ownDocuments->count() }})</span>
        </h2>
    </div>

    @if ($ownDocuments->isEmpty())
        <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:0.75rem;padding:2rem;text-align:center;color:#9ca3af;font-size:0.875rem;">
            No tens documents propis.
            Puja-n'hi des de la pàgina de cada curs.
        </div>
    @else
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;overflow:hidden;">
            @foreach ($ownDocuments as $doc)
            <div style="display:flex;align-items:center;gap:0.875rem;padding:0.875rem 1rem;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">

                {{-- Icona tipus --}}
                <div style="flex-shrink:0;width:2.25rem;height:2.25rem;display:flex;align-items:center;justify-content:center;border-radius:0.5rem;
                    {{ $doc->type === 'file' ? 'background:#eff6ff;' : 'background:#f0fdf4;' }}">
                    @if ($doc->type === 'url')
                        <svg style="width:1rem;height:1rem;color:#16a34a;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    @else
                        <svg style="width:1rem;height:1rem;color:#2563eb;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                    @endif
                </div>

                {{-- Contingut --}}
                <div style="flex:1;min-width:0;">
                    <p style="font-size:0.875rem;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $doc->title }}
                    </p>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.2rem;flex-wrap:wrap;">
                        @if ($doc->course)
                            <span style="font-size:0.75rem;color:#6b7280;">{{ $doc->course->code }}</span>
                            <span style="color:#d1d5db;">·</span>
                        @endif
                        <span style="font-size:0.75rem;
                            {{ $doc->visibility === 'public' ? 'color:#15803d;' : ($doc->visibility === 'private' ? 'color:#6b7280;' : 'color:#1d4ed8;') }}">
                            {{ \App\Models\CampusDocument::VISIBILITIES[$doc->visibility] ?? $doc->visibility }}
                        </span>
                        @if ($doc->status === 'draft')
                            <span style="color:#d1d5db;">·</span>
                            <span style="font-size:0.75rem;color:#d97706;">Esborrany</span>
                        @endif
                        @if ($doc->file_size_formatted)
                            <span style="color:#d1d5db;">·</span>
                            <span style="font-size:0.75rem;color:#9ca3af;">{{ $doc->file_size_formatted }}</span>
                        @endif
                        @if ($doc->available_from && now()->lt($doc->available_from))
                            <span style="color:#d1d5db;">·</span>
                            <span style="font-size:0.75rem;color:#d97706;" title="Els alumnes no hi tindran accés fins a aquesta data">
                                ⏰ Des del {{ $doc->available_from->format('d/m/Y') }}
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Accions --}}
                <div style="display:flex;gap:0.375rem;flex-shrink:0;">
                    @if ($doc->download_url)
                        <a href="{{ $doc->download_url }}" target="_blank"
                           title="Obrir"
                           style="display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border:1px solid #e5e7eb;border-radius:0.375rem;background:#f9fafb;color:#374151;text-decoration:none;">
                            <svg style="width:0.875rem;height:0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    @endif

                    <a href="{{ route('teacher.portal.documents.edit', $doc->id) }}"
                       title="Editar"
                       style="display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border:1px solid #e0e7ff;border-radius:0.375rem;background:#eef2ff;color:#4f46e5;text-decoration:none;">
                        <svg style="width:0.875rem;height:0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </a>

                    <form action="{{ route('teacher.portal.documents.destroy', $doc->id) }}"
                          method="POST"
                          onsubmit="return confirm('Eliminar «{{ addslashes($doc->title) }}»?')">
                        @csrf @method('DELETE')
                        <button type="submit" title="Eliminar"
                                style="display:inline-flex;align-items:center;justify-content:center;width:2rem;height:2rem;border:1px solid #fee2e2;border-radius:0.375rem;background:#fef2f2;color:#dc2626;cursor:pointer;">
                            <svg style="width:0.875rem;height:0.875rem;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ── Documents del professorat ────────────────────────────────────────────── --}}
<div>
    <h2 style="font-size:1rem;font-weight:600;color:#111827;margin-bottom:0.75rem;">
        Documents del professorat
        <span style="font-weight:400;color:#9ca3af;font-size:0.875rem;">({{ $othersDocuments->count() }})</span>
        <span style="font-size:0.75rem;font-weight:400;color:#9ca3af;margin-left:0.5rem;">— públics i per a matriculats</span>
    </h2>

    @if ($othersDocuments->isEmpty())
        <div style="background:#f9fafb;border:1px dashed #d1d5db;border-radius:0.75rem;padding:2rem;text-align:center;color:#9ca3af;font-size:0.875rem;">
            No hi ha documents d'altres professors amb la visibilitat seleccionada.
        </div>
    @else
        <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;overflow:hidden;">
            @foreach ($othersDocuments as $doc)
            <div style="display:flex;align-items:center;gap:0.875rem;padding:0.875rem 1rem;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">

                {{-- Icona --}}
                <div style="flex-shrink:0;width:2.25rem;height:2.25rem;display:flex;align-items:center;justify-content:center;border-radius:0.5rem;background:#faf5ff;">
                    @if ($doc->type === 'url')
                        <svg style="width:1rem;height:1rem;color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                    @else
                        <svg style="width:1rem;height:1rem;color:#7c3aed;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                        </svg>
                    @endif
                </div>

                {{-- Contingut --}}
                <div style="flex:1;min-width:0;">
                    <p style="font-size:0.875rem;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $doc->title }}
                    </p>
                    <div style="display:flex;align-items:center;gap:0.5rem;margin-top:0.2rem;flex-wrap:wrap;">
                        @if ($doc->teacher)
                            <span style="font-size:0.75rem;color:#7c3aed;font-weight:500;">{{ $doc->teacher->full_name }}</span>
                            <span style="color:#d1d5db;">·</span>
                        @endif
                        @if ($doc->course)
                            <span style="font-size:0.75rem;color:#6b7280;">{{ $doc->course->code }}</span>
                            <span style="color:#d1d5db;">·</span>
                        @endif
                        <span style="font-size:0.75rem;
                            {{ $doc->visibility === 'public' ? 'color:#15803d;' : 'color:#1d4ed8;' }}">
                            {{ \App\Models\CampusDocument::VISIBILITIES[$doc->visibility] ?? $doc->visibility }}
                        </span>
                        @if ($doc->description)
                            <span style="color:#d1d5db;">·</span>
                            <span style="font-size:0.75rem;color:#9ca3af;">{{ Str::limit($doc->description, 50) }}</span>
                        @endif
                    </div>
                </div>

                {{-- Acció: només visualitzar --}}
                @if ($doc->download_url)
                    <a href="{{ $doc->download_url }}" target="_blank"
                       style="flex-shrink:0;font-size:0.75rem;color:#6b7280;text-decoration:none;padding:0.3rem 0.6rem;border:1px solid #e5e7eb;border-radius:0.375rem;background:#f9fafb;white-space:nowrap;">
                        Obrir
                    </a>
                @endif
            </div>
            @endforeach
        </div>
    @endif
</div>

@endsection
