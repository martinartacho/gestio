@extends('campus.layouts.app')

@section('title', 'El meu carret')

@section('content')
<div class="max-w-3xl mx-auto">

    <h1 class="text-2xl font-bold text-gray-900 mb-6">El meu carret</h1>

    @php $maxCartItems = (int) setting('payment_max_cart_items', 5); @endphp

    @if ($cart->items->isEmpty())
        {{-- Carret buit --}}
        <div class="text-center py-16 bg-white rounded-xl border border-gray-200">
            <svg class="mx-auto mb-4" width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:#d1d5db">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
            </svg>
            <p class="text-gray-500 mb-4">El carret és buit.</p>
            <a href="{{ route('campus.catalog.index') }}"
               class="inline-block bg-indigo-600 text-white px-5 py-2 rounded-lg font-medium hover:bg-indigo-700 transition">
                Veure cursos disponibles
            </a>
        </div>

    @else

        {{-- Avís de límit: només quan no hi ha cap flash actiu (evita doble missatge) --}}
        @if ($cart->items->count() >= $maxCartItems && ! session('success') && ! session('info'))
            <div class="mb-4 flex items-center gap-2 p-3 bg-indigo-50 border border-indigo-200 text-indigo-800 rounded-lg text-sm">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                </svg>
                Carret ple — ja pots passar per caixa. Si vols canviar algun curs, elimina'l primer.
            </div>
        @elseif ($cart->items->count() === $maxCartItems - 1 && ! session('success'))
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 text-amber-700 rounded-lg text-sm text-center">
                Et queda 1 lloc al carret (màxim {{ $maxCartItems }} cursos).
            </div>
        @endif

        {{-- Llista de cursos al carret --}}
        <div class="bg-white rounded-xl border border-gray-200 divide-y divide-gray-100 mb-6">
            @foreach ($cart->items as $item)
            <div class="flex items-center justify-between p-4 gap-4">
                <div class="flex-1 min-w-0">
                    <a href="{{ route('campus.catalog.show', $item->course->slug) }}"
                       class="font-semibold text-gray-900 hover:text-indigo-700 truncate block">
                        {{ $item->course->title }}
                    </a>
                    <div class="flex flex-wrap gap-2 mt-0.5 text-xs text-gray-400">
                        @if ($item->course->season)
                            <span>{{ $item->course->season->name }}</span>
                        @endif
                        @if ($item->course->format)
                            <span>· {{ \App\Models\CampusCourse::FORMATS[$item->course->format] ?? $item->course->format }}</span>
                        @endif
                        @if ($item->course->start_date)
                            <span>· {{ $item->course->start_date->format('d/m/Y') }}</span>
                        @endif
                    </div>
                </div>

                <div class="flex items-center gap-3 shrink-0">
                    <span class="font-semibold text-gray-900 tabular-nums">
                        {{ number_format($item->price, 2, ',', '.') }} €
                    </span>

                    <form method="POST" action="{{ route('campus.cart.remove', $item->id) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-1 px-2 py-1 text-xs text-red-500 border border-red-200 rounded hover:bg-red-50 transition"
                                title="Eliminar del carret">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                            </svg>
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Resum i checkout --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex justify-between items-center mb-5">
                <span class="text-gray-600">Total ({{ $cart->items->count() }} {{ Str::plural('curs', $cart->items->count()) }})</span>
                <span class="text-xl font-bold text-gray-900">{{ number_format($cart->total(), 2, ',', '.') }} €</span>
            </div>

            <form method="POST" action="{{ route('campus.cart.checkout') }}">
                @csrf

                @php
                    $payMethods = [];
                    if (config('services.stripe.secret') && $cart->total() > 0) {
                        $payMethods['stripe'] = 'Targeta (Stripe)';
                    }
                    if ($cart->total() > 0) {
                        if (setting('payment_transfer_enabled')) $payMethods['transfer'] = 'Transferència bancària';
                        if (setting('payment_bizum_enabled'))    $payMethods['bizum']    = 'Bizum';
                        if (setting('payment_cash_enabled'))     $payMethods['cash']     = 'Efectiu';
                        if (setting('payment_paypal_enabled'))   $payMethods['paypal']   = 'PayPal';
                    }
                @endphp

                @if (count($payMethods) > 1)
                    <p class="text-sm font-medium text-gray-700 mb-2">Mètode de pagament:</p>
                    <div class="flex flex-col gap-1.5 mb-5">
                        @foreach ($payMethods as $key => $label)
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-gray-700 hover:text-gray-900">
                                <input type="radio" name="payment_method" value="{{ $key }}"
                                       {{ $loop->first ? 'checked' : '' }}
                                       class="accent-indigo-600">
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                @elseif (count($payMethods) === 1)
                    <input type="hidden" name="payment_method" value="{{ array_key_first($payMethods) }}">
                @else
                    <input type="hidden" name="payment_method" value="free">
                @endif

                <button type="submit"
                        class="w-full bg-indigo-600 text-white py-3 rounded-lg font-semibold hover:bg-indigo-700 transition">
                    Passar per caixa →
                </button>
            </form>

            <p class="text-xs text-gray-400 text-center mt-3">
                El carret caduca {{ $cart->expires_at?->diffForHumans() ?? 'en breu' }}.
            </p>
        </div>

        <div class="mt-4 text-center">
            <a href="{{ route('campus.catalog.index') }}" class="text-sm text-indigo-600 hover:underline">
                ← Continuar afegint cursos
            </a>
        </div>
    @endif

</div>
@endsection
