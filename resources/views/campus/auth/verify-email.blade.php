@extends('campus.layouts.app')

@section('title', 'Verifica el teu correu')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-full mb-3">
                <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0
                             1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25
                             0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5
                             4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0
                             1-1.07-1.916V6.75"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Introdueix el codi de verificació</h1>
            <p class="text-gray-500 text-sm mt-1">
                Hem enviat un codi de 6 caràcters a<br>
                <strong>{{ auth('student')->user()->email }}</strong>
            </p>
        </div>

        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-lg px-4 py-3 mb-4">
                {{ session('info') }}
            </div>
        @endif
        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('campus.verification.code') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2 text-center">Codi de verificació</label>
                <input type="text"
                       name="code"
                       maxlength="7"
                       autocomplete="one-time-code"
                       autofocus
                       placeholder="000 AAA"
                       class="w-full text-center text-2xl font-mono font-bold tracking-widest border border-gray-300
                              rounded-lg px-4 py-3 uppercase focus:outline-none focus:ring-2 focus:ring-indigo-500
                              @error('code') border-red-400 @enderror"
                       value="{{ old('code') }}">
                <p class="text-xs text-gray-400 text-center mt-1">3 números + 3 lletres</p>
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Verificar compte
            </button>
        </form>

        <div class="mt-5 text-center space-y-2">
            <form method="POST" action="{{ route('campus.verification.resend') }}">
                @csrf
                <button type="submit" class="text-sm text-indigo-600 hover:underline">
                    No heu rebut el codi? Reenviar
                </button>
            </form>
            <div class="text-xs text-gray-400">El codi caduca als 15 minuts</div>
            <form method="POST" action="{{ route('campus.logout') }}" class="pt-2">
                @csrf
                <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">Tancar sessió</button>
            </form>
        </div>
    </div>
</div>
@endsection
