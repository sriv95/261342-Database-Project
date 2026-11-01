@extends('admin.layout')

@section('title', 'Admin reviews')

@section('admin')
    <h1 class="display mb-4 text-3xl">Reviews</h1>
    @forelse ($reviews as $review)
        <article class="mb-3 flex items-start justify-between gap-4 rounded-lg border border-gray-200 bg-white p-4 text-sm">
            <div>
                <p><span class="font-medium">{{ $review->user->name }}</span> on <a href="{{ route('products.show', $review->product) }}" class="text-emerald-700 hover:underline">{{ $review->product->name }}</a>
                    <span class="ml-2 text-amber-500">{{ str_repeat('★', $review->rating) }}<span class="text-gray-300">{{ str_repeat('★', 5 - $review->rating) }}</span></span></p>
                <p class="mt-1 text-gray-700">{{ $review->comment }}</p>
                <p class="mt-1 text-xs text-gray-400">{{ $review->created_at->format('d M Y H:i') }}</p>
            </div>
            <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Delete this review?')">@csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button></form>
        </article>
    @empty
        <p class="py-12 text-center text-gray-500">No reviews.</p>
    @endforelse
    {{ $reviews->links() }}
@endsection
