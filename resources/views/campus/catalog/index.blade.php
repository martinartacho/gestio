@extends('campus.layouts.app')

@section('title', 'Cursos disponibles')

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Cursos disponibles</h1>
    @if ($activeSeason)
        <p class="text-gray-500 mt-1">{{ $activeSeason->name }}</p>
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
                    @if ($course->space)
                        <div>{{ $course->space->name }}</div>
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
