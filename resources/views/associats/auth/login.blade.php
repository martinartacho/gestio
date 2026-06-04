@extends('associats.layouts.app')

@section('title', 'Accés socis · ' . setting('associats_org_name', 'Entitat'))

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        <div class="text-center mb-6">
            <p class="text-xs font-semibold tracking-widest uppercase text-indigo-600 mb-1">
                {{ setting('associats_org_name', 'Entitat') }}
            </p>
            <h1 class="text-2xl font-bold text-gray-900">Accés socis</h1>
            <p class="text-sm text-gray-500 mt-1">Portal del Passaport Cultural Digital</p>
        </div>

        <form method="POST" action="{{ route('member.login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Correu electrònic</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-400 @enderror">
                @error('email')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Contrasenya</label>
                    <a href="{{ route('member.password.request') }}"
                       class="text-xs text-indigo-600 hover:underline">Heu oblidat la contrasenya?</a>
                </div>
                <input type="password" name="password" required
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="rounded">
                <label for="remember" class="text-sm text-gray-600">Recorda'm</label>
            </div>
            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Accedir
            </button>
        </form>

    </div>
</div>
@endsection
