@extends('campus.layouts.app')

@section('title', 'Tria la institució')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-full mb-3">
                <svg style="width:1.75rem;height:1.75rem;color:#4f46e5;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900">Tens accés a més d'una institució</h1>
            <p class="text-gray-500 text-sm mt-1">
                Aquest compte no pertany a la institució que estaves visitant. Tria amb quina vols entrar:
            </p>
        </div>

        <div class="space-y-2">
            @foreach ($tenants as $tenant)
                <a href="{{ route('campus.login.complete', ['tenant' => $tenant->slug]) }}"
                   class="block w-full text-center border border-gray-300 rounded-lg px-4 py-2.5 text-sm font-medium
                          text-gray-700 hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700 transition">
                    {{ $tenant->name }}
                </a>
            @endforeach
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('campus.login') }}" class="text-sm text-indigo-600 hover:underline">
                ← Tornar a l'inici de sessió
            </a>
        </div>
    </div>
</div>
@endsection
