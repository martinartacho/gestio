@php
    $steps = [
        1 => 'Bàsic',
        2 => 'Sessions',
        3 => 'Revisió',
    ];
@endphp

<div style="display:flex;align-items:center;gap:0;margin-bottom:2rem;max-width:42rem;margin-left:auto;margin-right:auto;margin-bottom:1.5rem;">
    @foreach ($steps as $num => $label)
        @php
            $done   = $num < $current;
            $active = $num === $current;
        @endphp

        {{-- Pas --}}
        <div style="display:flex;flex-direction:column;align-items:center;gap:0.25rem;flex:1;position:relative;">
            <div style="
                width:2rem;height:2rem;border-radius:999px;display:flex;align-items:center;justify-content:center;
                font-size:0.875rem;font-weight:700;
                {{ $done   ? 'background:#4f46e5;color:#fff;' : '' }}
                {{ $active ? 'background:#4f46e5;color:#fff;box-shadow:0 0 0 3px #c7d2fe;' : '' }}
                {{ !$done && !$active ? 'background:#f3f4f6;color:#9ca3af;' : '' }}
            ">
                @if ($done) ✓ @else {{ $num }} @endif
            </div>
            <span style="font-size:0.75rem;font-weight:{{ $active ? '700' : '400' }};color:{{ $active ? '#4f46e5' : ($done ? '#374151' : '#9ca3af') }};">
                {{ $label }}
            </span>
        </div>

        {{-- Connector (excepte l'últim) --}}
        @if (!$loop->last)
            <div style="flex:1;height:2px;background:{{ $done ? '#4f46e5' : '#e5e7eb' }};margin-bottom:1.25rem;"></div>
        @endif
    @endforeach
</div>
