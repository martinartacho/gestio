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

<h2 class="text-lg font-semibold text-gray-900 mb-3">
    Alumnes matriculats
    <span class="ml-2 text-sm font-normal text-gray-500">({{ $enrollments->count() }})</span>
</h2>

@if ($enrollments->isEmpty())
    <div class="bg-white rounded-xl border border-gray-200 p-6 text-center text-gray-500 text-sm">
        Encara no hi ha alumnes matriculats en aquest curs.
    </div>
@else
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Alumne/a</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Correu</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Telèfon</th>
                    {{-- <th class="text-left px-4 py-3 font-medium text-gray-500">Estat</th> --}}                    <th class="text-left px-4 py-3 font-medium text-gray-500">Matrícula</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($enrollments as $enrollment)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $enrollment->student?->full_name ?? $enrollment->first_name . ' ' . $enrollment->last_name }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $enrollment->student?->email ?? $enrollment->email }}
                    </td>
                    <td class="px-4 py-3 text-gray-600">
                        {{ $enrollment->student?->phone ?? $enrollment->phone ?? '—' }}
                    </td>
                    {{-- <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $enrollment->status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-orange-100 text-orange-700' }}">
                            {{ \App\Models\CampusEnrollment::STATUSES[$enrollment->status] ?? $enrollment->status }}
                        </span>
                    </td> --}}
                    <td class="px-4 py-3 text-gray-500">
                        {{ $enrollment->enrollment_date ? \Carbon\Carbon::parse($enrollment->enrollment_date)->format('d/m/Y') : '—' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif
@endsection
