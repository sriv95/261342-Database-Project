@extends('layouts.app')

@section('title', 'Checkout')

@section('content')
    <h1 class="display mb-6 text-4xl">Checkout</h1>

    <form method="POST" action="{{ route('checkout.store') }}" class="grid items-start gap-10 md:grid-cols-3">
        @csrf
        <div class="space-y-10 md:col-span-2">
            <section>
                <h2 class="display mb-3 text-2xl">Shipping address</h2>
                @forelse ($addresses as $address)
                    <label class="mb-2 flex cursor-pointer gap-3 rounded-md border border-gray-300 bg-white p-4 text-[0.9375rem] has-[:checked]:border-emerald-700 has-[:checked]:bg-emerald-50 has-[:checked]:outline has-[:checked]:outline-1 has-[:checked]:outline-emerald-700">
                        <input type="radio" name="address_id" value="{{ $address->id }}" class="mt-1" @checked(old('address_id', $addresses->first()->id) == $address->id)>
                        <span>
                            <span class="font-medium">{{ $address->recipient }}</span>
                            @if ($address->is_default)<span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800">Default</span>@endif<br>
                            {{ $address->line1 }}, {{ $address->district }}, {{ $address->province }} {{ $address->postal_code }}
                        </span>
                    </label>
                @empty
                    <p class="text-gray-600">No saved address yet. <a href="{{ route('account.show') }}" class="link">Add one in your account</a> before you check out.</p>
                @endforelse
            </section>

            <section>
                <h2 class="display mb-1 text-2xl">Payment</h2>
                <p class="mb-3 text-sm text-gray-500">Demo shop: no real payment is taken.</p>
                @foreach (['card' => 'Credit or debit card', 'promptpay' => 'PromptPay QR', 'cod' => 'Cash on delivery'] as $value => $label)
                    <label class="mb-2 flex cursor-pointer items-center gap-3 rounded-md border border-gray-300 bg-white p-4 text-[0.9375rem] has-[:checked]:border-emerald-700 has-[:checked]:bg-emerald-50 has-[:checked]:outline has-[:checked]:outline-1 has-[:checked]:outline-emerald-700"><input type="radio" name="payment_method" value="{{ $value }}" @checked(old('payment_method', 'card') === $value)> {{ $label }}</label>
                @endforeach
            </section>
        </div>

        <aside class="panel p-5 md:sticky md:top-6">
            <h2 class="display text-xl">Order summary</h2>
            <ul class="mt-4 space-y-3 text-sm">
                @foreach ($items as $item)
                    <li class="flex justify-between gap-3">
                        <span>{{ $item->variant->product->name }}<br><span class="text-gray-500">{{ $item->variant->label }}, qty {{ $item->quantity }}</span></span>
                        <span class="price">฿{{ number_format($item->quantity * $item->variant->price) }}</span>
                    </li>
                @endforeach
            </ul>
            <p class="mt-4 flex items-baseline justify-between border-t border-gray-200 pt-4"><span class="font-medium">Total</span><span class="price text-2xl">฿{{ number_format($subtotal) }}</span></p>
            <button @disabled($addresses->isEmpty()) class="btn btn-primary mt-5 w-full !py-2.5">Place order</button>
        </aside>
    </form>
@endsection
