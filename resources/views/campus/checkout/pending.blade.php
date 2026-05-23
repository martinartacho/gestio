@extends('campus.layouts.app')

@section('title', 'Sol·licitud d\'inscripció enviada')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Capçalera --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-16 h-16 bg-green-100 rounded-full mb-4">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-2xl font-bold text-gray-900">Sol·licitud enviada!</h1>
        <p class="text-gray-500 mt-1">Inscripció a <strong>{{ $course->title }}</strong></p>
    </div>

    {{-- Referència de pagament --}}
    @if ($enrollment->payment_reference)
    <div class="flex items-center justify-between bg-indigo-50 border border-indigo-200 rounded-xl px-5 py-4 mb-6">
        <div>
            <p class="text-xs font-semibold text-indigo-500 uppercase tracking-wide mb-0.5">Referència de pagament</p>
            <p class="font-mono text-2xl font-bold text-indigo-800 tracking-widest select-all">
                {{ $enrollment->payment_reference }}
            </p>
        </div>
        @if ($enrollment->payment_expires_at)
        <div class="text-right">
            <p class="text-xs text-indigo-400 mb-0.5">Vàlid fins a</p>
            <p class="text-sm font-semibold text-indigo-700">
                {{ $enrollment->payment_expires_at->format('d/m/Y') }}
            </p>
            <p class="text-xs text-indigo-400">{{ $enrollment->payment_expires_at->format('H:i') }} h</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Targeta d'instruccions --}}
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm p-6 mb-6">

        @php
            $concept = str_replace(
                ['{NOM}', '{CURS}', '{REFERENCIA}'],
                [$student->full_name, $course->title, $enrollment->payment_reference ?? '—'],
                $settings->get('payment_concept_template', '{NOM} - {CURS}')
            );
        @endphp

        @if ($method === 'transfer')
            <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl">🏦</span>
                <h2 class="text-lg font-semibold text-gray-800">Transferència bancària</h2>
            </div>
            <div class="space-y-3 text-sm">
                @if ($settings->get('payment_iban'))
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">IBAN</span>
                        <span class="font-mono font-semibold text-gray-900 select-all">{{ $settings->get('payment_iban') }}</span>
                    </div>
                @endif
                @if ($settings->get('payment_bank_holder'))
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Titular</span>
                        <span class="text-gray-900">{{ $settings->get('payment_bank_holder') }}</span>
                    </div>
                @endif
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500 font-medium">Concepte</span>
                    <span class="text-gray-900 font-medium select-all">{{ $concept }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-500 font-medium">Import</span>
                    <span class="text-indigo-700 font-bold text-base">{{ number_format($course->price, 2, ',', '.') }} €</span>
                </div>
            </div>

        @elseif ($method === 'bizum')
            <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl">📱</span>
                <h2 class="text-lg font-semibold text-gray-800">Bizum</h2>
            </div>
            <div class="space-y-3 text-sm">
                @if ($settings->get('payment_bizum_number'))
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Número Bizum</span>
                        <span class="font-mono font-semibold text-gray-900 text-lg select-all">{{ $settings->get('payment_bizum_number') }}</span>
                    </div>
                @endif
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500 font-medium">Concepte</span>
                    <span class="text-gray-900 font-medium select-all">{{ $concept }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-500 font-medium">Import</span>
                    <span class="text-indigo-700 font-bold text-base">{{ number_format($course->price, 2, ',', '.') }} €</span>
                </div>
            </div>

        @elseif ($method === 'cash')
            <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl">🏢</span>
                <h2 class="text-lg font-semibold text-gray-800">Pagament en efectiu</h2>
            </div>
            <p class="text-gray-600 text-sm mb-4">
                Paga en efectiu a la secretaria del Campus presentant aquesta confirmació (impresa o al mòbil).
            </p>
            <div class="flex justify-between py-2 border-t border-gray-100">
                <span class="text-gray-500 font-medium text-sm">Import</span>
                <span class="text-indigo-700 font-bold text-base">{{ number_format($course->price, 2, ',', '.') }} €</span>
            </div>

        @elseif ($method === 'paypal')
            <div class="flex items-center gap-3 mb-4">
                <span class="text-2xl">🔗</span>
                <h2 class="text-lg font-semibold text-gray-800">PayPal</h2>
            </div>
            <div class="space-y-3 text-sm">
                @if ($settings->get('payment_paypal_email'))
                    <div class="flex justify-between py-2 border-b border-gray-100">
                        <span class="text-gray-500 font-medium">Envia a</span>
                        <span class="text-gray-900 font-medium">{{ $settings->get('payment_paypal_email') }}</span>
                    </div>
                @endif
                <div class="flex justify-between py-2 border-b border-gray-100">
                    <span class="text-gray-500 font-medium">Concepte</span>
                    <span class="text-gray-900 font-medium select-all">{{ $concept }}</span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-gray-500 font-medium">Import</span>
                    <span class="text-indigo-700 font-bold text-base">{{ number_format($course->price, 2, ',', '.') }} €</span>
                </div>
            </div>
        @endif
    </div>

    {{-- Nota informativa --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 text-sm text-blue-700 mb-6">
        ℹ️ L'equip us confirmarà la plaça per correu electrònic un cop rebut el pagament.
        @if ($enrollment->payment_expires_at)
            @php
                $exp = $enrollment->payment_expires_at;
                $expText = $exp->isToday()
                    ? 'avui a les ' . $exp->format('H:i') . ' h'
                    : ($exp->isTomorrow()
                        ? 'demà a les ' . $exp->format('H:i') . ' h'
                        : $exp->format('d/m/Y') . ' a les ' . $exp->format('H:i') . ' h');
            @endphp
            La vostra plaça quedarà reservada fins al <strong>{{ $expText }}</strong>.
        @endif
    </div>

    {{-- Accions --}}
    <div class="flex gap-3 justify-center">
        <a href="{{ route('campus.catalog.index') }}"
           class="text-sm text-gray-500 hover:text-gray-700 border border-gray-200 px-4 py-2 rounded-lg transition">
            Tornar al catàleg
        </a>
        <a href="{{ route('campus.portal.courses') }}"
           class="text-sm text-indigo-600 hover:text-indigo-800 border border-indigo-200 px-4 py-2 rounded-lg transition">
            Els meus cursos
        </a>
    </div>

</div>
@endsection
