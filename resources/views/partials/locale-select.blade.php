{{-- Selector d'idioma per als formularis de perfil. Buit = idioma per defecte de l'entitat. --}}
<div>
    <label for="locale" class="block text-sm font-medium text-gray-700 mb-1">{{ __('site.language') }}</label>
    <select id="locale" name="locale"
            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('locale') border-red-400 @enderror">
        <option value="">{{ __('site.language_tenant_default') }}</option>
        @foreach (\App\Support\Locales::SUPPORTED as $code => $label)
            <option value="{{ $code }}" @selected(old('locale', $user->locale) === $code)>{{ $label }}</option>
        @endforeach
    </select>
    @error('locale')
        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
    @enderror
</div>
