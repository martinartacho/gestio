@extends('campus.teacher.layouts.app')

@section('title', 'Recuperar contrasenya — Professorat')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Recuperar contrasenya</h1>
        <p class="text-sm text-gray-500 mb-6">Introduïu el vostre correu i us enviarem un codi de 6 caràcters.</p>

        @if (session('info'))
            <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                {{ session('info') }}
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.password.send') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correu electrònic</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Enviar codi
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-500">
            <a href="{{ route('teacher.login') }}" class="text-indigo-600 hover:underline">Tornar a l'accés</a>
        </p>
    </div>
</div>
@endsection
