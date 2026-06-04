@extends('associats.layouts.app')

@section('title', 'Recuperar contrasenya · ' . setting('associats_org_name', 'Entitat'))

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        <div class="text-center mb-6">
            <p class="text-xs font-semibold tracking-widest uppercase text-indigo-600 mb-1">
                {{ setting('associats_org_name', 'Entitat') }}
            </p>
            <h1 class="text-2xl font-bold text-gray-900">Recuperar contrasenya</h1>
            <p class="text-sm text-gray-500 mt-1">
                Introduïu el vostre correu electrònic o el número de soci.
            </p>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if (session('info'))
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-800 rounded-lg text-sm">
                {{ session('info') }}
            </div>
        @endif

        @if (! session('success'))
        <form method="POST" action="{{ route('member.password.email') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Correu electrònic o número de soci
                </label>
                <input type="text" name="identifier" value="{{ old('identifier') }}"
                       required autofocus placeholder="Ex: maria@correu.cat  o  1947"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('identifier') border-red-400 @enderror">
                @error('identifier')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Enviar instruccions
            </button>
        </form>
        @endif

        <p class="text-center text-sm text-gray-500 mt-4">
            <a href="{{ route('member.login') }}" class="text-indigo-600 hover:underline">← Tornar a l'accés</a>
        </p>

    </div>
</div>
@endsection
