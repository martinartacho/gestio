<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Campus de Formació') — Campus</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('campus.catalog.index') }}" class="text-xl font-bold text-indigo-700 hover:text-indigo-900">
                Campus
            </a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('campus.catalog.index') }}" class="text-gray-600 hover:text-indigo-700">Cursos</a>
                @auth('student')
                    <a href="{{ route('campus.portal.courses') }}" class="text-gray-600 hover:text-indigo-700">Els meus cursos</a>
                    <form method="POST" action="{{ route('campus.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-red-600">Sortir</button>
                    </form>
                    <span class="text-indigo-700 font-medium">{{ auth('student')->user()->first_name }}</span>
                @else
                    <a href="{{ route('campus.login') }}" class="text-gray-600 hover:text-indigo-700">Accedir</a>
                    <a href="{{ route('campus.register') }}" class="bg-indigo-600 text-white px-3 py-1.5 rounded-md hover:bg-indigo-700">Registrar-se</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="flex-1 max-w-6xl mx-auto w-full px-4 py-8">
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded-lg text-sm">{{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="mb-4 p-3 bg-blue-100 text-blue-800 rounded-lg text-sm">{{ session('info') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 text-red-800 rounded-lg text-sm">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>

    <footer class="border-t border-gray-200 text-center text-xs text-gray-400 py-5">
        Campus de Formació Continuada
        &nbsp;·&nbsp;
        <a href="{{ route('campus.catalog.index') }}" class="hover:text-gray-600">Catàleg</a>
        &nbsp;·&nbsp;
        <a href="{{ route('campus.login') }}" class="hover:text-gray-600">Alumnat</a>
        &nbsp;·&nbsp;
        <a href="{{ route('teacher.login') }}" class="hover:text-gray-600">Professorat</a>
        &nbsp;·&nbsp;
        <a href="/admin" class="hover:text-gray-600">Administració</a>
    </footer>

    @if(app()->isLocal())
    @php
        $dbgSeasons  = \App\Models\CampusSeason::withCount(['courses',
            'courses as courses_active_count'  => fn($q) => $q->where('status','active'),
            'courses as courses_public_count'  => fn($q) => $q->where('is_public', true),
        ])->orderByDesc('start_date')->get();
        $dbgActive   = $dbgSeasons->firstWhere('status', 'active');
        $dbgEnroll   = \App\Models\CampusEnrollment::selectRaw('status, count(*) as total')
                            ->groupBy('status')->pluck('total','status');
        $dbgStudents = \App\Models\CampusStudent::count();
    @endphp
    <details class="mx-4 mb-4 border border-yellow-300 rounded-lg bg-yellow-50 text-xs text-left">
        <summary class="px-4 py-2 cursor-pointer font-mono font-semibold text-yellow-800 select-none">
            [DEBUG] Temporades · Cursos · Matrícules · Alumnes
        </summary>
        <div class="px-4 pb-4 pt-2 space-y-3 font-mono">

            <div>
                <p class="font-bold text-yellow-900 mb-1">Temporades ({{ $dbgSeasons->count() }})</p>
                <table class="w-full border-collapse text-yellow-800">
                    <thead><tr class="border-b border-yellow-200">
                        <th class="text-left pr-4 py-0.5">Nom</th>
                        <th class="text-center pr-4">Activa</th>
                        <th class="text-center pr-4">Total</th>
                        <th class="text-center pr-4">Actius</th>
                        <th class="text-center">Públics</th>
                    </tr></thead>
                    <tbody>
                    @foreach($dbgSeasons as $s)
                    <tr class="border-b border-yellow-100 {{ $s->status === 'active' ? 'bg-green-50 font-semibold' : '' }}">
                        <td class="pr-4 py-0.5">{{ $s->name }} {{ $s->status === 'active' ? '★' : '' }}</td>
                        <td class="text-center pr-4">{{ $s->status === 'active' ? 'SÍ' : 'no' }}</td>
                        <td class="text-center pr-4">{{ $s->courses_count }}</td>
                        <td class="text-center pr-4">{{ $s->courses_active_count }}</td>
                        <td class="text-center">{{ $s->courses_public_count }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div>
                <p class="font-bold text-yellow-900 mb-1">Matrícules per estat</p>
                <div class="flex gap-4 flex-wrap">
                    @foreach(['pending'=>'Pendent','paid'=>'Pagada','cancelled'=>'Cancel·lada','refunded'=>'Retornada'] as $k=>$label)
                    <span class="px-2 py-0.5 rounded border border-yellow-300
                        {{ $k==='paid' ? 'bg-green-100 text-green-800' : ($k==='pending' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-600') }}">
                        {{ $label }}: <strong>{{ $dbgEnroll[$k] ?? 0 }}</strong>
                    </span>
                    @endforeach
                </div>
            </div>

            <div class="text-yellow-800">
                Alumnes registrats: <strong>{{ $dbgStudents }}</strong>
                @if(auth('student')->check())
                &nbsp;·&nbsp; Sessió: <strong>{{ auth('student')->user()->email }}</strong>
                @else
                &nbsp;·&nbsp; Sessió: <em>anònim</em>
                @endif
            </div>

        </div>
    </details>
    @endif

</body>
</html>
