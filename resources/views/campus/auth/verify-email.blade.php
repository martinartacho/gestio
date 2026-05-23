@extends('campus.layouts.app')

@section('title', 'Verifica el teu correu')

@section('content')
<div class="max-w-md mx-auto text-center">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        <div class="inline-flex items-center justify-center w-16 h-16 bg-indigo-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                      d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5
                         0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0
                         0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0
                         1-1.07-1.916V6.75"/>
            </svg>
        </div>

        <h1 class="text-xl font-bold text-gray-900 mb-2">Verifica el teu correu</h1>
        <p class="text-gray-500 text-sm mb-6">
            Hem enviat un correu a <strong>{{ auth('student')->user()->email }}</strong>
            amb un enllaç de verificació. Feu clic a l'enllaç per activar el compte.
        </p>

        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 text-blue-700 text-sm rounded-lg px-4 py-3 mb-4">
                {{ session('info') }}
            </div>
        @endif

        <form method="POST" action="{{ route('campus.verification.resend') }}">
            @csrf
            <button type="submit"
                    class="text-sm text-indigo-600 hover:underline">
                No heu rebut el correu? Reenviar verificació
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-gray-100">
            <form method="POST" action="{{ route('campus.logout') }}">
                @csrf
                <button type="submit" class="text-xs text-gray-400 hover:text-gray-600">
                    Tancar sessió
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
