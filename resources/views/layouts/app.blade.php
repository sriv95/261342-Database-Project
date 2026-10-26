<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Home') · {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased flex flex-col">
    <header class="bg-white border-b border-gray-200">
        <nav class="mx-auto max-w-6xl px-4 py-3 flex items-center gap-6">
            <a href="{{ route('home') }}" class="text-lg font-bold text-emerald-700">🏸 {{ config('app.name') }}</a>
            <a href="{{ route('products.index') }}" class="text-sm font-medium hover:text-emerald-700">Products</a>

            <div class="ml-auto flex items-center gap-4 text-sm">
                @auth
                    <span class="text-gray-600">Hi, {{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="font-medium hover:text-emerald-700">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="font-medium hover:text-emerald-700">Log in</a>
                    <a href="{{ route('register') }}" class="rounded bg-emerald-600 px-3 py-1.5 font-medium text-white hover:bg-emerald-700">Register</a>
                @endauth
            </div>
        </nav>
    </header>

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-8">
        @if (session('status'))
            <div class="mb-6 rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @yield('content')
    </main>

    <footer class="border-t border-gray-200 bg-white py-4 text-center text-xs text-gray-500">
        {{ config('app.name') }} · 261342 Term Project
    </footer>
</body>
</html>
