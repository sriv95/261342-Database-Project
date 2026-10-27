@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <h1 class="mb-6 text-2xl font-bold">Checkout</h1>

    <form method="POST" action="{{ route('checkout.store') }}" class="grid gap-8 md:grid-cols-3">
        @csrf
        <div class="space-y-6 md:col-span-2">
            <section class="rounded-lg border border-gray-200 bg-white p-4">
                <h2 class="mb-3 font-semibold">Shipping address</h2>
                @forelse ($addresses as $address)
                    <label class="mb-2 flex cursor-pointer gap-3 rounded border border-gray-200 p-3 text-sm has-[:checked]:border-emerald-600">
                        <input type="radio" name="address_id" value="{{ $address->id }}" @checked(old('address_id', $addresses->first()->id) == $address->id)>
                        <span>
                            <span class="font-medium">{{ $address->recipient }}</span>
                            @if ($address->is_default)<span class="ml-1 text-xs text-emerald-700">default</span>@endif<br>
                            {{ $address->line1 }}, {{ $address->district }}, {{ $address->province }} {{ $address->postal_code }}
                        </span>
                    </label>
                @empty
                    <p class="text-sm text-gray-500">No saved address. <a href="{{ route('account.show') }}" class="text-emerald-700 hover:underline">Add one in your account</a> first.</p>
                @endforelse
            </section>

            <section class="rounded-lg border border-gray-200 bg-white p-4">
                <h2 class="mb-1 font-semibold">Payment</h2>
                <p class="mb-3 text-xs text-gray-500">Demo shop: no real payment is taken.</p>
                @foreach (['card' => 'Credit / debit card', 'promptpay' => 'PromptPay QR', 'cod' => 'Cash on delivery'] as $value => $label)
                    <label class="mb-1 flex items-center gap-2 text-sm"><input type="radio" name="payment_method" value="{{ $value }}" @checked(old('payment_method', 'card') === $value)> {{ $label }}</label>
                @endforeach
            </section>
        </div>

        <aside class="h-fit rounded-lg border border-gray-200 bg-white p-4">
            <h2 class="mb-3 font-semibold">Order summary</h2>
            <ul class="space-y-2 text-sm">
                @foreach ($items as $item)
                    <li class="flex justify-between gap-2">
                        <span>{{ $item->variant->product->name }} <span class="text-gray-500">({{ $item->variant->label }}) × {{ $item->quantity }}</span></span>
                        <span>฿{{ number_format($item->quantity * $item->variant->price) }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 flex justify-between border-t border-gray-200 pt-3 font-bold"><span>Total</span><span>฿{{ number_format($subtotal) }}</span></p>
            <button @disabled($addresses->isEmpty()) class="mt-4 w-full rounded bg-emerald-600 py-2 font-medium text-white hover:bg-emerald-700 disabled:opacity-50">Place order</button>
        </aside>
    </form>
@endsection
