@extends('campus.layouts.app')

@section('title', 'Nova contrasenya')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-full mb-3">
                <svg style="width:1.75rem;height:1.75rem;color:#4f46e5;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Introduïu el codi i la nova contrasenya</h1>
            <p class="text-gray-500 text-sm mt-1">
                Hem enviat un codi de 6 caràcters a<br>
                <strong>{{ session('password_reset_email') }}</strong>
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

        <form method="POST" action="{{ route('campus.password.reset') }}" class="space-y-4">
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

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nova contrasenya</label>
                <input type="password" name="password" required
                       placeholder="Mínim 8 caràcters"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none
                              focus:ring-2 focus:ring-indigo-500 @error('password') border-red-400 @enderror">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contrasenya</label>
                <input type="password" name="password_confirmation" required
                       placeholder="Repetiu la contrasenya"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none
                              focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Restablir contrasenya
            </button>
        </form>

        <div class="mt-4 text-center">
            <form method="POST" action="{{ route('campus.password.email') }}" class="inline">
                @csrf
                <input type="hidden" name="email" value="{{ session('password_reset_email') }}">
                <button type="submit" class="text-sm text-indigo-600 hover:underline">
                    No heu rebut el codi? Reenviar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
