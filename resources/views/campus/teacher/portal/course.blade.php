@extends('campus.teacher.layouts.app')

@section('title', $course->title)

@section('content')
<div class="mb-4">
    <a href="{{ route('teacher.portal.courses') }}" class="text-sm text-indigo-600 hover:underline">&larr; Tornar als cursos</a>
</div>

<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 mb-6">
    <div class="flex items-start justify-between gap-4 mb-4">
        <div>
            <span class="text-xs font-mono text-gray-400">{{ $course->code }}</span>
            <h1 class="text-2xl font-bold text-gray-900 mt-1">{{ $course->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $course->season?->name }}
                @if ($course->start_date)
                    · {{ $course->start_date->format('d/m/Y') }}
                    @if ($course->end_date)
                        – {{ $course->end_date->format('d/m/Y') }}
                    @endif
                @endif
            </p>
        </div>
        <span class="text-xs px-2 py-1 rounded-full font-medium shrink-0
            {{ $course->status === 'active' ? 'bg-green-100 text-green-700' : ($course->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500') }}">
            {{ \App\Models\CampusCourse::STATUSES[$course->status] ?? $course->status }}
        </span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
        <div>
            <p class="text-gray-400 text-xs mb-0.5">Espai</p>
            <p class="font-medium">{{ $course->space?->name ?? '—' }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs mb-0.5">Horari</p>
            <p class="font-medium">{{ $course->timeSlot?->description ?? '—' }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs mb-0.5">Sessions</p>
            <p class="font-medium">{{ $course->sessions ?? '—' }}</p>
        </div>
        @if($course->hours !=null )
        <div>
            <p class="text-gray-400 text-xs mb-0.5">Hores</p>
            <p class="font-medium">{{ $course->hours ?? ' ' }}</p>
        </div>
        @endif
    </div>
</div>

{{-- ── Notificació ──────────────────────────────────────────────────────────────── --}}
@if (session('success'))
    <div style="background-color:#f0fdf4;border:1px solid #bbf7d0;color:#166534;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.875rem;">
        {{ session('success') }}
    </div>
@endif

{{-- ── Documents del curs ───────────────────────────────────────────────────── --}}
<div style="margin-bottom:1.5rem;">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:0.75rem;">
        <h2 style="font-size:1.125rem;font-weight:600;color:#111827;">
            Documents
            <span style="margin-left:0.5rem;font-size:0.875rem;font-weight:400;color:#6b7280;">({{ $documents->count() }})</span>
        </h2>
    </div>

    {{-- Formulari de pujada --}}
    <div style="background-color:#ffffff;border-radius:0.75rem;border:1px solid #e5e7eb;padding:1.25rem;margin-bottom:1rem;" x-data="{ type: 'file' }">
        <p style="font-size:0.875rem;font-weight:600;color:#374151;margin-bottom:0.75rem;">Pujar nou document</p>

        <form action="{{ route('teacher.portal.course.documents.upload', $course->slug) }}"
              method="POST" enctype="multipart/form-data">
            @csrf

            @if ($errors->any())
                <div style="background-color:#fef2f2;border:1px solid #fecaca;color:#991b1b;border-radius:0.5rem;padding:0.75rem 1rem;margin-bottom:1rem;font-size:0.875rem;">
                    <ul style="margin:0;padding-left:1rem;">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:0.75rem;margin-bottom:0.75rem;">
                <div style="grid-column:span 2;">
                    <label style="display:block;font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Títol *</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;box-sizing:border-box;">
                </div>

                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Tipus *</label>
                    <select name="type" x-model="type"
                            style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;">
                        <option value="file">Fitxer</option>
                        <option value="url">Enllaç extern</option>
                    </select>
                </div>

                <div>
                    <label style="display:block;font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Visibilitat *</label>
                    <select name="visibility"
                            style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;">
                        <option value="enrolled" {{ old('visibility','enrolled')==='enrolled'?'selected':'' }}>Alumnes matriculats</option>
                        <option value="public" {{ old('visibility')==='public'?'selected':'' }}>Públic</option>
                        <option value="private" {{ old('visibility')==='private'?'selected':'' }}>Privat (sol professor/a)</option>
                    </select>
                </div>

                <div style="grid-column:span 2;" x-show="type === 'file'">
                    <label style="display:block;font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Fitxer *</label>
                    <input type="file" name="file"
                           accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.jpg,.jpeg,.png,.gif,.webp,.mp4,.mp3,.zip"
                           style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;background:#fff;box-sizing:border-box;">
                    <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.25rem;">PDF, Office, imatges, ZIP · màx. 10 MB</p>
                </div>

                <div style="grid-column:span 2;" x-show="type === 'url'">
                    <label style="display:block;font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">URL *</label>
                    <input type="url" name="url" value="{{ old('url') }}" placeholder="https://..."
                           style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;box-sizing:border-box;">
                </div>

                <div style="grid-column:span 2;">
                    <label style="display:block;font-size:0.75rem;font-weight:500;color:#374151;margin-bottom:0.25rem;">Descripció</label>
                    <textarea name="description" rows="2"
                              style="width:100%;border:1px solid #d1d5db;border-radius:0.375rem;padding:0.5rem 0.75rem;font-size:0.875rem;color:#111827;box-sizing:border-box;resize:vertical;">{{ old('description') }}</textarea>
                </div>
            </div>

            <button type="submit"
                    style="background-color:#4f46e5;color:#ffffff;border:none;border-radius:0.375rem;padding:0.5rem 1.25rem;font-size:0.875rem;font-weight:500;cursor:pointer;">
                Pujar document
            </button>
        </form>
    </div>

    {{-- Llista de documents --}}
    @if ($documents->isEmpty())
        <div style="background-color:#ffffff;border-radius:0.75rem;border:1px solid #e5e7eb;padding:1.5rem;text-align:center;color:#6b7280;font-size:0.875rem;">
            Encara no hi ha documents per a aquest curs.
        </div>
    @else
        <div style="background-color:#ffffff;border-radius:0.75rem;border:1px solid #e5e7eb;overflow:hidden;">
            @foreach ($documents as $doc)
                <div style="display:flex;align-items:center;gap:1rem;padding:0.875rem 1rem;{{ !$loop->last ? 'border-bottom:1px solid #f3f4f6;' : '' }}">
                    {{-- Icona --}}
                    <div style="flex-shrink:0;width:2rem;height:2rem;display:flex;align-items:center;justify-content:center;background-color:#f3f4f6;border-radius:0.375rem;">
                        @if ($doc->type === 'url')
                            <svg style="width:1rem;height:1rem;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                        @else
                            <svg style="width:1rem;height:1rem;color:#6b7280;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                            </svg>
                        @endif
                    </div>

                    {{-- Info --}}
                    <div style="flex:1;min-width:0;">
                        <p style="font-size:0.875rem;font-weight:500;color:#111827;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                            {{ $doc->title }}
                        </p>
                        <p style="font-size:0.75rem;color:#9ca3af;margin-top:0.1rem;">
                            @if ($doc->type === 'file')
                                {{ $doc->file_name ?? basename($doc->file_path ?? '') }}
                                @if ($doc->file_size_formatted) · {{ $doc->file_size_formatted }} @endif
                            @else
                                Enllaç extern
                            @endif
                            ·
                            @if ($doc->visibility === 'public')
                                <span style="color:#15803d;">Públic</span>
                            @elseif ($doc->visibility === 'enrolled')
                                <span style="color:#1d4ed8;">Matriculats</span>
                            @else
                                <span style="color:#6b7280;">Privat</span>
                            @endif
                            @php
                                $docConditions = [];
                                if ($doc->available_from) $docConditions[] = now()->gte($doc->available_from);
                                if ($doc->session_number) $docConditions[] = ($course->sessionsPast() ?? 0) >= $doc->session_number;
                                $docBlocked = !empty($docConditions) && !in_array(true, $docConditions, true);
                            @endphp
                            @if ($docBlocked)
                                · <span style="color:#d97706;" title="Els alumnes no hi tindran accés encara">⏰
                                    @if ($doc->available_from && !now()->gte($doc->available_from))
                                        Des del {{ $doc->available_from->format('d/m/Y') }}
                                    @endif
                                    @if ($doc->session_number)
                                        @if ($doc->available_from && !now()->gte($doc->available_from)) o @endif
                                        sessió {{ $doc->session_number }}
                                        (ara: {{ $course->sessionsPast() ?? 0 }})
                                    @endif
                                </span>
                            @endif
                        </p>
                    </div>

                    {{-- Accions --}}
                    <div style="display:flex;gap:0.5rem;flex-shrink:0;">
                        @if ($doc->download_url)
                            <a href="{{ $doc->download_url }}" target="_blank"
                               style="font-size:0.75rem;color:#4f46e5;text-decoration:none;padding:0.25rem 0.5rem;border:1px solid #e0e7ff;border-radius:0.25rem;background:#eef2ff;">
                                Obrir
                            </a>
                        @endif

                        <form action="{{ route('teacher.portal.course.documents.delete', [$course->slug, $doc->id]) }}"
                              method="POST"
                              onsubmit="return confirm('Eliminar el document \'{{ addslashes($doc->title) }}\'?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit"
                                    style="font-size:0.75rem;color:#dc2626;padding:0.25rem 0.5rem;border:1px solid #fee2e2;border-radius:0.25rem;background:#fef2f2;cursor:pointer;">
                                Eliminar
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

{{-- ── Alumnes matriculats ───────────────────────────────────────────────────── --}}
<h2 style="font-size:1.125rem;font-weight:600;color:#111827;margin-bottom:0.75rem;">
    Alumnes matriculats
    <span style="margin-left:0.5rem;font-size:0.875rem;font-weight:400;color:#6b7280;">({{ $enrollments->count() }})</span>
</h2>

@if ($enrollments->isEmpty())
    <div style="background-color:#ffffff;border-radius:0.75rem;border:1px solid #e5e7eb;padding:1.5rem;text-align:center;color:#6b7280;font-size:0.875rem;">
        Encara no hi ha alumnes matriculats en aquest curs.
    </div>
@else
    <div style="background-color:#ffffff;border-radius:0.75rem;border:1px solid #e5e7eb;box-shadow:0 1px 2px rgba(0,0,0,0.05);overflow:hidden;">
        <table style="width:100%;font-size:0.875rem;border-collapse:collapse;">
            <thead style="background-color:#f9fafb;border-bottom:1px solid #e5e7eb;">
                <tr>
                    <th style="text-align:left;padding:0.75rem 1rem;font-weight:500;color:#6b7280;">Alumne/a</th>
                    <th style="text-align:left;padding:0.75rem 1rem;font-weight:500;color:#6b7280;">Correu</th>
                    <th style="text-align:left;padding:0.75rem 1rem;font-weight:500;color:#6b7280;">Telèfon</th>
                    <th style="text-align:left;padding:0.75rem 1rem;font-weight:500;color:#6b7280;">Matrícula</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($enrollments as $enrollment)
                <tr style="border-bottom:{{ !$loop->last ? '1px solid #f3f4f6' : 'none' }};">
                    <td style="padding:0.75rem 1rem;font-weight:500;color:#111827;">
                        {{ $enrollment->student?->full_name ?? $enrollment->first_name . ' ' . $enrollment->last_name }}
                    </td>
                    <td style="padding:0.75rem 1rem;color:#4b5563;">
                        {{ $enrollment->student?->email ?? $enrollment->email }}
                    </td>
                    <td style="padding:0.75rem 1rem;color:#4b5563;">
                        {{ $enrollment->student?->phone ?? $enrollment->phone ?? '—' }}
                    </td>
                    <td style="padding:0.75rem 1rem;color:#6b7280;">
                        {{ $enrollment->enrollment_date ? \Carbon\Carbon::parse($enrollment->enrollment_date)->format('d/m/Y') : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
