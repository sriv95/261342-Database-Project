<a href="{{ route('products.show', $product) }}" class="group block overflow-hidden rounded-lg border border-gray-200 bg-white hover:shadow-md">
    <img src="{{ asset($product->images->first()?->path ?? 'images/placeholder.svg') }}" alt="{{ $product->name }}" class="aspect-square w-full object-cover">
    <div class="p-3">
        <p class="text-xs text-gray-500">{{ $product->brand->name ?? '' }}</p>
        <h3 class="font-medium group-hover:text-emerald-700">{{ $product->name }}</h3>
        <div class="mt-1 flex items-center justify-between text-sm">
            <span class="font-semibold">from ฿{{ number_format($product->variants_min_price) }}</span>
            @if ($product->reviews_count)
                <span class="text-amber-500">★ {{ number_format($product->reviews_avg_rating, 1) }} <span class="text-gray-400">({{ $product->reviews_count }})</span></span>
            @endif
        </div>
    </div>
</a>
