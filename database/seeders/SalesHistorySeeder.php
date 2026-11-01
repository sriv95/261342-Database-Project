<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Adds fake order history spread over the last ~45 days so the
 * "trending" / "sold per month" numbers on the homepage have data.
 * Safe to run on top of an existing database (only adds orders).
 */
class SalesHistorySeeder extends Seeder
{
    public function run(int $orders = 90): void
    {
        $customers = User::where('role', 'customer')->with('addresses')->get()->filter(fn ($u) => $u->addresses->isNotEmpty());
        $products = Product::where('is_active', true)->with('variants')->get()->filter(fn ($p) => $p->variants->isNotEmpty())->values();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Give every product a popularity weight so some clearly out-sell others.
        $weights = $products->mapWithKeys(fn ($p) => [$p->id => fake()->numberBetween(1, 10) ** 2]);

        for ($n = 0; $n < $orders; $n++) {
            $customer = $customers->random();
            $placedAt = now()->subDays(fake()->numberBetween(0, 45))->subMinutes(fake()->numberBetween(0, 1439));

            $order = Order::create([
                'user_id' => $customer->id,
                'address_id' => $customer->addresses->first()->id,
                'total' => 0,
                'status' => fake()->randomElement(['paid', 'shipped', 'done', 'done', 'done', 'cancelled']),
                'placed_at' => $placedAt,
            ]);

            $total = 0;
            $picked = [];
            foreach (range(1, fake()->numberBetween(1, 3)) as $_) {
                $product = $this->weightedPick($products, $weights);
                if (isset($picked[$product->id])) {
                    continue;
                }
                $picked[$product->id] = true;

                $variant = $product->variants->random();
                $qty = fake()->numberBetween(1, 4);
                $order->items()->create([
                    'variant_id' => $variant->id,
                    'quantity' => $qty,
                    'unit_price' => $variant->price,
                ]);
                $total += $qty * $variant->price;
            }

            $order->update(['total' => $total]);
        }
    }

    private function weightedPick($products, $weights): Product
    {
        $roll = fake()->numberBetween(1, (int) $weights->sum());
        foreach ($products as $product) {
            $roll -= $weights[$product->id];
            if ($roll <= 0) {
                return $product;
            }
        }

        return $products->last();
    }
}
