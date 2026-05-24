@extends('campus.layouts.app')

@section('title', 'Massa sol·licituds')

@section('content')
<div class="max-w-md mx-auto text-center py-12">
    <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-4">
        <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0
                     2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898
                     0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
        </svg>
    </div>
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Massa intents</h1>
    <p class="text-gray-500 mb-6">
        Heu fet massa sol·licituds en poc temps.<br>
        Espereu uns minuts i torneu-ho a provar.
    </p>
    <a href="{{ url()->previous(route('campus.catalog.index')) }}"
       class="inline-block text-sm text-indigo-600 hover:underline border border-indigo-200 px-4 py-2 rounded-lg">
        ← Tornar enrere
    </a>
</div>
@endsection
