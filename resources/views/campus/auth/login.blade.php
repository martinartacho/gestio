@extends('campus.layouts.app')

@section('title', 'Accés alumnes')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">Accés alumnes</h1>

        <form method="POST" action="{{ route('campus.login.post') }}" class="space-y-4">
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
                <label class="block text-sm font-medium text-gray-700 mb-1">Contrasenya</label>
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

        <p class="text-center text-sm text-gray-500 mt-4">
            Nou al campus?
            <a href="{{ route('campus.register') }}" class="text-indigo-600 hover:underline">Registrar-se</a>
        </p>
    </div>
</div>
@endsection
