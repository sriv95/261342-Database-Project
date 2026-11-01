@extends('layouts.app')

@section('title', 'Register')

@section('content')
    <div class="mx-auto grid max-w-3xl overflow-hidden rounded-md border border-gray-200 bg-white md:grid-cols-5">
        <div class="on-floor relative hidden bg-floor p-8 text-line md:col-span-2 md:block">
            @include('partials.court-lines', ['class' => 'pointer-events-none absolute inset-0 h-full w-full text-line'])
            <p class="display relative text-3xl font-bold">Join the club.</p>
            <p class="relative mt-3 text-line/85">Save gear to your wish list, review what you bought and reorder in a few clicks.</p>
        </div>
        <div class="p-8 md:col-span-3">
            <h1 class="display text-3xl">Create account</h1>
            <form method="POST" action="{{ route('register') }}" class="mt-6 space-y-4">
                @csrf
                @foreach ([['name', 'Name', 'text', 'name'], ['email', 'Email', 'email', 'email'], ['phone', 'Phone (optional)', 'tel', 'tel'], ['password', 'Password', 'password', 'new-password'], ['password_confirmation', 'Confirm password', 'password', 'new-password']] as [$field, $label, $type, $auto])
                    <div>
                        <label class="block text-sm font-medium" for="{{ $field }}">{{ $label }}</label>
                        <input id="{{ $field }}" name="{{ $field }}" type="{{ $type }}" autocomplete="{{ $auto }}" @if (! in_array($type, ['password'])) value="{{ old($field) }}" @endif @required($field !== 'phone') class="field mt-1">
                        @error($field) <p class="mt-1 text-sm text-red-700">{{ $message }}</p> @enderror
                    </div>
                @endforeach
                <button class="btn btn-primary w-full !py-2.5">Create account</button>
            </form>
            <p class="mt-5 text-sm text-gray-600">Already have an account? <a href="{{ route('login') }}" class="link">Log in</a></p>
        </div>
    </div>
@endsection
