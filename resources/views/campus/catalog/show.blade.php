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
            @if (! $course->hasUnlimitedPlaces())
                @php $slots = $course->availableSlots(); @endphp
                <div>
                    <span class="font-medium text-gray-800">Places:</span>
                    @if ($slots <= 0)
                        <span class="text-red-600 font-semibold">Complet</span>
                    @elseif ($slots <= 3)
                        <span class="text-orange-600 font-semibold">{{ $slots }} {{ $slots === 1 ? 'lloc disponible' : 'llocs disponibles' }}</span>
                    @else
                        {{ $slots }} disponibles / {{ $course->max_students }}
                    @endif
                </div>
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

        <div class="border-t border-gray-100 pt-6 mt-4">

            {{-- Preu --}}
            <div class="flex items-baseline justify-between mb-4">
                <span class="text-sm text-gray-500">Preu del curs</span>
                <span class="text-2xl font-bold text-indigo-700">
                    {{ $course->price > 0 ? number_format($course->price, 2, ',', '.') . ' €' : 'Gratuït' }}
                </span>
            </div>

            {{-- Accions --}}
            @if (! $alreadyEnrolled && $course->isFull())
                <div class="text-center">
                    <span class="inline-block bg-red-50 text-red-700 border border-red-200 text-sm font-semibold px-4 py-2 rounded-lg">
                        Curs complet
                    </span>
                    <p class="text-xs text-gray-400 mt-1">No hi ha places disponibles</p>
                </div>
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
                <div class="flex flex-col gap-1.5">
                    <span class="{{ $eCss }} block text-center text-sm font-semibold px-4 py-2 rounded-lg">
                        {{ $eIcon }} {{ $eLabel }}
                    </span>
                    @if ($eStatus === 'pending')
                        @if ($myEnrollment->payment_method && $myEnrollment->payment_method !== 'stripe')
                            <p class="text-xs text-center text-gray-400">Esperant confirmació de pagament</p>
                        @endif
                        <form method="POST"
                              action="{{ route('campus.checkout.cancel-enrollment', $course->slug) }}"
                              onsubmit="return confirm('Segur que vols cancel·lar la inscripció? Podràs tornar a inscriure\'t amb un altre mètode.')">
                            @csrf
                            <button type="submit" class="w-full text-xs text-gray-400 hover:text-red-600 underline transition">
                                Cancel·lar inscripció
                            </button>
                        </form>
                    @elseif ($eStatus === 'paid' || $eStatus === 'confirmed')
                        <p class="text-xs text-center text-green-600">Accés al curs garantit</p>
                    @endif
                </div>
            @elseif ($seasonIsPast)
                <span class="block text-center bg-gray-100 text-gray-500 text-sm font-medium px-4 py-2 rounded-lg">
                    Curs finalitzat
                </span>
            @elseif ($seasonIsFuture && !$enrollmentOpen && !$isPreview)
                <span class="block text-center bg-blue-50 text-blue-700 text-sm font-medium px-4 py-2 rounded-lg">
                    @if ($course->season?->start_date_enrollment)
                        Inscripcions obertes a partir del {{ $course->season->start_date_enrollment->format('d/m/Y') }}
                    @else
                        Properament disponible
                    @endif
                </span>
            @elseif (!$enrollmentOpen && !$isPreview)
                <span class="block text-center bg-orange-50 text-orange-700 text-sm font-medium px-4 py-2 rounded-lg">
                    Inscripcions tancades
                </span>
            @elseauth('student')
                <div class="flex flex-col gap-2">
                    @if ($course->price > 0)
                        @if ($inCart)
                            <a href="{{ route('campus.cart.show') }}"
                               class="w-full flex items-center justify-center gap-2 bg-indigo-50 border border-indigo-300 text-indigo-700 px-6 py-2.5 rounded-lg font-semibold hover:bg-indigo-100 transition">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5"
                                     viewBox="0 0 24 24" style="flex-shrink:0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                                </svg>
                                Al carret — veure el carret →
                            </a>
                        @else
                            <form method="POST" action="{{ route('campus.cart.add') }}">
                                @csrf
                                <input type="hidden" name="slug" value="{{ $course->slug }}">
                                <button type="submit"
                                        class="w-full bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition flex items-center justify-center gap-2">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5"
                                         viewBox="0 0 24 24" style="flex-shrink:0">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                                    </svg>
                                    Afegir al carret — {{ number_format($course->price, 2, ',', '.') }} €
                                </button>
                            </form>
                        @endif
                    @else
                        <form method="POST" action="{{ route('campus.checkout.create', $course->slug) }}">
                            @csrf
                            <input type="hidden" name="payment_method" value="free">
                            <button type="submit"
                                    class="w-full bg-emerald-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-emerald-700 transition text-center">
                                Inscriure'm — Gratuït
                            </button>
                        </form>
                    @endif
                </div>
            @else
                <a href="{{ route('campus.login') }}?redirect={{ urlencode(request()->url()) }}"
                   class="block text-center bg-indigo-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Identificar-se per inscriure's
                </a>
            @endauth
        </div>
    </div>
</div>
@endsection
