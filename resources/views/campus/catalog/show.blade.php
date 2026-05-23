@extends('campus.layouts.app')

@section('title', $course->title)

@section('content')
<div class="max-w-3xl mx-auto">

    @if ($isPreview)
    <div class="mb-4 flex items-center gap-3 rounded-lg border border-amber-300 bg-amber-50 px-4 py-3 text-sm text-amber-800">
        <svg class="h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.573-3.007-9.964-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        </svg>
        <div class="flex-1">
            <strong>Mode previsualització</strong>
            @if (! $course->is_public)
                — <span class="text-amber-700">Aquest curs no és públic</span>
            @endif
            @if ($course->status !== 'active')
                — Estat: <span class="font-semibold">{{ \App\Models\CampusCourse::STATUSES[$course->status] ?? $course->status }}</span>
            @endif
        </div>
        <a href="{{ route('campus.catalog.show', $course->slug) }}"
           class="shrink-0 underline hover:no-underline">Sortir del preview</a>
    </div>
    @endif

    <a href="{{ route('campus.catalog.index', array_filter(['preview' => $isPreview ? 1 : null])) }}"
       class="text-sm text-indigo-600 hover:underline mb-4 inline-block">← Tornar al catàleg</a>

    <div class="bg-white rounded-xl border shadow-sm p-8
                {{ $isPreview && (! $course->is_public || $course->status !== 'active') ? 'border-amber-200' : 'border-gray-200' }}">
        <div class="flex items-center gap-3 mb-2 flex-wrap">
            @if ($course->category)
                <span class="text-xs font-semibold uppercase tracking-wide px-2 py-0.5 rounded-full"
                      style="background-color: {{ $course->category->color ?? '#e5e7eb' }}22; color: {{ $course->category->color ?? '#6b7280' }}">
                    {{ $course->category->name }}
                </span>
            @endif
            <span class="text-xs text-gray-400">{{ $course->code }}</span>
            @if ($course->season)
                <span class="text-xs text-gray-400">· {{ $course->season->name }}</span>
            @endif
            @if ($course->open_enrollment)
                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">
                    ∞ Inscripció oberta
                </span>
            @endif
        </div>

        <h1 class="text-2xl font-bold text-gray-900 mb-4">{{ $course->title }}</h1>

        @if ($course->description)
            <p class="text-gray-600 mb-6">{{ $course->description }}</p>
        @endif

        <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 mb-6">
            @if ($course->start_date)
                <div><span class="font-medium text-gray-800">Inici:</span> {{ $course->start_date->format('d/m/Y') }}</div>
            @endif
            @if ($course->end_date)
                <div><span class="font-medium text-gray-800">Final:</span> {{ $course->end_date->format('d/m/Y') }}</div>
            @endif
            @if ($course->sessions)
                <div><span class="font-medium text-gray-800">Sessions:</span> {{ $course->sessions }}</div>
            @endif
            @if ($course->hours)
                <div><span class="font-medium text-gray-800">Hores:</span> {{ $course->hours }}h</div>
            @endif
            @if ($course->space)
                <div><span class="font-medium text-gray-800">Lloc:</span> {{ $course->space->name }}</div>
            @endif
            @if ($course->format)
                <div><span class="font-medium text-gray-800">Format:</span> {{ \App\Models\CampusCourse::FORMATS[$course->format] ?? $course->format }}</div>
            @endif
        </div>

        @if ($course->objectives)
            <div class="mb-6">
                <h2 class="font-semibold text-gray-800 mb-1">Objectius</h2>
                <p class="text-gray-600 text-sm">{{ $course->objectives }}</p>
            </div>
        @endif

        @if ($course->requirements)
            <div class="mb-6">
                <h2 class="font-semibold text-gray-800 mb-1">Requisits previs</h2>
                <p class="text-gray-600 text-sm">{{ $course->requirements }}</p>
            </div>
        @endif

        <div class="flex items-center justify-between border-t border-gray-100 pt-6 mt-4">
            <div class="text-2xl font-bold text-indigo-700">
                {{ $course->price ? number_format($course->price, 2, ',', '.') . ' €' : 'Gratuït' }}
            </div>

            @if ($isPreview)
                <span class="bg-amber-50 text-amber-700 border border-amber-200 text-sm font-medium px-4 py-2 rounded-lg">
                    Previsualització — inscripció desactivada
                </span>
            @elseif ($alreadyEnrolled)
                @php
                    $eStatus = $myEnrollment->status;
                    $eLabel  = \App\Models\CampusEnrollment::STATUSES[$eStatus] ?? $eStatus;
                    $eCss = match(\App\Models\CampusEnrollment::STATUS_COLORS[$eStatus] ?? 'gray') {
                        'warning' => 'bg-yellow-50 text-yellow-800 border border-yellow-200',
                        'success' => 'bg-green-100 text-green-800 border border-green-200',
                        'danger'  => 'bg-red-50 text-red-700 border border-red-200',
                        default   => 'bg-gray-100 text-gray-600 border border-gray-200',
                    };
                    $eIcon = match($eStatus) {
                        'paid', 'confirmed' => '✓',
                        'pending'           => '⏳',
                        'cancelled'         => '✕',
                        'refunded'          => '↩',
                        default             => '•',
                    };
                @endphp
                <div class="flex flex-col items-end gap-1.5">
                    <span class="{{ $eCss }} text-sm font-semibold px-4 py-2 rounded-lg">
                        {{ $eIcon }} {{ $eLabel }}
                    </span>
                    @if ($eStatus === 'pending' && $myEnrollment->payment_method && $myEnrollment->payment_method !== 'stripe')
                        <span class="text-xs text-gray-400">Esperant confirmació de pagament</span>
                    @elseif ($eStatus === 'paid' || $eStatus === 'confirmed')
                        <span class="text-xs text-green-600">Accés al curs garantit</span>
                    @endif
                </div>
            @elseif ($seasonIsPast)
                <span class="bg-gray-100 text-gray-500 text-sm font-medium px-4 py-2 rounded-lg">
                    Curs finalitzat
                </span>
            @elseif ($seasonIsFuture && !$enrollmentOpen)
                <span class="bg-blue-50 text-blue-700 text-sm font-medium px-4 py-2 rounded-lg">
                    @if ($course->season?->start_date_enrollment)
                        Inscripcions obertes a partir del {{ $course->season->start_date_enrollment->format('d/m/Y') }}
                    @else
                        Properament disponible
                    @endif
                </span>
            @elseif (!$enrollmentOpen)
                <span class="bg-orange-50 text-orange-700 text-sm font-medium px-4 py-2 rounded-lg">
                    Inscripcions tancades
                </span>
            @elseauth('student')
                @php
                    // Mètodes de pagament disponibles (ordre: primer Stripe, despres manuals)
                    $payMethods = [];
                    if (config('services.stripe.secret') && $course->price > 0) {
                        $payMethods['stripe'] = 'Targeta bancària';
                    }
                    if ($course->price > 0) {
                        if (setting('payment_transfer_enabled')) $payMethods['transfer'] = 'Transferència bancària';
                        if (setting('payment_bizum_enabled'))    $payMethods['bizum']    = 'Bizum';
                        if (setting('payment_cash_enabled'))     $payMethods['cash']     = 'Efectiu';
                        if (setting('payment_paypal_enabled'))   $payMethods['paypal']   = 'PayPal';
                    }
                @endphp

                <form method="POST" action="{{ route('campus.checkout.create', $course->slug) }}"
                      class="flex flex-col items-end gap-3">
                    @csrf

                    @if (count($payMethods) > 1)
                        <div class="text-right">
                            <p class="text-xs text-gray-500 mb-1.5 font-medium">Mètode de pagament:</p>
                            @foreach ($payMethods as $key => $label)
                                <label class="flex items-center justify-end gap-2 mb-1 cursor-pointer text-sm text-gray-700 hover:text-gray-900">
                                    {{ $label }}
                                    <input type="radio" name="payment_method" value="{{ $key }}"
                                           {{ $loop->first ? 'checked' : '' }}
                                           class="accent-indigo-600">
                                </label>
                            @endforeach
                        </div>
                    @elseif (count($payMethods) === 1)
                        <input type="hidden" name="payment_method" value="{{ array_key_first($payMethods) }}">
                    @else
                        {{-- Curs gratuït: no cal mètode --}}
                        <input type="hidden" name="payment_method" value="free">
                    @endif

                    <button type="submit"
                            class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                        Inscriure'm — {{ $course->price > 0 ? number_format($course->price, 2, ',', '.') . ' €' : 'Gratuït' }}
                    </button>
                </form>
            @else
                <a href="{{ route('campus.login') }}?redirect={{ urlencode(request()->url()) }}"
                   class="bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Identificar-se per inscriure's
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
