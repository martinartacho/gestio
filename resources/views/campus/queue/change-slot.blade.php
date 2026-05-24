@extends('campus.layouts.app')

@section('title', 'Canviar hora del torn')

@section('content')
<div class="max-w-md mx-auto">
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-8">

        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-indigo-100 rounded-full mb-3">
                <svg style="width:1.75rem;height:1.75rem;color:#4f46e5;" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mb-1">Canviar l'hora del torn</h1>
            <p class="text-gray-500 text-sm">Torn <strong>#{{ $entry->queue_number }}</strong> — {{ $entry->email }}</p>
        </div>

        <div class="bg-indigo-50 border border-indigo-200 rounded-lg px-4 py-3 text-sm text-indigo-800 mb-5">
            Hora actual: <strong>{{ $entry->slotTimeLabel() }}</strong>
        </div>

        <form method="POST" action="{{ route('campus.queue.update-slot') }}" class="space-y-4">
            @csrf
            <input type="hidden" name="email" value="{{ $entry->email }}">
            <input type="hidden" name="queue_number" value="{{ $entry->queue_number }}">

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Trieu una nova hora:</label>
                <div class="space-y-2">
                    @foreach ($slots as $index => $slotTime)
                    <label class="flex items-center gap-3 border border-gray-200 rounded-lg px-4 py-2.5 cursor-pointer hover:bg-gray-50
                                  {{ $index == $entry->currentSlotIndex ? 'border-indigo-300 bg-indigo-50' : '' }}">
                        <input type="radio" name="slot_index" value="{{ $index }}"
                               {{ $index == $entry->currentSlotIndex ? 'checked' : '' }}
                               class="accent-indigo-600">
                        <div>
                            <span class="font-medium text-gray-800 text-sm">
                                @if ($slotTime->isToday())
                                    Avui,
                                @elseif ($slotTime->isTomorrow())
                                    Demà,
                                @else
                                    {{ $slotTime->translatedFormat('l d/m') }},
                                @endif
                                a les <strong>{{ $slotTime->format('H:i') }} h</strong>
                            </span>
                            @if ($index == $entry->currentSlotIndex)
                                <span class="ml-2 text-xs text-indigo-600 font-medium">(actual)</span>
                            @endif
                        </div>
                    </label>
                    @endforeach
                </div>
            </div>

            @error('slot_index')
                <p class="text-red-600 text-xs">{{ $message }}</p>
            @enderror

            <button type="submit"
                    class="w-full bg-indigo-600 text-white py-2.5 rounded-lg font-semibold hover:bg-indigo-700 transition">
                Confirmar nova hora
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="{{ route('campus.queue.status', ['email' => $entry->email]) }}"
               class="text-sm text-gray-500 hover:text-indigo-600 hover:underline">
                ← Tornar a l'estat del torn
            </a>
        </div>
    </div>
</div>
@endsection
