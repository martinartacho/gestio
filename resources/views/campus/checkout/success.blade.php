@extends('campus.layouts.app')

@section('title', 'Inscripció confirmada')

@section('content')
<div class="max-w-md mx-auto text-center py-16">
    <div class="text-5xl mb-4">✓</div>
    <h1 class="text-2xl font-bold text-gray-900 mb-2">Inscripció completada</h1>
    <p class="text-gray-500 mb-6">El pagament s'ha processat correctament. En breu rebràs la confirmació per correu.</p>
    <a href="{{ route('campus.portal.courses') }}"
       class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
        Veure els meus cursos
    </a>
</div>
@endsection
