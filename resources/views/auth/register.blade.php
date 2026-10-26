@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <div class="mx-auto max-w-sm rounded-lg border border-gray-200 bg-white p-6">
        <h1 class="mb-4 text-xl font-bold">Create account</h1>
        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf
            @foreach ([['name', 'Name', 'text'], ['email', 'Email', 'email'], ['phone', 'Phone (optional)', 'tel'], ['password', 'Password', 'password'], ['password_confirmation', 'Confirm password', 'password']] as [$field, $label, $type])
                <div>
                    <label class="block text-sm font-medium" for="{{ $field }}">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" @if (! in_array($type, ['password'])) value="{{ old($field) }}" @endif @required($field !== 'phone') class="mt-1 w-full rounded border border-gray-300 px-3 py-2 text-sm">
                    @error($field) <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            @endforeach
            <button class="w-full rounded bg-emerald-600 py-2 font-medium text-white hover:bg-emerald-700">Register</button>
        </form>
        <p class="mt-4 text-center text-sm text-gray-500">Have an account? <a href="{{ route('login') }}" class="text-emerald-700 hover:underline">Log in</a></p>
    </div>
@endsection
