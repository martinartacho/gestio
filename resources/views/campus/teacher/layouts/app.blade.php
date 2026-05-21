<!DOCTYPE html>
<html lang="ca">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Portal Professorat') — {{ setting('campus_name', 'Campus') }}</title>
    @if(setting('campus_favicon_url'))
        <link rel="icon" href="{{ setting('campus_favicon_url') }}">
    @endif
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 text-gray-900 min-h-screen flex flex-col">

    <nav class="bg-white border-b border-gray-200 shadow-sm">
        <div class="max-w-6xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('campus.catalog.index') }}" class="text-xl font-bold text-indigo-700 hover:text-indigo-900">
                {{ setting('campus_name', 'Campus') }}
            </a>
            <div class="flex items-center gap-4 text-sm">
                @auth('teacher')
                    <a href="{{ route('teacher.portal.courses') }}" class="text-gray-600 hover:text-indigo-700">Els meus cursos</a>
                    @if(setting('documents_enabled', true))
                    <a href="{{ route('teacher.portal.documents') }}" class="text-gray-600 hover:text-indigo-700">Documents</a>
                    @endif
                    <a href="{{ route('teacher.portal.liquidations') }}" class="text-gray-600 hover:text-indigo-700">Liquidacions</a>
                    <a href="{{ route('teacher.portal.profile') }}" class="text-gray-600 hover:text-indigo-700">Perfil</a>
                    <form method="POST" action="{{ route('teacher.logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-600 hover:text-red-600">Sortir</button>
                    </form>
                    <span class="text-indigo-700 font-medium">{{ auth('teacher')->user()->first_name }}</span>
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
        {{ setting('campus_name', 'Campus de Formació Continuada') }}
        &nbsp;·&nbsp;
        <a href="{{ route('campus.catalog.index') }}" class="hover:text-gray-600">Catàleg</a>
        &nbsp;·&nbsp;
        <a href="{{ route('campus.login') }}" class="hover:text-gray-600">Alumnat</a>
        &nbsp;·&nbsp;
        <a href="{{ route('teacher.login') }}" class="hover:text-gray-600">Professorat</a>
        &nbsp;·&nbsp;
        <a href="/admin" class="hover:text-gray-600">Administració</a>
    </footer>

</body>
</html>
