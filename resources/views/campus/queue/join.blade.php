@extends('campus.layouts.app')

@section('title', 'Cua d\'inscripcions')

@section('content')
<div class="max-w-lg mx-auto">

    <div class="bg-white rounded-xl border border-indigo-200 shadow-sm p-8 text-center">

        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94
                         4.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17
                         0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971
                         0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0
                         0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971
                         5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25
                         2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5
                         0 2.25 2.25 0 0 1 4.5 0Z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-2">Cua d'inscripcions</h1>
        <p class="text-gray-500 text-sm mb-2">
            Per garantir un accés equitatiu, les inscripcions s'obren per torns.
        </p>

        @php
            $queueStart = $settings->get('queue_start_at') ? \Carbon\Carbon::parse($settings->get('queue_start_at')) : null;
            $total = \App\Models\CampusQueueEntry::count();
        @endphp

        @if ($queueStart && $queueStart->isFuture())
        <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700 mb-6">
            Les inscripcions s'obren el <strong>{{ $queueStart->format('d/m/Y') }} a les {{ $queueStart->format('H:i') }} h</strong>.
            Apunteu-vos ara per reservar el torn.
        </div>
        @endif

        @if ($total > 0)
        <p class="text-xs text-gray-400 mb-4">{{ $total }} {{ $total === 1 ? 'persona apuntada' : 'persones apuntades' }}</p>
        @endif

        <form method="POST" action="{{ route('campus.queue.store') }}" class="space-y-4 text-left">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correu electrònic</label>
                <input type="email" name="email" value="{{ old('email', auth('student')->user()?->email) }}"
                       required autofocus
                       placeholder="el.vostre@correu.cat"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none
                              focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Reservar torn →
            </button>
        </form>

        <p class="text-xs text-gray-400 mt-4">
            Rebreu un email amb el número de torn i l'hora estimada d'accés.
        </p>

        {{-- Formulari d'introducció de codi si ja el tenen --}}
        <div class="mt-6 pt-5 border-t border-gray-100">
            <p class="text-sm font-medium text-gray-600 mb-3">Ja teniu el codi d'accés?</p>
            <form method="POST" action="{{ route('campus.queue.code') }}" class="flex gap-2">
                @csrf
                <input type="text" name="code" maxlength="6" placeholder="000AAA"
                       class="flex-1 text-center font-mono font-bold text-lg tracking-widest uppercase border border-gray-300
                              rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500
                              @error('code') border-red-400 @enderror">
                <button type="submit"
                        class="bg-green-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-green-700 transition text-sm">
                    Entrar
                </button>
            </form>
            @error('code') <p class="text-red-600 text-xs mt-1 text-center">{{ $message }}</p> @enderror
        </div>
    </div>
</div>
@endsection
