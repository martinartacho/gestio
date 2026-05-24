@extends('campus.layouts.app')

@section('title', 'El vostre torn')

@section('content')
<div class="max-w-md mx-auto">

    @if ($manyReloads ?? false)
    <div class="bg-amber-50 border border-amber-300 rounded-xl px-4 py-3 text-sm text-amber-800 mb-4 flex items-start gap-2">
        <span class="shrink-0 text-base">⚠️</span>
        <div>
            <strong>Moltes sol·licituds des del vostre dispositiu.</strong>
            Recarregar repetidament no avança el torn.
            Si continueu, l'accés podria ser restringit temporalment.
        </div>
    </div>
    @endif

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8 text-center">

        @if ($entry->status === 'waiting')
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-1">Sou a la cua</h1>
            <p class="text-4xl font-bold text-indigo-600 my-3">#{{ $entry->queue_number }}</p>
            <p class="text-gray-500 text-sm">Hora estimada d'accés:</p>
            <p class="font-semibold text-gray-800 text-lg">{{ $entry->slotTimeLabel() }}</p>
            <p class="text-xs text-gray-400 mt-4">Rebreu un correu amb el codi d'accés quan arribi el vostre torn. No cal que espereu aquí.</p>

            {{-- Avís per a nous estudiants sense compte --}}
            @guest('student')
            <div class="mt-5 text-left bg-amber-50 border border-amber-200 rounded-xl p-4">
                <p class="text-sm font-semibold text-amber-800 mb-1">⚠️ Mentre espereu, creeu el vostre compte</p>
                <p class="text-xs text-amber-700 mb-3">
                    Per completar la inscripció quan arribi el vostre torn necessiteu un compte actiu.
                    Creeu-lo ara (és gratuït) o inicieu sessió si ja en teniu.
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('campus.register') }}"
                       class="flex-1 text-center bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                        Crear compte
                    </a>
                    <a href="{{ route('campus.login') }}"
                       class="flex-1 text-center border border-amber-300 text-amber-800 text-xs font-semibold px-3 py-2 rounded-lg hover:bg-amber-100 transition">
                        Ja tinc compte
                    </a>
                </div>
            </div>
            @endguest

        @elseif ($entry->status === 'notified')
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-1">🚀 Ara és el vostre torn!</h1>
            <p class="text-gray-500 text-sm mb-4">Introduïu el codi que heu rebut per correu:</p>
            <form method="POST" action="{{ route('campus.queue.code') }}" class="space-y-3">
                @csrf
                <input type="text" name="code" maxlength="7" placeholder="000 AAA" autofocus
                       class="w-full text-center font-mono font-bold text-2xl tracking-widest uppercase border
                              border-gray-300 rounded-lg px-4 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500
                              @error('code') border-red-400 @enderror">
                @error('code') <p class="text-red-600 text-xs">{{ $message }}</p> @enderror
                <button type="submit" class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Accedir al catàleg
                </button>
            </form>
            <p class="text-xs text-gray-400 mt-3">
                Vàlid fins: {{ $entry->access_expires_at?->format('H:i') }} h
            </p>

        @elseif ($entry->status === 'accessed')
            <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-2">Torn completat</h1>
            <p class="text-gray-500 text-sm">Heu accedit al catàleg el {{ $entry->accessed_at?->format('d/m/Y \a\l\e\s H:i') }} h.</p>
            <a href="{{ route('campus.catalog.index') }}"
               class="inline-block mt-4 bg-indigo-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition text-sm">
                Tornar al catàleg
            </a>

        @else {{-- expired --}}
            <div class="inline-flex items-center justify-center w-16 h-16 bg-red-100 rounded-full mb-4">
                <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-2">Torn caducat</h1>
            <p class="text-gray-500 text-sm mb-4">El temps per accedir ha expirat. Podeu tornar a apuntar-vos.</p>
            <a href="{{ route('campus.queue.join') }}"
               class="inline-block bg-indigo-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition text-sm">
                Tornar a apuntar-se
            </a>
        @endif

    </div>
</div>
@endsection
