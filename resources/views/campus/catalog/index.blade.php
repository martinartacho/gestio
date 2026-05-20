@extends('campus.layouts.app')

@section('title', 'Cursos disponibles')

@section('content')

@if ($isPreview)
<div class="mb-4 flex items-center gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
    <svg class="h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
    </svg>
    <span><strong>Mode previsualització</strong> — Estàs veient cursos i temporades no publicats. Els alumnes no veuen aquesta vista.</span>
    <a href="{{ route('campus.catalog.index', ['season' => request('season')]) }}"
       class="ml-auto shrink-0 underline hover:no-underline">Sortir del preview</a>
</div>
@endif

<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Cursos disponibles</h1>
        @if ($selectedSeason)
            <p class="text-gray-500 mt-1 flex items-center gap-2">
                {{ $selectedSeason->name }}
                @php
                    $color = \App\Models\CampusSeason::STATUS_COLORS[$selectedSeason->status] ?? 'gray';
                    $label = \App\Models\CampusSeason::STATUSES[$selectedSeason->status] ?? $selectedSeason->status;
                    $bgMap = ['success' => 'bg-green-100 text-green-700', 'danger' => 'bg-red-100 text-red-600', 'gray' => 'bg-gray-100 text-gray-500'];
                @endphp
                @if ($isPreview)
                    <span class="text-xs px-1.5 py-0.5 rounded font-semibold {{ $bgMap[$color] ?? 'bg-gray-100 text-gray-500' }}">
                        {{ $label }}
                    </span>
                @endif
            </p>
            @if ($selectedSeason->start_date_enrollment && $selectedSeason->end_date_enrollment)
                <p class="text-xs text-indigo-600 mt-0.5">
                    Inscripcions: {{ $selectedSeason->start_date_enrollment->format('d/m/Y') }} – {{ $selectedSeason->end_date_enrollment->format('d/m/Y') }}
                    @if ($selectedSeason->enrollmentIsOpen())
                        <span class="ml-1 bg-green-100 text-green-700 px-1.5 py-0.5 rounded font-semibold">Obertes</span>
                    @elseif ($selectedSeason->isClosed() || now() > $selectedSeason->end_date_enrollment)
                        <span class="ml-1 bg-gray-100 text-gray-500 px-1.5 py-0.5 rounded">Tancades</span>
                    @else
                        <span class="ml-1 bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">Properament</span>
                    @endif
                </p>
            @endif
        @endif
    </div>

    @if ($seasons->count() > 1)
    <div class="flex flex-wrap gap-2 text-sm">
        @foreach ($seasons as $s)
            <a href="{{ route('campus.catalog.index', array_filter(['season' => $s->id, 'preview' => $isPreview ? 1 : null])) }}"
               class="px-3 py-1.5 rounded-full border transition
                      {{ $s->id === $selectedSeason?->id
                          ? 'bg-indigo-600 text-white border-indigo-600'
                          : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400' }}">
                {{ $s->name }}
                @if ($s->isActive()) <span class="ml-0.5 text-xs">★</span> @endif
                @if ($isPreview && ! $s->isActive())
                    <span class="ml-1 text-xs opacity-60">({{ \App\Models\CampusSeason::STATUSES[$s->status] ?? '' }})</span>
                @endif
            </a>
        @endforeach
    </div>
    @endif
</div>

@if ($courses->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <p class="text-lg">No hi ha cursos disponibles en aquest moment.</p>
        @if ($isPreview)
            <p class="text-sm mt-2">En mode preview es mostren tots els cursos (actius, esborranys i no públics).</p>
        @endif
    </div>
@else
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($courses as $course)
            <a href="{{ route('campus.catalog.show', array_filter(['slug' => $course->slug, 'preview' => $isPreview ? 1 : null])) }}"
               class="bg-white rounded-xl border shadow-sm hover:shadow-md hover:border-indigo-300 transition p-5 flex flex-col
                      {{ $isPreview && (! $course->is_public || $course->status !== 'active') ? 'border-amber-200' : 'border-gray-200' }}">

                @if ($isPreview && (! $course->is_public || $course->status !== 'active'))
                    <div class="text-xs text-amber-600 font-semibold mb-2 flex gap-2">
                        @if (! $course->is_public) <span>No públic</span> @endif
                        @if ($course->status !== 'active') <span>{{ \App\Models\CampusCourse::STATUSES[$course->status] ?? $course->status }}</span> @endif
                    </div>
                @endif

                <div class="flex items-start justify-between mb-3">
                    <span class="text-xs font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full"
                          style="background-color: {{ $course->category->color ?? '#e5e7eb' }}22; color: {{ $course->category->color ?? '#6b7280' }}">
                        {{ $course->category->name ?? '—' }}
                    </span>
                    <span class="text-xs text-gray-400">{{ $course->code }}</span>
                </div>
                <h2 class="font-semibold text-gray-900 text-base mb-2 flex-1">{{ $course->title }}</h2>
                <div class="text-sm text-gray-500 space-y-1 mt-auto">
                    @if ($course->start_date)
                        <div>{{ $course->start_date->format('d/m/Y') }}
                            @if ($course->end_date) — {{ $course->end_date->format('d/m/Y') }} @endif
                        </div>
                    @endif
                    @if ($course->space || $course->format)
                        <div>
                            {{ $course->space->name ?? '—' }}
                            @if ($course->format)
                                · {{ \App\Models\CampusCourse::FORMATS[$course->format] ?? $course->format }}
                            @endif
                        </div>
                    @endif
                    <div class="flex items-center justify-between pt-2 border-t border-gray-100 mt-2">
                        <span class="text-indigo-700 font-bold text-base">
                            {{ $course->price ? number_format($course->price, 2, ',', '.') . ' €' : 'Gratuït' }}
                        </span>
                        @if ($course->sessions)
                            <span class="text-xs text-gray-400">{{ $course->sessions }} sessions</span>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>
@endif
@endsection
