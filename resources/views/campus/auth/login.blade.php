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
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-gray-700">Contrasenya</label>
                    <a href="{{ route('campus.password.request') }}"
                       class="text-xs text-indigo-600 hover:underline">Heu oblidat la contrasenya?</a>
                </div>
                <div style="position:relative;">
                    <input type="password" name="password" id="pwd" required
                           style="padding-right:2.5rem;"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <button type="button" tabindex="-1"
                            onclick="var f=document.getElementById('pwd');f.type=f.type==='password'?'text':'password'"
                            style="position:absolute;top:0;right:0;bottom:0;padding:0 0.625rem;display:flex;align-items:center;background:none;border:none;cursor:pointer;color:#9ca3af;"
                            onmouseover="this.style.color='#4b5563'" onmouseout="this.style.color='#9ca3af'">
                        <svg xmlns="http://www.w3.org/2000/svg" style="width:1rem;height:1rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                    </button>
                </div>
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
