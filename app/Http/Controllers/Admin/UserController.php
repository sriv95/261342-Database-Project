<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->withCount('orders')
            ->withSum(['orders as spent' => fn ($q) => $q->where('status', '!=', 'cancelled')], 'total')
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w->where('name', 'like', "%{$term}%")->orWhere('email', 'like', "%{$term}%")))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', ['users' => $users]);
    }

    public function updateRole(Request $request, User $user)
    {
        $data = $request->validate(['role' => ['required', 'in:customer,admin']]);

        if ($user->is($request->user())) {
            return back()->withErrors(['user' => 'You cannot change your own role.']);
        }

        $user->update($data);

        return back()->with('status', "{$user->name} is now {$data['role']}.");
    }
}
