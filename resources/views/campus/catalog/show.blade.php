@extends('campus.layouts.app')

@section('title', $course->title)

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('campus.catalog.index') }}" class="text-sm text-indigo-600 hover:underline mb-4 inline-block">← Tornar al catàleg</a>

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        <div class="flex items-center gap-3 mb-2">
            @if ($course->category)
                <span class="text-xs font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full"
                      style="background-color: {{ $course->category->color ?? '#e5e7eb' }}22; color: {{ $course->category->color ?? '#6b7280' }}">
                    {{ $course->category->name }}
                </span>
            @endif
            <span class="text-xs text-gray-400">{{ $course->code }}</span>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $course->title }}</h1>

        @if ($course->description)
            <p class="text-gray-600 mb-6">{{ $course->description }}</p>
        @endif

        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 mb-6">
            @if ($course->start_date)
                <div><span class="font-medium text-gray-800">Inici:</span> {{ $course->start_date->format('d/m/Y') }}</div>
            @endif
            @if ($course->end_date)
                <div><span class="font-medium text-gray-800">Final:</span> {{ $course->end_date->format('d/m/Y') }}</div>
            @endif
            @if ($course->sessions)
                <div><span class="font-medium text-gray-800">Sessions:</span> {{ $course->sessions }}</div>
            @endif
            @if ($course->hours)
                <div><span class="font-medium text-gray-800">Hores:</span> {{ $course->hours }}h</div>
            @endif
            @if ($course->space)
                <div><span class="font-medium text-gray-800">Lloc:</span> {{ $course->space->name }}</div>
            @endif
            @if ($course->format)
                <div><span class="font-medium text-gray-800">Format:</span> {{ \App\Models\CampusCourse::FORMATS[$course->format] ?? $course->format }}</div>
            @endif
        </div>

        @if ($course->objectives)
            <div class="mb-6">
                <h2 class="font-semibold text-gray-800 mb-1">Objectius</h2>
                <p class="text-gray-600 text-sm">{{ $course->objectives }}</p>
            </div>
        @endif

        @if ($course->requirements)
            <div class="mb-6">
                <h2 class="font-semibold text-gray-800 mb-1">Requisits previs</h2>
                <p class="text-gray-600 text-sm">{{ $course->requirements }}</p>
            </div>
        @endif

        <div class="flex items-center justify-between border-t border-gray-100 pt-6 mt-4">
            <div class="text-2xl font-bold text-indigo-700">
                {{ $course->price ? number_format($course->price, 2, ',', '.') . ' €' : 'Gratuït' }}
            </div>

            @if ($alreadyEnrolled)
                <span class="bg-green-100 text-green-800 text-sm font-medium px-4 py-2 rounded-lg">
                    Ja estàs inscrit/a
                </span>
            @elseauth('student')
                <form method="POST" action="{{ route('campus.checkout.create', $course->slug) }}">
                    @csrf
                    <button type="submit"
                            class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        Inscriure'm — {{ $course->price ? number_format($course->price, 2, ',', '.') . ' €' : 'Gratuït' }}
                    </button>
                </form>
            @else
                <a href="{{ route('campus.login') }}?redirect={{ urlencode(request()->url()) }}"
                   class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Identificar-se per inscriure's
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
