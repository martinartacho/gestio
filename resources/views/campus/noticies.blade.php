<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notícies · {{ setting('campus_name', 'Campus') }}</title>
    @if(setting('campus_favicon_url'))
        <link rel="icon" href="{{ setting('campus_favicon_url') }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    {{-- Capçalera --}}
    <header class="bg-white border-b border-gray-200">
        <div class="max-w-5xl mx-auto px-6 py-4 flex items-center justify-between">
            <a href="{{ route('home') }}" class="text-sm font-semibold text-gray-700 hover:text-indigo-600 transition">
                ← {{ setting('campus_name', 'Campus') }}
            </a>
        </div>
    </header>

    <main class="flex-1 max-w-3xl mx-auto w-full px-6 py-14">

        {{-- Títol --}}
        <div class="mb-10">
            <p class="text-xs font-semibold uppercase tracking-widest text-indigo-500 mb-2">Novetats</p>
            <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Notícies</h1>
            <p class="text-gray-500 text-sm">Tot el que millora a {{ setting('campus_name', 'la plataforma') }}, explicat sense tecnicismes.</p>
        </div>

        {{-- Filtre per categoria --}}
        @php
            $categoriaActiva = request('categoria', 'totes');
        @endphp
        <div class="flex flex-wrap gap-2 mb-10">
            @php
                $filtres = ['totes' => 'Totes'] + $categories;
                $colors  = [
                    'totes'      => 'bg-gray-900 text-white',
                    'campus'     => 'bg-indigo-600 text-white',
                    'associats'  => 'bg-amber-500 text-white',
                    'tresoreria' => 'bg-emerald-600 text-white',
                    'secretaria' => 'bg-rose-500 text-white',
                    'admin'      => 'bg-violet-600 text-white',
                    'sistema'    => 'bg-gray-500 text-white',
                ];
                $colorsInactiu = [
                    'totes'      => 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50',
                    'campus'     => 'bg-white text-indigo-600 border border-indigo-200 hover:bg-indigo-50',
                    'associats'  => 'bg-white text-amber-600 border border-amber-200 hover:bg-amber-50',
                    'tresoreria' => 'bg-white text-emerald-600 border border-emerald-200 hover:bg-emerald-50',
                    'secretaria' => 'bg-white text-rose-600 border border-rose-200 hover:bg-rose-50',
                    'admin'      => 'bg-white text-violet-600 border border-violet-200 hover:bg-violet-50',
                    'sistema'    => 'bg-white text-gray-600 border border-gray-300 hover:bg-gray-50',
                ];
            @endphp
            @foreach($filtres as $clau => $etiqueta)
            <a href="{{ route('campus.noticies', $clau !== 'totes' ? ['categoria' => $clau] : []) }}"
               class="text-xs font-semibold px-4 py-1.5 rounded-full transition
                      {{ $categoriaActiva === $clau ? $colors[$clau] : $colorsInactiu[$clau] }}">
                {{ $etiqueta }}
            </a>
            @endforeach
        </div>

        {{-- Llista de notícies --}}
        @php
            $noticiesFiltrades = $categoriaActiva === 'totes'
                ? $noticies
                : $noticies->filter(fn($n) => in_array($categoriaActiva, $n->labels ?? []));
        @endphp

        @if($noticiesFiltrades->isEmpty())
            <p class="text-gray-400 text-sm">No hi ha notícies en aquesta categoria.</p>
        @else
        <div class="flex flex-col gap-8">
            @foreach($noticiesFiltrades as $noticia)
            @php
                $badgeColors = [
                    'campus'     => 'bg-indigo-100 text-indigo-700',
                    'associats'  => 'bg-amber-100 text-amber-700',
                    'tresoreria' => 'bg-emerald-100 text-emerald-700',
                    'secretaria' => 'bg-rose-100 text-rose-700',
                    'admin'      => 'bg-violet-100 text-violet-700',
                    'sistema'    => 'bg-gray-100 text-gray-600',
                ];
                $labelNames = [
                    'campus'     => 'Campus',
                    'associats'  => 'Associats',
                    'tresoreria' => 'Tresoreria',
                    'secretaria' => 'Secretaria',
                    'admin'      => 'Administració',
                    'sistema'    => 'Sistema',
                ];
            @endphp
            <article class="bg-white rounded-2xl border border-gray-200 shadow-sm p-8">
                <div class="flex items-center gap-3 mb-4">
                    @foreach($noticia->labels ?? [] as $label)
                    <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full {{ $badgeColors[$label] ?? 'bg-gray-100 text-gray-600' }}">
                        {{ $labelNames[$label] ?? $label }}
                    </span>
                    @endforeach
                    @if($noticia->version)
                    <span class="text-xs text-gray-400 font-mono">{{ $noticia->version }}</span>
                    @endif
                    <span class="text-xs text-gray-400 ml-auto">
                        {{ $noticia->published_at->translatedFormat('d \d\e F \d\e Y') }}
                    </span>
                </div>

                <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $noticia->title }}</h2>

                @if($noticia->summary)
                <p class="text-gray-500 text-sm mb-4 leading-relaxed">{{ $noticia->summary }}</p>
                @endif

                <div class="prose prose-sm prose-indigo max-w-none text-gray-700">
                    {!! $noticia->body !!}
                </div>
            </article>
            @endforeach
        </div>
        @endif

    </main>

    @include('campus.partials.footer')

</body>
</html>
