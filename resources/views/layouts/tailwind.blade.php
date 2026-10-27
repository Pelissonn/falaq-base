<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'FalaQ-Eu_T_3scuto')</title>
    {{-- Rode `npm install && npm run build` (ou `npm run dev`) para gerar o Tailwind. --}}
    @if (file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-gray-100 text-gray-800 min-h-screen">
    <nav class="bg-white shadow mb-8">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center justify-between">
            <a href="{{ route('eventos.index') }}" class="font-bold text-lg text-indigo-600">
                🚀 FalaQ-Eu_T_3scuto
            </a>
            <div class="flex items-center gap-4 text-sm">
                @auth
                    <span class="text-gray-600">{{ auth()->user()->name }}</span>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="text-indigo-600 hover:underline">Sair</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="text-indigo-600 hover:underline">Entrar</a>
                    <a href="{{ route('register') }}" class="text-indigo-600 hover:underline">Cadastrar</a>
                @endauth
            </div>
        </div>
    </nav>

    <main class="px-4 pb-12">
        @if (session('sucesso'))
            <div class="max-w-2xl mx-auto mb-6 rounded-lg bg-green-100 text-green-800 px-4 py-3">
                {{ session('sucesso') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
