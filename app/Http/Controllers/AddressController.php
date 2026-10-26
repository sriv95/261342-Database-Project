<?php

namespace App\Http\Controllers;

use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AddressController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'recipient' => ['required', 'string', 'max:100'],
            'line1' => ['required', 'string', 'max:255'],
            'district' => ['required', 'string', 'max:80'],
            'province' => ['required', 'string', 'max:80'],
            'postal_code' => ['required', 'string', 'max:10'],
        ]);

        $user = $request->user();
        $data['is_default'] = ! $user->addresses()->exists();
        $user->addresses()->create($data);

        return back()->with('status', 'Address added.');
    }

    public function makeDefault(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);

        DB::transaction(function () use ($request, $address) {
            $request->user()->addresses()->update(['is_default' => false]);
            $address->update(['is_default' => true]);
        });

        return back()->with('status', 'Default address changed.');
    }

    public function destroy(Request $request, Address $address)
    {
        $this->authorizeAddress($request, $address);

        DB::transaction(function () use ($request, $address) {
            $wasDefault = $address->is_default;
            $address->update(['is_default' => false]);
            $address->delete(); // soft delete keeps past orders intact

            if ($wasDefault) {
                $request->user()->addresses()->first()?->update(['is_default' => true]);
            }
        });

        return back()->with('status', 'Address removed.');
    }

    private function authorizeAddress(Request $request, Address $address): void
    {
        abort_unless($address->user_id === $request->user()->id, 403);
    }
}
