@extends('campus.layouts.app')

@section('title', 'Cursos disponibles')

@section('content')
<div class="mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-3xl font-bold text-gray-900">Cursos disponibles</h1>
        @if ($selectedSeason)
            <p class="text-gray-500 mt-1">{{ $selectedSeason->name }}</p>
            @if ($selectedSeason->start_date_enrollment && $selectedSeason->end_date_enrollment)
                <p class="text-xs text-indigo-600 mt-0.5">
                    Inscripcions: {{ $selectedSeason->start_date_enrollment->format('d/m/Y') }} – {{ $selectedSeason->end_date_enrollment->format('d/m/Y') }}
                    @if ($selectedSeason->enrollmentIsOpen())
                        <span class="ml-1 bg-green-100 text-green-700 px-1.5 py-0.5 rounded font-semibold">Obertes</span>
                    @elseif ($selectedSeason->isPast() || now() > $selectedSeason->end_date_enrollment)
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
            <a href="{{ route('campus.catalog.index', ['season' => $s->id]) }}"
               class="px-3 py-1.5 rounded-full border transition
                      {{ $s->id === $selectedSeason?->id
                          ? 'bg-indigo-600 text-white border-indigo-600'
                          : 'bg-white text-gray-600 border-gray-300 hover:border-indigo-400' }}">
                {{ $s->name }}
                @if ($s->is_active) <span class="ml-0.5 text-xs">★</span> @endif
            </a>
        @endforeach
    </div>
    @endif
</div>

@if ($courses->isEmpty())
    <div class="text-center py-16 text-gray-400">
        <p class="text-lg">No hi ha cursos disponibles en aquest moment.</p>
    </div>
@else
    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($courses as $course)
            <a href="{{ route('campus.catalog.show', $course->slug) }}"
               class="bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md hover:border-indigo-300 transition p-5 flex flex-col">
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
