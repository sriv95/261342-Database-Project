@extends('layouts.app')

@section('title', $product->name)

@section('content')
    <nav aria-label="Breadcrumb" class="mb-5 text-sm text-gray-500">
        <a href="{{ route('products.index') }}" class="hover:underline">Products</a>
        <span aria-hidden="true" class="mx-1.5 text-gray-300">/</span>
        <a href="{{ route('products.index', ['category' => $product->category_id]) }}" class="hover:underline">{{ $product->category->name }}</a>
    </nav>

    <div class="grid gap-8 md:grid-cols-2 md:gap-12">
        <div class="tile aspect-square self-start md:sticky md:top-6">
            <img src="{{ asset($product->images->first()?->path ?? 'images/placeholder.svg') }}" alt="{{ $product->name }}" class="h-full w-full object-contain p-6">
        </div>

        <div>
            <p class="text-gray-500">{{ $product->brand->name }}</p>
            <div class="flex items-start justify-between gap-4">
                <h1 class="display text-4xl sm:text-5xl">{{ $product->name }}</h1>
                @auth
                    <form method="POST" action="{{ route('wishlist.toggle', $product) }}" class="mt-1" data-ajax="wish" data-wished="{{ $wished ? 1 : 0 }}">
                        @csrf
                        <button data-wish-label class="btn btn-line whitespace-nowrap !px-4 !py-1.5 text-sm hover:!border-red-400 hover:!text-red-700">{{ $wished ? '♥ Saved' : '♡ Wish list' }}</button>
                    </form>
                @endauth
            </div>
            @if ($product->reviews_count)
                <p class="mt-2"><span class="text-amber-500" aria-hidden="true">★</span> <span class="font-medium">{{ number_format($product->reviews_avg_rating, 1) }}</span> <a href="#reviews" class="text-sm text-gray-500 hover:underline">{{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</a></p>
            @endif
            <p class="mt-5 max-w-[60ch] text-gray-700">{{ $product->description }}</p>

            @php
                $variantData = $product->variants->map(fn ($v) => [
                    'id' => $v->id, 'color' => $v->color, 'option' => $v->option_name,
                    'price' => (float) $v->price, 'stock' => $v->stock_qty,
                ])->values();
                $colors = $variantData->pluck('color')->filter()->unique()->values();
                $options = $variantData->pluck('option')->filter()->unique()->values();
            @endphp
            <div id="picker" class="mt-7 space-y-5" data-variants='@json($variantData)'>
                @if ($colors->isNotEmpty())
                    <div>
                        <h2 class="mb-2 text-sm font-medium text-gray-600">Color: <span id="color-label" class="text-gray-900"></span></h2>
                        <div class="flex flex-wrap gap-2" id="color-pills">
                            @foreach ($colors as $color)
                                <button type="button" data-color="{{ $color }}" class="pill rounded-full border px-3 py-1 text-sm">{{ $color }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if ($options->isNotEmpty())
                    <div>
                        <h2 class="mb-2 text-sm font-medium text-gray-600">{{ $colors->isNotEmpty() ? 'Option' : 'Options' }}</h2>
                        <div class="flex flex-wrap gap-2" id="option-pills">
                            @foreach ($options as $option)
                                <button type="button" data-option="{{ $option }}" class="pill rounded-md border px-3 py-1 text-sm">{{ $option }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="panel p-5">
                    <div class="flex items-baseline justify-between gap-4">
                        <span id="pick-price" class="price text-4xl"></span>
                        <span id="pick-stock" class="text-sm font-medium"></span>
                    </div>
                    <form id="pick-form" method="POST" action="{{ route('cart.store') }}" class="mt-4 flex items-center gap-3" data-ajax="cart">
                        @csrf
                        <input type="hidden" name="variant_id" id="pick-id">
                        @auth
                            <label class="sr-only" for="pick-qty">Quantity</label>
                            <input type="number" name="quantity" id="pick-qty" value="1" min="1" class="field !w-20 text-center">
                            <button id="pick-add" class="btn btn-primary flex-1 !py-2.5">Add to cart</button>
                        @else
                            <a href="{{ route('login') }}" class="btn btn-primary flex-1 !py-2.5">Log in to buy</a>
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
                    stock.textContent = sel.stock > 0 ? (sel.stock <= 5 ? 'Only ' + sel.stock + ' left' : sel.stock + ' in stock') : 'Out of stock';
                    stock.className = sel.stock > 0 ? 'text-emerald-700' : 'text-red-700';
                    document.getElementById('pick-id').value = sel.id;
                    const qty = document.getElementById('pick-qty'), add = document.getElementById('pick-add');
                    if (qty) { qty.max = Math.min(20, sel.stock); qty.value = Math.min(+qty.value || 1, qty.max || 1); qty.disabled = add.disabled = sel.stock < 1; add.textContent = sel.stock < 1 ? 'Out of stock' : 'Add to cart'; }
                };
                const style = (btn, active, available) => {
                    btn.className = 'pill border-[1.5px] px-4 py-1.5 text-[0.9375rem] ' + (btn.dataset.color !== undefined ? 'rounded-full ' : 'rounded-md ')
                        + (active ? 'border-emerald-700 bg-emerald-700 text-white' : 'border-gray-300 bg-white hover:border-emerald-700')
                        + (available ? '' : ' opacity-50 line-through');
                    btn.setAttribute('aria-pressed', active ? 'true' : 'false');
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

    <section id="reviews" class="mt-16 grid scroll-mt-6 gap-8 border-t border-gray-200 pt-8 md:grid-cols-3">
        <div>
            <h2 class="display text-3xl">Reviews</h2>
            @if ($product->reviews_count)
                <p class="mt-3"><span class="price text-5xl">{{ number_format($product->reviews_avg_rating, 1) }}</span> <span class="text-gray-500">out of 5, {{ $product->reviews_count }} {{ Str::plural('review', $product->reviews_count) }}</span></p>
            @endif

            @auth
                @if ($canReview)
                    <form method="POST" action="{{ route('reviews.store', $product) }}" class="mt-6 space-y-3 text-sm">
                        @csrf
                        <label class="block font-medium" for="rating">{{ $myReview ? 'Edit your review' : 'Write a review' }}</label>
                        <select id="rating" name="rating" class="field !w-auto">
                            @for ($i = 5; $i >= 1; $i--)
                                <option value="{{ $i }}" @selected(old('rating', $myReview?->rating ?? 5) == $i)>{{ str_repeat('★', $i) }} ({{ $i }})</option>
                            @endfor
                        </select>
                        <textarea name="comment" rows="3" aria-label="Your comment" placeholder="Share your experience" class="field">{{ old('comment', $myReview?->comment) }}</textarea>
                        <button class="btn btn-primary">{{ $myReview ? 'Save review' : 'Post review' }}</button>
                    </form>
                @else
                    <p class="mt-4 text-sm text-gray-500">Only customers who bought this product can review it.</p>
                @endif
            @endauth
        </div>

        <div data-stagger class="md:col-span-2">
            @forelse ($product->reviews as $review)
                <article class="border-b border-gray-200 py-5 first:pt-0 last:border-b-0">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-medium">{{ $review->user->name }}</span>
                        <span class="text-amber-500" aria-label="{{ $review->rating }} out of 5 stars">{{ str_repeat('★', $review->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $review->rating) }}</span></span>
                    </div>
                    <p class="mt-1.5 max-w-[65ch] text-gray-700">{{ $review->comment }}</p>
                    @if (auth()->id() === $review->user_id)
                        <form method="POST" action="{{ route('reviews.destroy', $product) }}" class="mt-2">@csrf @method('DELETE')<button class="text-sm text-red-700 hover:underline">Delete my review</button></form>
                    @endif
                </article>
            @empty
                <p class="text-gray-500">No reviews yet. Buy this product to be the first.</p>
            @endforelse
        </div>
    </section>
@endsection
