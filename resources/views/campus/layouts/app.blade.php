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

    <footer class="border-t border-gray-200 text-center text-xs text-gray-400 py-4">
        Campus de Formació Continuada
    </footer>
</body>
</html>
