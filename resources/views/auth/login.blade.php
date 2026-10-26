@extends('layouts.app')

@section('title', 'Log in')

@section('content')
    <div class="mx-auto max-w-sm rounded-lg border border-gray-200 bg-white p-6">
        <h1 class="mb-4 text-xl font-bold">Log in</h1>
        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium" for="email">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm">
                @error('email') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium" for="password">Password</label>
                <input id="password" name="password" type="password" required class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm">
            </div>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Remember me</label>
            <button class="w-full rounded bg-emerald-600 py-2 font-medium text-white hover:bg-emerald-700">Log in</button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-500">No account? <a href="{{ route('register') }}" class="text-emerald-700 hover:underline">Register</a></p>
    </div>
@endsection
