@extends('layouts.app')

@section('title', 'My account')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">My account</h1>

    <div class="grid gap-6 md:grid-cols-2">
        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="mb-3 font-semibold">Profile</h2>
            <form method="POST" action="{{ route('account.update') }}" class="space-y-3 text-sm">
                @csrf @method('PATCH')
                <div>
                    <label class="block font-medium">Email</label>
                    <p class="text-gray-600">{{ $user->email }}</p>
                </div>
                <div>
                    <label class="block font-medium" for="name">Name</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block font-medium" for="phone">Phone</label>
                    <input id="phone" name="phone" value="{{ old('phone', $user->phone) }}" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <button class="rounded bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">Save</button>
            </form>
            <p class="mt-4 text-sm text-gray-500">Member since {{ $user->created_at->format('d M Y') }} · {{ $orderCount }} order(s)</p>
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="mb-3 font-semibold">Addresses</h2>
            @foreach ($addresses as $address)
                <div class="mb-2 rounded border border-gray-200 p-3 text-sm">
                    <p class="font-medium">{{ $address->recipient }} @if ($address->is_default)<span class="text-xs text-emerald-700">default</span>@endif</p>
                    <p class="text-gray-600">{{ $address->line1 }}, {{ $address->district }}, {{ $address->province }} {{ $address->postal_code }}</p>
                    <div class="mt-2 flex gap-3">
                        @unless ($address->is_default)
                            <form method="POST" action="{{ route('addresses.default', $address) }}">@csrf @method('PATCH')<button class="text-emerald-700 hover:underline">Make default</button></form>
                        @endunless
                        <form method="POST" action="{{ route('addresses.destroy', $address) }}">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Remove</button></form>
                    </div>
                </div>
            @endforeach

            <h3 class="mt-4 mb-2 text-sm font-semibold">Add address</h3>
            <form method="POST" action="{{ route('addresses.store') }}" class="grid grid-cols-2 gap-2 text-sm">
                @csrf
                <input name="recipient" placeholder="Recipient" value="{{ old('recipient') }}" required class="col-span-2 rounded border border-gray-300 px-3 py-2">
                <input name="line1" placeholder="Address line" value="{{ old('line1') }}" required class="col-span-2 rounded border border-gray-300 px-3 py-2">
                <input name="district" placeholder="District" value="{{ old('district') }}" required class="rounded border border-gray-300 px-3 py-2">
                <input name="province" placeholder="Province" value="{{ old('province') }}" required class="rounded border border-gray-300 px-3 py-2">
                <input name="postal_code" placeholder="Postal code" value="{{ old('postal_code') }}" required class="rounded border border-gray-300 px-3 py-2">
                <button class="rounded bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">Add</button>
            </form>
        </section>
    </div>
@endsection
