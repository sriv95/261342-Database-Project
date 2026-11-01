@extends('layouts.app')

@section('title', 'My account')

@section('content')
    <h1 class="display mb-6 text-4xl">My account</h1>

    <div class="grid gap-10 md:grid-cols-2">
        <section>
            <h2 class="display mb-4 text-2xl">Profile</h2>
            <form method="POST" action="{{ route('account.update') }}" class="space-y-4 text-[0.9375rem]">
                @csrf @method('PATCH')
                <div>
                    <p class="font-medium">Email</p>
                    <p class="text-gray-600">{{ $user->email }}</p>
                </div>
                <div>
                    <label class="block font-medium" for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="field mt-1">
                </div>
                <div>
                    <label class="block font-medium" for="phone">Phone</label>
                    <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="field mt-1">
                </div>
                <button class="btn btn-primary">Save changes</button>
            </form>
            <p class="mt-5 text-sm text-gray-500">Member since {{ $user->created_at->format('d M Y') }}, {{ $orderCount }} {{ Str::plural('order', $orderCount) }}</p>
        </section>

        <section>
            <h2 class="display mb-4 text-2xl">Addresses</h2>
            @foreach ($addresses as $address)
                <div class="panel mb-3 p-4 text-[0.9375rem]">
                    <p class="font-medium">{{ $address->recipient }} @if ($address->is_default)<span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Default</span>@endif</p>
                    <p class="text-gray-600">{{ $address->line1 }}, {{ $address->district }}, {{ $address->province }} {{ $address->postal_code }}</p>
                    <div class="mt-2 flex gap-4 text-sm">
                        @unless ($address->is_default)
                            <form method="POST" action="{{ route('addresses.default', $address) }}">@csrf @method('PATCH')<button class="link">Make default</button></form>
                        @endunless
                        <form method="POST" action="{{ route('addresses.destroy', $address) }}">@csrf @method('DELETE')<button class="text-red-700 hover:underline">Remove</button></form>
                    </div>
                </div>
            @endforeach

            <h3 class="display mt-8 mb-3 text-xl">Add an address</h3>
            <form method="POST" action="{{ route('addresses.store') }}" class="grid grid-cols-2 gap-3 text-[0.9375rem]">
                @csrf
                <input name="recipient" aria-label="Recipient" placeholder="Recipient" value="{{ old('recipient') }}" required class="field col-span-2">
                <input name="line1" aria-label="Address line" placeholder="Address line" value="{{ old('line1') }}" required class="field col-span-2">
                <input name="district" aria-label="District" placeholder="District" value="{{ old('district') }}" required class="field">
                <input name="province" aria-label="Province" placeholder="Province" value="{{ old('province') }}" required class="field">
                <input name="postal_code" aria-label="Postal code" placeholder="Postal code" value="{{ old('postal_code') }}" required class="field">
                <button class="btn btn-primary">Add address</button>
            </form>
        </section>
    </div>
@endsection
