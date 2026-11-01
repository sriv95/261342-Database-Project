<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Home') | {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Thai:wght@400;500;600&family=Kanit:ital,wght@0,500;1,500;1,600;1,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col antialiased">
    <header class="on-floor bg-floor text-line">
        <nav class="mx-auto flex max-w-6xl flex-wrap items-center gap-x-6 gap-y-3 px-4 py-3">
            <a href="{{ route('home') }}" class="display flex items-center gap-2 text-xl font-bold">
                @include('partials.shuttle', ['class' => 'h-8 w-8 text-line'])
                {{ config('app.name') }}
            </a>
            <a href="{{ route('products.index') }}" class="text-[0.9375rem] font-medium hover:underline hover:underline-offset-4">Products</a>

            <form method="GET" action="{{ route('products.index') }}" role="search" class="order-last w-full sm:order-none sm:w-auto sm:min-w-56 sm:flex-1 sm:max-w-sm">
                <label class="sr-only" for="nav-search">Search products</label>
                <input id="nav-search" type="search" name="q" placeholder="Search rackets, shoes, shuttles" class="w-full rounded-full border-0 bg-line/12 px-4 py-1.5 text-sm text-line placeholder:text-line/60 focus:bg-line focus:text-gray-900 focus:placeholder:text-gray-400">
            </form>

            <div class="ml-auto flex flex-wrap items-center gap-x-5 gap-y-2 text-[0.9375rem]">
                @auth
                    @if (auth()->user()->isAdmin())
                        <a href="{{ route('admin.dashboard') }}" class="rounded-full bg-shuttle px-3.5 py-1 text-sm font-medium text-gray-900 hover:bg-white">Admin</a>
                    @endif
                    <a href="{{ route('wishlist.index') }}" class="font-medium hover:underline hover:underline-offset-4">Wish list</a>
                    <a href="{{ route('orders.index') }}" class="font-medium hover:underline hover:underline-offset-4">Orders</a>
                    <a href="{{ route('cart.index') }}" class="font-medium hover:underline hover:underline-offset-4">Cart @if ($cartCount)<span class="ml-1 inline-block min-w-5 rounded-full bg-shuttle px-1.5 text-center text-xs font-semibold leading-5 text-gray-900">{{ $cartCount }}</span>@endif</a>
                    <a href="{{ route('account.show') }}" class="text-line/75 hover:text-line hover:underline hover:underline-offset-4" title="My account">{{ auth()->user()->name }}</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="font-medium hover:underline hover:underline-offset-4">Log out</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="font-medium hover:underline hover:underline-offset-4">Log in</a>
                    <a href="{{ route('register') }}" class="btn btn-light !py-1.5">Register</a>
                @endauth
            </div>
        </nav>
    </header>

    @yield('hero')

    <main class="mx-auto w-full max-w-6xl flex-1 px-4 py-10">
        @if (session('status'))
            <div role="status" class="mb-6 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">{{ session('status') }}</div>
        @endif
        @if ($errors->hasAny(['stock', 'address_id', 'review', 'variant', 'user']))
            <div role="alert" class="mb-6 rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                @foreach (['stock', 'address_id', 'review', 'variant', 'user'] as $key)@foreach ($errors->get($key) as $message)<p>{{ $message }}</p>@endforeach @endforeach
            </div>
        @endif
        @yield('content')
    </main>
</body>
</html>
