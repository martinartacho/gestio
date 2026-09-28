@extends('associats.layouts.app')

@section('title', __('Perfil') . ' · ' . setting('associats_org_name', __('Entitat')))

@section('content')
<div style="max-width:540px; margin:0 auto;">

    <h1 style="font-size:1.5rem;font-weight:700;color:#111827;margin-bottom:1.5rem;">{{ __('El meu perfil') }}</h1>

    <div style="background:#fff;border:1px solid #e5e7eb;border-radius:0.75rem;padding:1.5rem;font-size:0.875rem;color:#374151;">

        @foreach([
            ['label' => __('Nom'),       'value' => $member->first_name],
            ['label' => __('Cognoms'),   'value' => $member->last_name],
            ['label' => __('Correu'),    'value' => $member->email],
            ['label' => __('Telèfon'),   'value' => $member->phone],
            ['label' => __('Adreça'),    'value' => $member->address],
            ['label' => __('Codi postal'),'value' => $member->postal_code],
            ['label' => __('Ciutat'),    'value' => $member->city],
        ] as $field)
        @if($field['value'])
        <div style="display:flex;justify-content:space-between;padding:0.625rem 0;border-bottom:1px solid #f3f4f6;">
            <span style="color:#6b7280;min-width:8rem;">{{ $field['label'] }}</span>
            <span>{{ $field['value'] }}</span>
        </div>
        @endif
        @endforeach

        <div style="display:flex;justify-content:space-between;padding:0.625rem 0;">
            <span style="color:#6b7280;">{{ __('Número de soci') }}</span>
            <span style="font-weight:600;">
                {{ setting('associats_member_prefix', '') }}{{ $member->member_number }}
            </span>
        </div>
    </div>

    <form method="POST" action="{{ route('member.profile.locale') }}"
          class="mt-4 bg-white border border-gray-200 rounded-xl p-6 space-y-4">
        @csrf
        @include('partials.locale-select', ['user' => $member])
        <button type="submit"
                class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition text-sm">
            {{ __('site.save') }}
        </button>
    </form>

    <div style="margin-top:1rem;text-align:center;">
        <a href="{{ route('member.card') }}"
           style="font-size:0.875rem;color:#6366f1;text-decoration:none;">
            ← {{ __('Tornar al carnet') }}
        </a>
    </div>

</div>
@endsection
