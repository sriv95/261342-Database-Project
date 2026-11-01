<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Adds fake customers, order history (spread over the last ~4 months) and
 * reviews so the trending / sold / rating numbers have realistic data.
 * Safe to run on top of an existing database: it only adds rows.
 */
class SalesHistorySeeder extends Seeder
{
    private const EXTRA_CUSTOMERS = 20;

    private const ORDERS = 400;

    private const DAYS = 120;

    private const COMMENTS = [
        5 => ['Excellent, exactly what I needed.', 'Top quality, fast delivery.', 'Love it, will buy again.', 'Feels amazing on court.'],
        4 => ['Great quality, arrived quickly.', 'Good value for the price.', 'Solid product, happy with it.', 'Exactly as described.'],
        3 => ['Decent, but nothing special.', 'OK for the price.', 'Packaging was a bit damaged but product is fine.'],
        2 => ['Not as good as expected.'],
    ];

    public function run(): void
    {
        $customers = $this->seedCustomers();
        $products = Product::where('is_active', true)->with('variants')->get()->filter(fn ($p) => $p->variants->isNotEmpty())->values();

        if ($customers->isEmpty() || $products->isEmpty()) {
            return;
        }

        // Popularity weight per product so some clearly out-sell others.
        $weights = $products->mapWithKeys(fn ($p) => [$p->id => fake()->numberBetween(1, 10) ** 2]);
        $reviewed = Review::get(['user_id', 'product_id'])->mapWithKeys(fn ($r) => [$r->user_id.'-'.$r->product_id => true])->all();

        for ($n = 0; $n < self::ORDERS; $n++) {
            $customer = $customers->random();
            // Squared random => a little more weight on recent days (shop growing).
            $daysAgo = (int) floor((1 - sqrt(fake()->randomFloat(4, 0, 1))) * self::DAYS);
            $placedAt = now()->subDays($daysAgo)->subMinutes(fake()->numberBetween(0, 1439));

            $status = $daysAgo > 14
                ? fake()->randomElement(['done', 'done', 'done', 'done', 'shipped', 'cancelled'])
                : fake()->randomElement(['pending', 'paid', 'paid', 'shipped', 'done', 'cancelled']);

            $order = new Order([
                'user_id' => $customer->id,
                'address_id' => $customer->addresses->first()->id,
                'total' => 0,
                'status' => $status,
                'placed_at' => $placedAt,
            ]);
            $order->forceFill(['created_at' => $placedAt, 'updated_at' => $placedAt])->save();

            $total = 0;
            $picked = [];
            foreach (range(1, fake()->numberBetween(1, 3)) as $_) {
                $product = $this->weightedPick($products, $weights);
                if (isset($picked[$product->id])) {
                    continue;
                }
                $picked[$product->id] = $product;

                $variant = $product->variants->random();
                $qty = fake()->numberBetween(1, 4);
                $item = $order->items()->make([
                    'variant_id' => $variant->id,
                    'quantity' => $qty,
                    'unit_price' => $variant->price,
                ]);
                $item->forceFill(['created_at' => $placedAt, 'updated_at' => $placedAt])->save();
                $total += $qty * $variant->price;
            }

            $order->update(['total' => $total]);

            // Delivered orders sometimes get a review (one per user/product).
            if ($status === 'done') {
                foreach ($picked as $product) {
                    $key = $customer->id.'-'.$product->id;
                    if (isset($reviewed[$key]) || fake()->numberBetween(1, 100) > 35) {
                        continue;
                    }
                    $reviewed[$key] = true;

                    $rating = fake()->randomElement([5, 5, 5, 4, 4, 4, 3, 2]);
                    $reviewedAt = $placedAt->copy()->addDays(fake()->numberBetween(3, 10))->min(now());
                    $review = new Review([
                        'user_id' => $customer->id,
                        'product_id' => $product->id,
                        'rating' => $rating,
                        'comment' => fake()->randomElement(self::COMMENTS[$rating]),
                    ]);
                    $review->forceFill(['created_at' => $reviewedAt, 'updated_at' => $reviewedAt])->save();
                }
            }
        }
    }

    /** Existing customers plus a pool of extra fake ones (created once, reused on re-run). */
    private function seedCustomers()
    {
        foreach (range(1, self::EXTRA_CUSTOMERS) as $i) {
            $user = User::firstOrCreate(
                ['email' => "fake{$i}@shop.test"],
                ['name' => fake()->name(), 'password' => 'password', 'phone' => '08'.fake()->numerify('########')]
            );

            if ($user->addresses()->doesntExist()) {
                Address::create([
                    'user_id' => $user->id,
                    'recipient' => $user->name,
                    'line1' => fake()->numerify('## ').fake()->streetName(),
                    'district' => fake()->randomElement(['Mueang', 'Hang Dong', 'San Sai', 'Saraphi']),
                    'province' => fake()->randomElement(['Chiang Mai', 'Bangkok', 'Khon Kaen', 'Phuket']),
                    'postal_code' => fake()->numerify('#####'),
                    'is_default' => true,
                ]);
            }
        }

        return User::where('role', 'customer')->with('addresses')->get()->filter(fn ($u) => $u->addresses->isNotEmpty());
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
