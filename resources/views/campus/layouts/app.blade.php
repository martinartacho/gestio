<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', setting('campus_name', 'Campus de Formació')) — {{ setting('campus_name', 'Campus') }}</title>
    @if(setting('campus_favicon_url'))
        <link rel="icon" href="{{ setting('campus_favicon_url') }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ url('/') }}" class="text-xl font-bold text-indigo-700 hover:text-indigo-900">
                {{ setting('campus_name', 'Campus') }}
            </a>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ route('campus.catalog.index') }}" class="text-gray-600 hover:text-indigo-700">Catàleg</a>
                @auth('student')
                    @php
                        $cartItemCount = \App\Models\CampusCart::where('student_id', auth('student')->id())
                            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
                            ->withCount('items')
                            ->latest()->first()?->items_count ?? 0;
                    @endphp
                    <a href="{{ route('campus.cart.show') }}"
                       class="relative flex items-center text-gray-600 hover:text-indigo-700" title="Carret">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>
                        </svg>
                        @if ($cartItemCount > 0)
                            <span class="absolute -top-1.5 -right-1.5 bg-indigo-600 text-white text-[10px] font-bold w-4 h-4 rounded-full flex items-center justify-center">
                                {{ $cartItemCount }}
                            </span>
                        @endif
                    </a>
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

    {{-- Banner verificació email --}}
    @auth('student')
        @if (! auth('student')->user()->hasVerifiedEmail() && ! request()->routeIs('campus.verification.*'))
        <div style="background:#fffbeb;border-bottom:1px solid #fde68a;padding:0.625rem 1rem;">
            <div style="max-width:72rem;margin:0 auto;display:flex;flex-wrap:wrap;align-items:center;justify-content:space-between;gap:0.75rem;">
                <span style="display:flex;align-items:center;gap:0.5rem;font-size:0.8125rem;color:#92400e;">
                    <svg style="width:1rem;height:1rem;flex-shrink:0;color:#d97706;" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z"/>
                    </svg>
                    Compte pendent de verificació — introduïu el codi que heu rebut per correu.
                </span>
                <div style="display:flex;align-items:center;gap:0.75rem;flex-shrink:0;">
                    <a href="{{ route('campus.verification.notice') }}"
                       style="background:#f59e0b;color:#fff;font-size:0.75rem;font-weight:600;padding:0.375rem 0.75rem;border-radius:0.5rem;text-decoration:none;"
                       onmouseover="this.style.background='#d97706'" onmouseout="this.style.background='#f59e0b'">
                        Introduir codi →
                    </a>
                    <form method="POST" action="{{ route('campus.verification.resend') }}" style="display:inline;">
                        @csrf
                        <button type="submit" style="background:none;border:none;font-size:0.75rem;color:#92400e;cursor:pointer;text-decoration:underline;">Reenviar</button>
                    </form>
                </div>
            </div>
        </div>
        @endif
    @endauth

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

    @include('campus.partials.footer')

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
