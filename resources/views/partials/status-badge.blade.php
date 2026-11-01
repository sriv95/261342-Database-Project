@php
    $badge = [
        'pending' => 'bg-amber-100 text-amber-900',
        'paid' => 'bg-emerald-100 text-emerald-800',
        'shipped' => 'bg-emerald-700 text-white',
        'done' => 'bg-gray-900 text-white',
        'cancelled' => 'bg-red-100 text-red-800',
    ][$status] ?? 'bg-gray-100 text-gray-700';
@endphp
<span class="inline-block rounded-full px-3 py-0.5 text-sm font-medium capitalize {{ $badge }}">{{ $status }}</span>
