@extends('campus.layouts.app')

@section('title', 'Recuperar contrasenya')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-full mb-3">
                <svg style="width:1.75rem;height:1.75rem;color:#4f46e5;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Recuperar contrasenya</h1>
            <p class="text-gray-500 text-sm mt-1">
                Introduïu el vostre correu i us enviarem un codi per restablir la contrasenya.
            </p>
        </div>

        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-lg px-4 py-3 mb-4">
                {{ session('info') }}
            </div>
        @endif
        @error('email')
            <div class="bg-red-50 border border-red-200 text-red-700 text-sm rounded-lg px-4 py-3 mb-4">
                {{ $message }}
            </div>
        @enderror

        <form method="POST" action="{{ route('campus.password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correu electrònic</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       required autofocus
                       placeholder="el.vostre@correu.cat"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2.5 text-sm focus:outline-none
                              focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Enviar codi de recuperació
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('campus.login') }}" class="text-sm text-indigo-600 hover:underline">
                ← Tornar a l'inici de sessió
            </a>
        </div>
    </div>
</div>
@endsection
