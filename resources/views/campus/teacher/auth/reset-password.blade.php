@extends('campus.teacher.layouts.app')

@section('title', 'Accés amb codi — Professorat')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-2">Introduïu el codi</h1>
        <p class="text-sm text-gray-500 mb-6">Hem enviat un codi de 6 caràcters al vostre correu. Introduïu-lo per accedir.</p>

        @if (session('info'))
            <div class="mb-4 rounded-lg bg-blue-50 border border-blue-200 px-4 py-3 text-sm text-blue-700">
                {{ session('info') }}
            </div>
        @endif

        <form method="POST" action="{{ route('teacher.password.reset') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Codi de verificació</label>
                <input type="text" name="code" value="{{ old('code') }}" required autofocus
                       maxlength="7" placeholder="ex: 482 KNT"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm font-mono tracking-widest text-center text-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('code') border-red-400 @enderror">
                @error('code')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Accedir
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-gray-500">
            <a href="{{ route('teacher.password.request') }}" class="text-indigo-600 hover:underline">Sol·licitar un nou codi</a>
        </p>
    </div>
</div>
@endsection
