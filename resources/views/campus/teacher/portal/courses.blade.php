@extends('campus.teacher.layouts.app')

@section('title', 'Els meus cursos')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">Els meus cursos</h1>

@if ($courses->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-8 text-center text-gray-500">
        No tens cap curs assignat en aquest moment.
    </div>
@else
    <div class="space-y-4">
        @foreach ($courses as $course)
        <a href="{{ route('teacher.portal.course', $course->slug) }}"
           class="block bg-white rounded-xl border border-gray-200 shadow-sm hover:shadow-md transition p-5">
            <div class="flex items-start justify-between gap-4">

                {{-- Informació principal --}}
                <div class="flex-1 min-w-0">
                    {{-- Badges de capçalera --}}
                    <div class="flex flex-wrap items-center gap-2 mb-1">
                        <span class="text-xs font-mono text-gray-400">{{ $course->code }}</span>

                        {{-- Estat del curs --}}
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium
                            {{ $course->status === 'active' ? 'bg-green-100 text-green-700' : ($course->status === 'planning' ? 'bg-blue-100 text-blue-700' : ($course->status === 'closed' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-500')) }}">
                            {{ \App\Models\CampusCourse::STATUSES[$course->status] ?? $course->status }}
                        </span>

                        {{-- Rol del professor --}}
                        @if ($course->pivot->role === 'main')
                            <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-100 text-indigo-700 font-medium">Principal</span>
                        @else
                            <span class="text-xs px-2 py-0.5 rounded-full bg-gray-100 text-gray-500">Col·laborador/a</span>
                        @endif

                        {{-- Badge alumnes --}}
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-violet-100 text-violet-700">
                            {{ $course->students_count }}
                            {{ $course->students_count === 1 ? 'alumne' : 'alumnes' }}
                        </span>
                    </div>

                    <h2 class="text-base font-semibold text-gray-900 truncate">{{ $course->title }}</h2>

                    <p class="text-sm text-gray-500 mt-0.5">
                        {{ $course->season?->name }}
                        @if ($course->start_date)
                            · {{ $course->start_date->format('d/m/Y') }}
                            @if ($course->end_date)
                                – {{ $course->end_date->format('d/m/Y') }}
                            @endif
                        @endif
                        @if ($course->sessions !== null)
                            {{ $course->calendar_notes }}
                        @endif

                    </p>

                    {{-- Sessions: passades vs per fer --}}
                    @if ($course->sessions && $course->sessions_past !== null)
                        @php
                            $past   = $course->sessions_past;
                            $future = $course->sessions - $past;
                            $pct    = $course->sessions > 0 ? round($past / $course->sessions * 100) : 0;
                            $filled = (int) round($pct / 10);
                            $bar    = str_repeat('█', $filled) . str_repeat('░', 10 - $filled);
                        @endphp
                        <p class="mt-2 text-xs font-mono text-gray-500 whitespace-nowrap">
                            @if ($past === 0)
                                ● <span class="text-gray-400">{{ $course->sessions }} sessions per fer</span>
                                &nbsp; [{{ $bar }} {{ $pct }}%]
                            @elseif ($future === 0)
                                ● <span class="text-gray-400">{{ $past }} / {{ $course->sessions }} fetes</span>
                                &nbsp; [{{ $bar }} {{ $pct }}%]
                            @else
                                ● <span class="text-gray-500">{{ $past }} fetes</span>
                                &nbsp;·&nbsp;
                                <span class="text-indigo-600 font-medium">{{ $future }} per fer</span>
                                &nbsp; [{{ $bar }} {{ $pct }}%]
                            @endif
                        </p>
                    @elseif ($course->sessions)
                        <p class="text-xs text-gray-400 mt-1.5 font-mono">● {{ $course->sessions }} sessions</p>
                    @endif
                </div>

                {{-- Dades de la dreta --}}
                <div class="text-right shrink-0 text-sm text-gray-500 space-y-0.5">
                    @if ($course->pivot->sessions_assigned)
                        <p class="text-xs text-indigo-600 font-medium">{{ $course->pivot->sessions_assigned }} assignades</p>
                    @endif
                    <p>{{ $course->space?->name ?? '—' }}</p>
                    <p class="text-xs text-gray-400">{{ $course->timeSlot?->description ?? '' }}</p>
                </div>

            </div>
        </a>
        @endforeach
    </div>
@endif
@endsection
