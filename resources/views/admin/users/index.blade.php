@extends('admin.layout')

@section('title', 'Admin users')

@section('admin')
    <div class="mb-4 flex items-center justify-between">
        <h1 class="display text-3xl">Users</h1>
        <form method="GET"><input type="search" name="q" value="{{ request('q') }}" placeholder="Search name or email" class="rounded border border-gray-300 px-3 py-1.5 text-sm"></form>
    </div>

    <table class="w-full rounded-lg border border-gray-200 bg-white text-sm">
        <thead class="bg-gray-50 text-left text-xs text-gray-500"><tr><th class="p-3">Name</th><th>Email</th><th>Joined</th><th class="text-right">Orders</th><th class="text-right">Spent</th><th class="p-3">Role</th></tr></thead>
        <tbody>
            @foreach ($users as $user)
                <tr class="border-t border-gray-100">
                    <td class="p-3 font-medium">{{ $user->name }}</td>
                    <td>{{ $user->email }}</td>
                    <td>{{ $user->created_at->format('d M Y') }}</td>
                    <td class="text-right">{{ $user->orders_count }}</td>
                    <td class="text-right">฿{{ number_format($user->spent ?? 0) }}</td>
                    <td class="p-3">
                        @if ($user->is(auth()->user()))
                            <span class="text-gray-400">{{ $user->role }} (you)</span>
                        @else
                            <form method="POST" action="{{ route('admin.users.role', $user) }}" class="flex gap-1">
                                @csrf @method('PATCH')
                                <select name="role" class="rounded border border-gray-300 px-2 py-1 text-xs">
                                    @foreach (['customer', 'admin'] as $r)<option value="{{ $r }}" @selected($user->role === $r)>{{ $r }}</option>@endforeach
                                </select>
                                <button class="text-xs text-emerald-700 hover:underline">Set</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div class="mt-4">{{ $users->links() }}</div>
@endsection
