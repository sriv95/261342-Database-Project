@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="mx-auto grid max-w-3xl overflow-hidden rounded-md border border-gray-200 bg-white md:grid-cols-5">
        <div class="on-floor relative hidden bg-floor p-8 text-line md:col-span-2 md:block">
            @include('partials.court-lines', ['class' => 'pointer-events-none absolute inset-0 h-full w-full text-line'])
            <p class="display relative text-3xl font-bold">Welcome back.</p>
        </div>
        <div class="p-8 md:col-span-3">
            <h1 class="display text-3xl">Log in</h1>
            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="email" class="field mt-1">
                    @error('email') <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium" for="password">Password</label>
                    <input id="password" name="password" type="password" required autocomplete="current-password" class="field mt-1">
                </div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Keep me logged in</label>
                <button class="btn btn-primary w-full !py-2.5">Log in</button>
            </form>
            <p class="mt-5 text-sm text-gray-600">No account yet? <a href="{{ route('register') }}" class="link">Create one</a></p>
        </div>
    </div>
@endsection
