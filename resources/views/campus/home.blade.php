<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ setting('campus_name', 'Campus') }}</title>
    @if(setting('campus_favicon_url'))
        <link rel="icon" href="{{ setting('campus_favicon_url') }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    {{-- Hero (només si campus actiu) ----------------------------------------}}
    @if(setting('campus_enabled', true))
    <div style="background-color:{{ setting('hero_color', '#3730a3') }};">
        <div class="max-w-5xl mx-auto px-6 py-16 text-center">
            <h1 class="text-4xl font-extrabold tracking-tight mb-3" style="color:{{ setting('hero_text_color', '#ffffff') }};">
                {{ setting('hero_title', setting('campus_name', 'Campus de Formació Continuada')) }}
            </h1>
            <p class="text-lg mb-8" style="color:{{ setting('hero_text_color', '#ffffff') }};opacity:0.85;">
                {{ setting('hero_subtitle', 'Formació professional i personal al teu ritme') }}
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('campus.catalog.index') }}"
                   class="font-semibold px-6 py-3 rounded-xl transition shadow"
                   style="background-color:#ffffff;color:#3730a3;">
                    Veure el catàleg de cursos
                </a>
                <a href="{{ route('campus.register') }}"
                   class="font-semibold px-6 py-3 rounded-xl transition"
                   style="border:2px solid #ffffff;color:#ffffff;background-color:transparent;">
                    Inscriure's
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Access cards --------------------------------------------------------}}
    <main class="flex-1 max-w-5xl mx-auto w-full px-6 py-14">

        <h2 class="text-center text-sm font-semibold uppercase tracking-widest text-gray-400 mb-8">Accés per perfil</h2>

        @php
            $campusActiu    = setting('campus_enabled', true);
            $associatsActiu = setting('associats_enabled', false);

            $cols = 1 + (int) $campusActiu * 2 + (int) $associatsActiu;
            $gridClass = match(true) {
                $cols >= 4  => 'grid grid-cols-1 sm:grid-cols-4 gap-6',
                $cols === 3 => 'grid grid-cols-1 sm:grid-cols-3 gap-6',
                $cols === 2 => 'grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-xl mx-auto',
                default     => 'grid grid-cols-1 gap-6 max-w-xs mx-auto',
            };
        @endphp

        <div class="{{ $gridClass }}">

            {{-- Alumnat (campus) --}}
            @if($campusActiu)
            <a href="{{ route('campus.login') }}"
               class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition p-8 text-center flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-indigo-100 flex items-center justify-center group-hover:bg-indigo-200 transition">
                    <svg class="w-7 h-7 text-indigo-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-base">Alumnat</p>
                    <p class="text-sm text-gray-500 mt-0.5">Portal d'inscripcions i cursos</p>
                </div>
                <span class="mt-auto text-xs text-indigo-600 font-medium group-hover:underline">Accedir &rarr;</span>
            </a>

            {{-- Professorat (campus) --}}
            <a href="{{ route('teacher.login') }}"
               class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition p-8 text-center flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-emerald-100 flex items-center justify-center group-hover:bg-emerald-200 transition">
                    <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 3.741-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-base">Professorat</p>
                    <p class="text-sm text-gray-500 mt-0.5">Cursos, alumnes i liquidacions</p>
                </div>
                <span class="mt-auto text-xs text-emerald-600 font-medium group-hover:underline">Accedir &rarr;</span>
            </a>
            @endif

            {{-- Socis (associats) --}}
            @if($associatsActiu)
            <a href="{{ route('member.login') }}"
               class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition p-8 text-center flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-violet-100 flex items-center justify-center group-hover:bg-violet-200 transition">
                    <svg class="w-7 h-7 text-violet-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Zm6-10.125a1.875 1.875 0 1 1-3.75 0 1.875 1.875 0 0 1 3.75 0Zm1.294 6.336a6.721 6.721 0 0 1-3.17.789 6.721 6.721 0 0 1-3.168-.789 3.376 3.376 0 0 1 6.338 0Z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-base">Socis</p>
                    <p class="text-sm text-gray-500 mt-0.5">{{ setting('associats_org_name', 'Entitat') }} · Passaport Cultural</p>
                </div>
                <span class="mt-auto text-xs text-violet-600 font-medium group-hover:underline">Accedir &rarr;</span>
            </a>
            @endif

            {{-- Gestió (sempre visible) --}}
            <a href="/admin"
               class="group bg-white rounded-2xl border border-gray-200 shadow-sm hover:shadow-md transition p-8 text-center flex flex-col items-center gap-3">
                <div class="w-14 h-14 rounded-full bg-amber-100 flex items-center justify-center group-hover:bg-amber-200 transition">
                    <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.559.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.894.149c-.424.07-.764.383-.929.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.398.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.272-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.108-1.204l-.526-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-gray-900 text-base">Gestió</p>
                    <p class="text-sm text-gray-500 mt-0.5">Administració del campus</p>
                </div>
                <span class="mt-auto text-xs text-amber-600 font-medium group-hover:underline">Accedir &rarr;</span>
            </a>

        </div>

    </main>

    @include('campus.partials.footer')

</body>
</html>
