@extends('campus.layouts.app')

@section('title', 'Registre d\'alumne')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Crear compte</h1>

        <form method="POST" action="{{ route('campus.register.post') }}" class="space-y-4">
            @csrf
            {{-- Honeypot: els bots omplen camps ocults; els humans no els veuen --}}
            <div style="display:none" aria-hidden="true">
                <input type="text" name="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nom</label>
                    <input type="text" name="first_name" value="{{ old('first_name') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('first_name') border-red-400 @enderror">
                    @error('first_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cognoms</label>
                    <input type="text" name="last_name" value="{{ old('last_name') }}" required
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('last_name') border-red-400 @enderror">
                    @error('last_name') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correu electrònic</label>
                <input type="email" name="email" value="{{ old('email') }}" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                @error('email') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telèfon (opcional)</label>
                <input type="tel" name="phone" value="{{ old('phone') }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contrasenya</label>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-400 @enderror">
                @error('password') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contrasenya</label>
                <input type="password" name="password_confirmation" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-start gap-2">
                <input type="checkbox" name="data_consent" id="data_consent" required class="mt-0.5 rounded">
                <label for="data_consent" class="text-sm text-gray-600">
                    Accepto el tractament de les meves dades personals per a la gestió de la inscripció (RGPD).
                </label>
                @error('data_consent') <p class="text-red-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Crear compte
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-4">
            Ja tens compte?
            <a href="{{ route('campus.login') }}" class="text-indigo-600 hover:underline">Accedir</a>
        </p>
    </div>
</div>
@endsection
