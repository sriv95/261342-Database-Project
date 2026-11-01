<div class="group relative">
    <a href="{{ route('products.show', $product) }}" class="block">
        <div class="tile aspect-square">
            <img src="{{ asset($product->images->first()?->path ?? 'images/placeholder.svg') }}" alt="" loading="lazy" class="h-full w-full object-contain p-3">
        </div>
        <div class="mt-3">
            <p class="text-sm text-gray-500">{{ $product->brand->name ?? '' }}</p>
            <h3 class="font-medium leading-snug group-hover:underline group-hover:underline-offset-4">{{ $product->name }}</h3>
            <div class="mt-1 flex items-baseline justify-between gap-2">
                <span class="price text-lg">from ฿{{ number_format($product->variants_min_price) }}</span>
                @if ($product->reviews_count)
                    <span class="whitespace-nowrap text-sm text-gray-500"><span class="text-amber-500" aria-hidden="true">★</span> {{ number_format($product->reviews_avg_rating, 1) }} <span class="text-gray-400">({{ $product->reviews_count }})</span></span>
                @endif
            </div>
            @isset($product->sold_monthly)
                <p class="mt-1.5"><span class="inline-block rounded-full bg-shuttle/25 px-2.5 py-0.5 text-xs font-medium text-amber-900">Selling fast: {{ number_format($product->sold_monthly) }} sold this month</span></p>
            @elseif (isset($product->total_sold))
                <p class="mt-1 text-sm text-gray-500">{{ number_format($product->total_sold) }} sold</p>
            @endisset
        </div>
    </a>

    @if (! empty($showWish))
        @php
            $isWished = in_array($product->id, $wishedIds ?? [], true);
            $heartClass = 'absolute right-2 top-2 flex h-9 w-9 items-center justify-center rounded-full bg-white/90 shadow-sm transition hover:scale-110 focus:opacity-100 '
                .($isWished ? 'opacity-100' : 'opacity-0 group-hover:opacity-100 pointer-coarse:opacity-100');
            $heartIcon = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.8" class="h-5 w-5 '
                .($isWished ? 'fill-red-500 stroke-red-500' : 'fill-none stroke-gray-600 hover:stroke-red-500')
                .'"><path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z"/></svg>';
        @endphp
        @auth
            <form method="POST" action="{{ route('wishlist.toggle', $product) }}">
                @csrf
                <button type="submit" class="{{ $heartClass }}" aria-label="{{ $isWished ? 'Remove from wish list' : 'Add to wish list' }}" title="{{ $isWished ? 'Remove from wish list' : 'Add to wish list' }}">{!! $heartIcon !!}</button>
            </form>
        @else
            <a href="{{ route('login') }}" class="{{ $heartClass }}" aria-label="Log in to use wish list" title="Log in to use wish list">{!! $heartIcon !!}</a>
        @endauth
    @endif
</div>
