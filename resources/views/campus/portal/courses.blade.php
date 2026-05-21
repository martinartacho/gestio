@extends('campus.layouts.app')

@section('title', 'Els meus cursos')

@section('content')
<div class="mb-6">
    <h1 class="text-2xl font-bold text-gray-900">Els meus cursos</h1>
    <p class="text-gray-500 mt-1">Hola, {{ auth('student')->user()->full_name }}</p>
</div>

@if ($enrollments->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-10 text-center text-gray-400">
        <p class="text-lg">Encara no estàs inscrit/a a cap curs.</p>
        <a href="{{ route('campus.catalog.index') }}" class="mt-4 inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg hover:bg-indigo-700 transition text-sm font-medium">
            Veure el catàleg de cursos
        </a>
    </div>
@else
    <div class="space-y-4">
        @foreach ($enrollments as $enrollment)
            @php
                $statusColors = \App\Models\CampusEnrollment::STATUS_COLORS;
                $statusLabels = \App\Models\CampusEnrollment::STATUSES;
                $color = $statusColors[$enrollment->status] ?? 'gray';
                $colorClasses = [
                    'success' => 'bg-green-100 text-green-800',
                    'warning' => 'bg-yellow-100 text-yellow-800',
                    'danger'  => 'bg-red-100 text-red-800',
                    'gray'    => 'bg-gray-100 text-gray-600',
                ];
            @endphp
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-xs text-gray-400">{{ $enrollment->course->code }}</span>
                        @if ($enrollment->course->category)
                            <span class="text-xs px-2 py-0.5 rounded-full"
                                  style="background-color: {{ $enrollment->course->category->color ?? '#e5e7eb' }}22; color: {{ $enrollment->course->category->color ?? '#6b7280' }}">
                                {{ $enrollment->course->category->name }}
                            </span>
                        @endif
                    </div>
                    <h2 class="font-semibold text-gray-900">{{ $enrollment->course->title }}</h2>
                     <div>
                         @if ($enrollment->course->description !== null)
                            {{ $enrollment->course->description }}
                        @endif
                    </div>
                    <div class="text-sm text-gray-500 mt-1">
                        @if ($enrollment->course->start_date)
                            {{ $enrollment->course->start_date->format('d/m/Y') }}
                            @if ($enrollment->course->end_date) — {{ $enrollment->course->end_date->format('d/m/Y') }} @endif
                        @endif
                        @if ($enrollment->course->season)
                            · {{ $enrollment->course->season->name }}
                        @endif
                    </div>
                        @if ($enrollment->course->calendar_notes !== null)
                    <div class="text-sm text-gray-500 mt-1">{{ $enrollment->course->calendar_notes }}</div>
                    @endif

                    {{-- Accés LMS --}}
                    @if (setting('lms_enabled') && ($enrollment->course->published_lessons_count ?? 0) > 0)
                    <div style="margin-top:0.875rem;">
                        <a href="{{ route('campus.lms.course', $enrollment->course->slug) }}"
                           style="display:inline-flex;align-items:center;gap:0.4rem;background:#4f46e5;color:#fff;font-size:0.8125rem;font-weight:600;padding:0.45rem 1rem;border-radius:0.5rem;text-decoration:none;"
                           onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4f46e5'">
                            <svg style="width:0.9rem;height:0.9rem;flex-shrink:0;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                            Accedir al curs en línia
                            <span style="font-size:0.7rem;opacity:0.8;">({{ $enrollment->course->published_lessons_count }} sessions)</span>
                        </a>
                    </div>
                    @endif

                    {{-- Documents accessibles --}}
                    @php $docs = $documentsByCourse[$enrollment->course_id] ?? collect(); @endphp
                    @if ($docs->isNotEmpty())
                    <div style="margin-top:0.75rem;border-top:1px solid #f3f4f6;padding-top:0.75rem;">
                        <p style="font-size:0.75rem;font-weight:600;color:#6b7280;margin-bottom:0.5rem;text-transform:uppercase;letter-spacing:0.05em;">Documents</p>
                        <div style="display:flex;flex-direction:column;gap:0.375rem;">
                            @foreach ($docs as $doc)
                            <a href="{{ $doc->download_url }}" target="_blank"
                               style="display:flex;align-items:center;gap:0.5rem;font-size:0.8125rem;color:#4f46e5;text-decoration:none;">
                                @if ($doc->type === 'url')
                                    <svg style="width:0.875rem;height:0.875rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                    </svg>
                                @else
                                    <svg style="width:0.875rem;height:0.875rem;flex-shrink:0;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                @endif
                                <span>{{ $doc->title }}</span>
                                @if ($doc->file_size_formatted)
                                    <span style="color:#9ca3af;font-size:0.75rem;">· {{ $doc->file_size_formatted }}</span>
                                @endif
                            </a>
                            @endforeach
                        </div>
                    </div>
                    @endif

                </div>
                <div class="text-right shrink-0">
                    <span class="inline-block text-xs font-semibold px-2.5 py-1 rounded-full {{ $colorClasses[$color] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $statusLabels[$enrollment->status] ?? $enrollment->status }}
                    </span>
                    @if ($enrollment->amount)
                        <div class="text-sm font-bold text-gray-700 mt-1">
                            {{ number_format($enrollment->amount, 2, ',', '.') }} €
                        </div>
                    @endif
                    @if ($enrollment->paid_at)
                        <div class="text-xs text-gray-400 mt-0.5">{{ $enrollment->paid_at->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
@endsection
