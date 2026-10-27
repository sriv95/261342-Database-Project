@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <nav class="mb-4 text-sm text-gray-500">
        <a href="{{ route('products.index') }}" class="hover:underline">Products</a> /
        <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="hover:underline">{{ $product->category->name }}</a>
    </nav>

    <div class="grid gap-8 md:grid-cols-2">
        <img src="{{ asset($product->images->first()?->path ?? 'images/placeholder.svg') }}" alt="{{ $product->name }}" class="w-full rounded-lg border border-gray-200 bg-white">

        <div>
            <p class="text-sm text-gray-500">{{ $product->brand->name }}</p>
            <div class="flex items-start justify-between gap-4">
                <h1 class="text-3xl font-bold">{{ $product->name }}</h1>
                @auth
                    <form method="POST" action="{{ route('wishlist.toggle', $product) }}">
                        @csrf
                        <button class="whitespace-nowrap rounded border border-gray-300 px-3 py-1.5 text-sm hover:border-red-400">{{ $wished ? '♥ Saved' : '♡ Wish list' }}</button>
                    </form>
                @endauth
            </div>
            @if ($product->reviews_count)
                <p class="mt-1 text-amber-500">★ {{ number_format($product->reviews_avg_rating, 1) }} <span class="text-sm text-gray-400">({{ $product->reviews_count }} reviews)</span></p>
            @endif
            <p class="mt-4 text-gray-700">{{ $product->description }}</p>

            @php
                $variantData = $product->variants->map(fn ($v) => [
                    'id' => $v->id, 'color' => $v->color, 'option' => $v->option_name,
                    'price' => (float) $v->price, 'stock' => $v->stock_qty,
                ])->values();
                $colors = $variantData->pluck('color')->filter()->unique()->values();
                $options = $variantData->pluck('option')->filter()->unique()->values();
            @endphp
            <div id="picker" class="mt-6 space-y-4" data-variants='@json($variantData)'>
                @if ($colors->isNotEmpty())
                    <div>
                        <h2 class="mb-2 text-sm font-semibold uppercase text-gray-500">Color: <span id="color-label" class="normal-case text-gray-900"></span></h2>
                        <div class="flex flex-wrap gap-2" id="color-pills">
                            @foreach ($colors as $color)
                                <button type="button" data-color="{{ $color }}" class="pill rounded-full border px-3 py-1 text-sm">{{ $color }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($options->isNotEmpty())
                    <div>
                        <h2 class="mb-2 text-sm font-semibold uppercase text-gray-500">{{ $colors->isNotEmpty() ? 'Option' : 'Options' }}</h2>
                        <div class="flex flex-wrap gap-2" id="option-pills">
                            @foreach ($options as $option)
                                <button type="button" data-option="{{ $option }}" class="pill rounded-md border px-3 py-1 text-sm">{{ $option }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="flex flex-wrap items-center gap-4 rounded-lg border border-gray-200 bg-white px-4 py-3 text-sm">
                    <span id="pick-price" class="text-lg font-semibold"></span>
                    <span id="pick-stock"></span>
                    <form id="pick-form" method="POST" action="{{ route('cart.store') }}" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="variant_id" id="pick-id">
                        @auth
                            <input type="number" name="quantity" id="pick-qty" value="1" min="1" class="w-16 rounded border border-gray-300 px-2 py-1">
                            <button id="pick-add" class="rounded bg-emerald-600 px-4 py-1.5 text-white hover:bg-emerald-700 disabled:bg-gray-300">Add to cart</button>
                        @else
                            <a href="{{ route('login') }}" class="text-emerald-700 hover:underline">Log in to buy</a>
                        @endauth
                    </form>
                </div>
            </div>
            <script>
                (() => {
                    const root = document.getElementById('picker');
                    const variants = JSON.parse(root.dataset.variants);
                    let sel = variants.find(v => v.stock > 0) || variants[0];
                    const find = (color, option) => variants.find(v => v.color === color && v.option === option);
                    // pick a dimension value; if the combo does not exist, keep the value and take the first variant that has it
                    const choose = (key, value) => {
                        const next = {color: sel.color, option: sel.option, [key]: value};
                        sel = find(next.color, next.option)
                            || variants.find(v => v[key] === value && v.stock > 0)
                            || variants.find(v => v[key] === value);
                        render();
                    };
                    const render = () => {
                        root.querySelectorAll('[data-color]').forEach(b => style(b, b.dataset.color === sel.color,
                            variants.some(v => v.color === b.dataset.color && v.stock > 0)));
                        root.querySelectorAll('[data-option]').forEach(b => style(b, b.dataset.option === sel.option,
                            variants.some(v => v.option === b.dataset.option && (sel.color === null || v.color === sel.color) && v.stock > 0)));
                        const label = document.getElementById('color-label');
                        if (label) label.textContent = sel.color;
                        document.getElementById('pick-price').textContent = '฿' + sel.price.toLocaleString('en-US', {maximumFractionDigits: 0});
                        const stock = document.getElementById('pick-stock');
                        stock.textContent = sel.stock > 0 ? sel.stock + ' in stock' : 'Out of stock';
                        stock.className = sel.stock > 0 ? 'text-emerald-600' : 'text-red-500';
                        document.getElementById('pick-id').value = sel.id;
                        const qty = document.getElementById('pick-qty'), add = document.getElementById('pick-add');
                        if (qty) { qty.max = Math.min(20, sel.stock); qty.value = Math.min(+qty.value || 1, qty.max || 1); qty.disabled = add.disabled = sel.stock < 1; }
                    };
                    const style = (btn, active, available) => {
                        btn.className = 'pill border px-3 py-1 text-sm ' + (btn.dataset.color !== undefined ? 'rounded-full ' : 'rounded-md ')
                            + (active ? 'border-emerald-600 bg-emerald-600 text-white' : 'border-gray-300 bg-white hover:border-emerald-500')
                            + (available ? '' : ' opacity-50 line-through');
                    };
                    root.addEventListener('click', e => {
                        const b = e.target.closest('.pill');
                        if (!b) return;
                        b.dataset.color !== undefined ? choose('color', b.dataset.color) : choose('option', b.dataset.option);
                    });
                    render();
                })();
            </script>
        </div>
    </div>

    <section class="mt-12">
        <h2 class="mb-4 text-xl font-semibold">Reviews</h2>

        @auth
            @if ($canReview)
                <form method="POST" action="{{ route('reviews.store', $product) }}" class="mb-6 space-y-3 rounded-lg border border-gray-200 bg-white p-4 text-sm">
                    @csrf
                    <p class="font-medium">{{ $myReview ? 'Edit your review' : 'Write a review' }}</p>
                    <select name="rating" class="rounded border border-gray-300 px-3 py-2">
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected(old('rating', $myReview?->rating ?? 5) == $i)>{{ str_repeat('★', $i) }} ({{ $i }})</option>
                        @endfor
                    </select>
                    <textarea name="comment" rows="3" placeholder="Share your experience" class="w-full rounded border border-gray-300 px-3 py-2">{{ old('comment', $myReview?->comment) }}</textarea>
                    <button class="rounded bg-emerald-600 px-4 py-2 font-medium text-white hover:bg-emerald-700">Submit</button>
                </form>
            @else
                <p class="mb-4 text-sm text-gray-500">Only customers who bought this product can review it.</p>
            @endif
        @endauth

        @forelse ($product->reviews as $review)
            <article class="mb-3 rounded-lg border border-gray-200 bg-white p-4">
                <div class="flex items-center justify-between text-sm">
                    <span class="font-medium">{{ $review->user->name }}</span>
                    <span class="text-amber-500">{{ str_repeat('★', $review->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                </div>
                <p class="mt-1 text-sm text-gray-700">{{ $review->comment }}</p>
                @if (auth()->id() === $review->user_id)
                    <form method="POST" action="{{ route('reviews.destroy', $product) }}" class="mt-2">@csrf @method('DELETE')<button class="text-xs text-red-600 hover:underline">Delete my review</button></form>
                @endif
            </article>
        @empty
            <p class="text-sm text-gray-500">No reviews yet.</p>
        @endforelse
    </section>
@endsection
