@extends('associats.layouts.app')

@section('title', 'Perfil · ' . setting('associats_org_name', 'Entitat'))

@section('content')
<div style="max-width:540px; margin:0 auto;">

    <h1 style="font-size:1.5rem;font-weight:700;color:#111827;margin-bottom:1.5rem;">El meu perfil</h1>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;font-size:0.875rem;color:#374151;">

        @foreach([
            ['label' => 'Nom',       'value' => $member->first_name],
            ['label' => 'Cognoms',   'value' => $member->last_name],
            ['label' => 'Correu',    'value' => $member->email],
            ['label' => 'Telèfon',   'value' => $member->phone],
            ['label' => 'Adreça',    'value' => $member->address],
            ['label' => 'Codi postal','value' => $member->postal_code],
            ['label' => 'Ciutat',    'value' => $member->city],
        ] as $field)
        @if($field['value'])
        <div style="display:flex;justify-content:space-between;padding:0.625rem 0;border-bottom:1px solid #f3f4f6;">
            <span style="color:#6b7280;min-width:8rem;">{{ $field['label'] }}</span>
            <span>{{ $field['value'] }}</span>
        </div>
        @endif
        @endforeach

        <div style="display:flex;justify-content:space-between;padding:0.625rem 0;">
            <span style="color:#6b7280;">Número de soci</span>
            <span style="font-weight:600;">
                {{ setting('associats_member_prefix', '') }}{{ $member->member_number }}
            </span>
        </div>
    </div>

    <div style="margin-top:1rem;text-align:center;">
        <a href="{{ route('member.card') }}"
           style="font-size:0.875rem;color:#6366f1;text-decoration:none;">
            ← Tornar al carnet
        </a>
    </div>

</div>
@endsection
