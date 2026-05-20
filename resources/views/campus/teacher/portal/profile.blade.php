@extends('campus.teacher.layouts.app')

@section('title', 'El meu perfil')

@section('content')
<h1 class="text-2xl font-bold text-gray-900 mb-6">El meu perfil</h1>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Info bàsica (lectura) --}}
    <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 space-y-4 text-sm">
        <h2 class="font-semibold text-gray-900 text-base">Dades personals</h2>

        <div>
            <p class="text-gray-400 text-xs mb-0.5">Nom complet</p>
            <p class="font-medium">{{ $teacher->full_name }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs mb-0.5">Correu electrònic</p>
            <p>{{ $teacher->email }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs mb-0.5">Especialitat</p>
            <p>{{ $teacher->specialization ?? '—' }}</p>
        </div>
        <div>
            <p class="text-gray-400 text-xs mb-0.5">Estat</p>
            <p>{{ \App\Models\CampusTeacher::STATUSES[$teacher->status] ?? $teacher->status }}</p>
        </div>
    </div>

    {{-- Formulari editable --}}
    <div class="lg:col-span-2 bg-white rounded-xl border border-gray-200 shadow-sm p-6">
        <h2 class="font-semibold text-gray-900 text-base mb-4">Editar perfil</h2>

        <form method="POST" action="{{ route('teacher.portal.profile.update') }}" class="space-y-4">
            @csrf

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Telèfon</label>
                <input type="text" name="phone" value="{{ old('phone', $teacher->phone) }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('phone') border-red-400 @enderror">
                @error('phone')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Presentació / Biografia</label>
                <textarea name="bio" rows="4"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('bio') border-red-400 @enderror">{{ old('bio', $teacher->bio) }}</textarea>
                @error('bio')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <hr class="border-gray-100">

            <p class="text-sm font-medium text-gray-700">Canviar contrasenya <span class="font-normal text-gray-400">(opcional)</span></p>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nova contrasenya</label>
                <input type="password" name="password"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-400 @enderror">
                @error('password')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Confirmar contrasenya</label>
                <input type="password" name="password_confirmation"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div class="pt-2">
                <button type="submit"
                        class="bg-indigo-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-indigo-700 transition text-sm">
                    Desar canvis
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
