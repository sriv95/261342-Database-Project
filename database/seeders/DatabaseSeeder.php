<?php

namespace Database\Seeders;

use App\Models\Address;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedUsers();
        $this->seedCatalog();
        $this->seedActivity();
    }

    private function seedUsers(): void
    {
        User::create([
            'name' => 'Shop Admin',
            'email' => 'admin@shop.test',
            'password' => 'password',
            'role' => 'admin',
        ]);

        foreach (['Somchai Jaidee', 'Napat Srisuk', 'Mali Wongdee'] as $i => $name) {
            $user = User::create([
                'name' => $name,
                'email' => 'customer'.($i + 1).'@shop.test',
                'password' => 'password',
                'phone' => '08'.fake()->numerify('########'),
            ]);

            Address::create([
                'user_id' => $user->id,
                'recipient' => $name,
                'line1' => fake()->numerify('## Nimman Rd.'),
                'district' => 'Mueang',
                'province' => 'Chiang Mai',
                'postal_code' => '50200',
                'is_default' => true,
            ]);
        }
    }

    private function seedCatalog(): void
    {
        $categories = collect(['Rackets', 'Shoes', 'Shuttlecocks', 'Strings', 'Grips & Accessories', 'Apparel & Bags'])
            ->mapWithKeys(fn ($name) => [$name => Category::create(['name' => $name, 'slug' => Str::slug($name)])]);

        $brands = collect(['Yonex', 'Li-Ning', 'Victor', 'Mizuno', 'Kawasaki'])
            ->mapWithKeys(fn ($name) => [$name => Brand::create(['name' => $name])]);

        // [category, brand, name, description, [[option, price, stock], ...]]
        $catalog = [
            ['Rackets', 'Yonex', 'Astrox 99 Pro', 'Head-heavy attacking racket for powerful smashes.', [['3U-G5', 7900, 12], ['4U-G5', 7900, 8]]],
            ['Rackets', 'Yonex', 'Nanoflare 800', 'Head-light speed racket for fast drives and defence.', [['4U-G5', 6900, 10], ['5U-G5', 6900, 6]]],
            ['Rackets', 'Yonex', 'Arcsaber 11 Pro', 'Balanced control racket with great feel.', [['3U-G5', 6500, 9], ['4U-G5', 6500, 7]]],
            ['Rackets', 'Li-Ning', 'Axforce 80', 'Stable all-round power racket.', [['3U-G5', 5200, 10], ['4U-G5', 5200, 10]]],
            ['Rackets', 'Li-Ning', 'Windstorm 72', 'Light and quick for doubles play.', [['4U-G5', 3900, 14]]],
            ['Rackets', 'Victor', 'Thruster K 9900', 'Powerful head-heavy racket.', [['3U-G5', 5800, 8], ['4U-G5', 5800, 8]]],
            ['Rackets', 'Victor', 'Auraspeed 90K', 'Fast swing speed with solid control.', [['3U-G5', 5400, 6]]],
            ['Rackets', 'Kawasaki', 'Honor S5', 'Great value entry racket.', [['4U-G5', 1290, 25], ['5U-G5', 1290, 20]]],
            ['Shoes', 'Yonex', 'Power Cushion 65 Z3', 'Cushioned court shoe with strong grip.', [['EU 40', 4200, 6], ['EU 42', 4200, 8], ['EU 44', 4200, 5]]],
            ['Shoes', 'Yonex', 'Eclipsion Z3', 'Lightweight shoe for quick footwork.', [['EU 40', 3900, 5], ['EU 42', 3900, 7], ['EU 44', 3900, 4]]],
            ['Shoes', 'Victor', 'P9200', 'Stable shoe with wide base.', [['EU 41', 3600, 6], ['EU 43', 3600, 6]]],
            ['Shoes', 'Mizuno', 'Wave Claw 2', 'Responsive shoe with Wave plate.', [['EU 40', 3300, 5], ['EU 42', 3300, 9], ['EU 44', 3300, 4]]],
            ['Shoes', 'Li-Ning', 'Saga Lite', 'Breathable everyday court shoe.', [['EU 41', 2400, 10], ['EU 43', 2400, 10]]],
            ['Shuttlecocks', 'Yonex', 'Aerosensa 30', 'Premium feather shuttle, tournament grade.', [['Speed 77 (12 pcs)', 1650, 40], ['Speed 78 (12 pcs)', 1650, 30]]],
            ['Shuttlecocks', 'Victor', 'Gold No.1', 'Durable training feather shuttle.', [['Speed 77 (12 pcs)', 1100, 50]]],
            ['Shuttlecocks', 'Kawasaki', 'Nylon Plastic Tube', 'Long-lasting nylon shuttle for practice.', [['Medium (6 pcs)', 350, 60], ['Fast (6 pcs)', 350, 60]]],
            ['Strings', 'Yonex', 'BG 65', 'Classic durable string.', [['0.70mm White', 280, 50], ['0.70mm Yellow', 280, 40]]],
            ['Strings', 'Yonex', 'BG 80 Power', 'High repulsion string for power.', [['0.68mm White', 420, 30], ['0.68mm Yellow', 420, 30]]],
            ['Strings', 'Victor', 'VBS-66N', 'Control-oriented string with crisp feel.', [['0.66mm White', 330, 35]]],
            ['Grips & Accessories', 'Yonex', 'Super Grap (3 pcs)', 'Overgrip, tacky and absorbent.', [['White', 150, 80], ['Black', 150, 80]]],
            ['Grips & Accessories', 'Victor', 'Wrist Band', 'Cotton wrist band.', [['Black', 120, 40]]],
            ['Apparel & Bags', 'Yonex', 'Match Tournament Bag', '6-racket bag with shoe compartment.', [['Black/Red', 2900, 8], ['Navy', 2900, 6]]],
            ['Apparel & Bags', 'Li-Ning', 'Team Polo', 'Quick-dry polo for training.', [['M', 790, 15], ['L', 790, 15], ['XL', 790, 10]]],
            ['Apparel & Bags', 'Kawasaki', 'Sport Shorts', 'Lightweight stretch shorts.', [['M', 450, 20], ['L', 450, 20]]],
        ];

        foreach ($catalog as $n => [$cat, $brand, $name, $desc, $variants]) {
            $product = Product::create([
                'category_id' => $categories[$cat]->id,
                'brand_id' => $brands[$brand]->id,
                'name' => $name,
                'description' => $desc,
            ]);

            $product->images()->create(['path' => 'images/placeholder.svg', 'is_primary' => true]);

            foreach ($variants as $v => [$option, $price, $stock]) {
                $product->variants()->create([
                    'sku' => sprintf('SKU-%03d-%d', $n + 1, $v + 1),
                    'option_name' => $option,
                    'price' => $price,
                    'stock_qty' => $stock,
                ]);
            }
        }
    }

    private function seedActivity(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products = Product::with('variants')->get();

        foreach ($customers as $customer) {
            $customer->cart()->create();
            $customer->wishlistProducts()->attach($products->random(3)->pluck('id'));

            foreach (range(1, 2) as $_) {
                $order = Order::create([
                    'user_id' => $customer->id,
                    'address_id' => $customer->addresses()->first()->id,
                    'total' => 0,
                    'status' => fake()->randomElement(['paid', 'shipped', 'done']),
                    'placed_at' => now()->subDays(fake()->numberBetween(1, 60)),
                ]);

                $total = 0;
                foreach ($products->random(2) as $product) {
                    $variant = $product->variants->random();
                    $qty = fake()->numberBetween(1, 2);
                    $order->items()->create([
                        'variant_id' => $variant->id,
                        'quantity' => $qty,
                        'unit_price' => $variant->price,
                    ]);
                    $total += $qty * $variant->price;
                }
                $order->update(['total' => $total]);

                foreach ($order->items()->with('variant')->get() as $item) {
                    Review::firstOrCreate(
                        ['user_id' => $customer->id, 'product_id' => $item->variant->product_id],
                        [
                            'rating' => fake()->numberBetween(3, 5),
                            'comment' => fake()->randomElement([
                                'Great quality, arrived quickly.',
                                'Good value for the price.',
                                'Feels solid on court.',
                                'Exactly as described.',
                            ]),
                        ]
                    );
                }
            }
        }
    }
}
