@extends('campus.layouts.app')

@section('title', __('site.my_profile'))

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">{{ __('site.my_profile') }}</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4 text-sm">
        <h2 class="font-semibold text-gray-900 text-base">{{ __('site.personal_info') }}</h2>

        <div>
            <p class="text-gray-400 text-xs mb-0.5">{{ __('site.full_name') }}</p>
            <p class="font-medium">{{ $student->first_name }} {{ $student->last_name }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs mb-0.5">{{ __('site.email') }}</p>
            <p>{{ $student->email }}</p>
        </div>
    </div>

    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <form method="POST" action="{{ route('campus.portal.profile.update') }}" class="space-y-4">
            @csrf

            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ __('site.phone') }}</label>
                <input id="phone" type="text" name="phone" value="{{ old('phone', $student->phone) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('phone') border-red-400 @enderror">
                @error('phone')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            @include('partials.locale-select', ['user' => $student])

            <hr class="border-gray-100">

            <p class="text-sm font-medium text-gray-700">{{ __('site.change_password') }} <span class="font-normal text-gray-400">{{ __('site.optional') }}</span></p>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ __('site.new_password') }}</label>
                <input id="password" type="password" name="password" autocomplete="new-password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ __('site.confirm_password') }}</label>
                <input id="password_confirmation" type="password" name="password_confirmation" autocomplete="new-password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition text-sm">
                    {{ __('site.save') }}
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
