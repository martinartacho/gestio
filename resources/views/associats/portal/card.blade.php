@extends('associats.layouts.app')

@section('title', 'Carnet digital · ' . setting('associats_org_name', 'Entitat'))

@section('content')
@php
    use chillerlan\QRCode\QRCode;
    use chillerlan\QRCode\QROptions;

    $qrUrl = route('member.card') . '?token=' . $member->qr_token;
    $options = new QROptions(['eccLevel' => QRCode::ECC_H]);
    $qrImage = (new QRCode($options))->render($qrUrl);
@endphp

<div style="max-width:420px; margin:0 auto;">

    {{-- Carnet --}}
    <div style="background:linear-gradient(135deg,#1e1b4b 0%,#3730a3 60%,#4f46e5 100%);border-radius:1rem;padding:2rem;color:#fff;box-shadow:0 8px 32px rgba(79,70,229,0.25);margin-bottom:1.5rem;">

        {{-- Capçalera --}}
        <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:1.5rem;">
            <div>
                <p style="font-size:0.6rem;letter-spacing:0.2em;text-transform:uppercase;color:#a5b4fc;margin:0 0 0.25rem;">
                    {{ setting('associats_org_name', 'Entitat') }}
                </p>
                <p style="font-size:0.75rem;color:#c7d2fe;margin:0;">Passaport Cultural Digital</p>
            </div>
            <div style="text-align:right;">
                <span style="font-size:0.65rem;letter-spacing:0.15em;text-transform:uppercase;padding:0.2rem 0.6rem;border:1px solid {{ $member->isActive() ? '#86efac' : '#fca5a5' }};border-radius:999px;color:{{ $member->isActive() ? '#86efac' : '#fca5a5' }};">
                    {{ $member->isActive() ? 'Actiu' : ucfirst($member->status) }}
                </span>
            </div>
        </div>

        {{-- Nom i número --}}
        <div style="margin-bottom:1.5rem;">
            <p style="font-size:1.5rem;font-weight:700;margin:0 0 0.25rem;letter-spacing:-0.01em;">
                {{ $member->full_name }}
            </p>
            <p style="font-size:0.875rem;color:#a5b4fc;margin:0;">
                Soci/a nº&nbsp;<strong style="color:#fff;font-size:1.1rem;">
                    {{ setting('associats_member_prefix', '') }}{{ $member->member_number }}
                </strong>
            </p>
        </div>

        {{-- QR --}}
        <div style="display:flex;justify-content:center;">
            <div style="background:#fff;border-radius:0.5rem;padding:0.5rem;width:148px;height:148px;">
                <img src="{{ $qrImage }}" alt="QR soci {{ $member->member_number }}"
                     style="width:100%;height:100%;display:block;">
            </div>
        </div>

        {{-- Peu --}}
        <p style="text-align:center;font-size:0.65rem;color:#818cf8;margin:1rem 0 0;">
            @if($member->joined_at)
                Soci des de {{ $member->joined_at->translatedFormat('F Y') }}
            @endif
        </p>

    </div>

    {{-- Info addicional --}}
    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.25rem;font-size:0.875rem;color:#374151;">
        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #f3f4f6;">
            <span style="color:#6b7280;">Correu</span>
            <span>{{ $member->email }}</span>
        </div>
        @if($member->phone)
        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;border-bottom:1px solid #f3f4f6;">
            <span style="color:#6b7280;">Telèfon</span>
            <span>{{ $member->phone }}</span>
        </div>
        @endif
        @if($member->city)
        <div style="display:flex;justify-content:space-between;padding:0.5rem 0;">
            <span style="color:#6b7280;">Ciutat</span>
            <span>{{ $member->city }}</span>
        </div>
        @endif
    </div>

    <div style="text-align:center;margin-top:1rem;">
        <a href="{{ route('member.profile') }}"
           style="font-size:0.875rem;color:#6366f1;text-decoration:none;">
            Editar perfil →
        </a>
    </div>

</div>
@endsection
