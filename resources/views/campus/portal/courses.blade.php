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
                    <div  class="text-sm text-gray-500 mt-1">
                         @if ($enrollment->course->calendar_notes !== null)
                            {{ $enrollment->course->calendar_notes }}
                        @endif
                    </div>
                    

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
