@extends('associats.layouts.app')

@section('title', 'Nova contrasenya · ' . setting('associats_org_name', 'Entitat'))

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        <div class="text-center mb-6">
            <p class="text-xs font-semibold tracking-widest uppercase text-indigo-600 mb-1">
                {{ setting('associats_org_name', 'Entitat') }}
            </p>
            <h1 class="text-2xl font-bold text-gray-900">Nova contrasenya</h1>
            <p class="text-sm text-gray-500 mt-1">Trieu una contrasenya segura (mínim 8 caràcters).</p>
        </div>

        <form method="POST" action="{{ route('member.password.update', $token) }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nova contrasenya</label>
                <input type="password" name="password" required autofocus minlength="8"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contrasenya</label>
                <input type="password" name="password_confirmation" required minlength="8"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Desar nova contrasenya
            </button>
        </form>

    </div>
</div>
@endsection
