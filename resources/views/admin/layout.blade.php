@extends('layouts.app')

@section('content')
    <div class="mb-6 flex flex-wrap gap-1 border-b border-gray-200 pb-3 text-sm">
        @foreach (['dashboard' => 'Dashboard', 'products.index' => 'Products', 'orders.index' => 'Orders', 'users.index' => 'Users', 'reviews.index' => 'Reviews'] as $route => $label)
            <a href="{{ route('admin.'.$route) }}" class="rounded px-3 py-1.5 font-medium {{ request()->routeIs('admin.'.explode('.', $route)[0].'*') ? 'bg-gray-900 text-white' : 'text-gray-700 hover:bg-gray-200' }}">{{ $label }}</a>
        @endforeach
    </div>
    @yield('admin')
@endsection
