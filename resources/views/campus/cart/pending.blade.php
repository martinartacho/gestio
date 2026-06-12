@extends('campus.layouts.app')

@section('title', 'Sol·licituds enviades — pendent de pagament')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900 mb-1">
            Sol·licituds enviades — <span class="text-amber-600">pendent de pagament</span>
        </h1>
        <p class="text-gray-500 text-sm">Has sol·licitat inscripció a {{ $enrollments->count() }} {{ Str::plural('curs', $enrollments->count()) }}.</p>
    </div>

    {{-- Cursos inscrits --}}
    <div class="bg-white border border-gray-200 rounded-xl divide-y divide-gray-100 mb-6">
        @foreach ($enrollments as $enrollment)
        <div class="flex justify-between items-center px-4 py-3">
            <span class="text-sm font-medium text-gray-800">{{ $enrollment->course->title }}</span>
            <span class="text-sm font-semibold text-gray-700">{{ number_format($enrollment->amount, 2, ',', '.') }} €</span>
        </div>
        @endforeach
        <div class="flex justify-between items-center px-4 py-3 bg-gray-50 rounded-b-xl">
            <span class="text-sm font-bold text-gray-900">Total</span>
            <span class="text-sm font-bold text-indigo-700">{{ number_format($enrollments->sum('amount'), 2, ',', '.') }} €</span>
        </div>
    </div>

    {{-- Instruccions de pagament --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-5 mb-6">
        <p class="text-sm font-semibold text-amber-900 mb-3">Referència de pagament única:</p>
        <p class="font-mono text-lg font-bold text-amber-700 tracking-widest mb-4">{{ $reference }}</p>

        @if ($method === 'transfer')
            <div class="space-y-1 text-sm text-amber-800">
                <p><strong>IBAN:</strong> {{ setting('payment_iban') }}</p>
                <p><strong>Titular:</strong> {{ setting('payment_bank_holder') }}</p>
                <p><strong>Concepte:</strong> {{ $reference }} – {{ $student->first_name }} {{ $student->last_name }}</p>
                <p><strong>Import:</strong> {{ number_format($enrollments->sum('amount'), 2, ',', '.') }} €</p>
            </div>
        @elseif ($method === 'bizum')
            <p class="text-sm text-amber-800">
                Envia <strong>{{ number_format($enrollments->sum('amount'), 2, ',', '.') }} €</strong>
                al número <strong>{{ setting('payment_bizum_number') }}</strong>
                amb la referència <strong>{{ $reference }}</strong>.
            </p>
        @elseif ($method === 'cash')
            <p class="text-sm text-amber-800">
                Porta <strong>{{ number_format($enrollments->sum('amount'), 2, ',', '.') }} €</strong>
                en efectiu a l'administració i indica la referència <strong>{{ $reference }}</strong>.
            </p>
        @elseif ($method === 'paypal')
            <p class="text-sm text-amber-800">
                Envia <strong>{{ number_format($enrollments->sum('amount'), 2, ',', '.') }} €</strong>
                a <strong>{{ setting('payment_paypal_email') }}</strong>
                indicant la referència <strong>{{ $reference }}</strong>.
            </p>
        @endif

        @if ($enrollments->first()?->payment_expires_at)
            <p class="text-xs text-amber-700 mt-3">
                ⏰ Tens fins al <strong>{{ $enrollments->first()->payment_expires_at->format('d/m/Y H:i') }}</strong> per efectuar el pagament.
            </p>
        @endif
    </div>

    <div class="text-center">
        <a href="{{ route('campus.portal.courses') }}"
           class="inline-block bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
            Veure les meves inscripcions
        </a>
    </div>

</div>
@endsection
