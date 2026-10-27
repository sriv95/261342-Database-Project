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

        // One file per brand in database/seeders/data/, see data/README.md for the format.
        foreach (glob(__DIR__.'/data/*.php') as $file) {
            foreach (require $file as $item) {
                $product = Product::create([
                    'category_id' => $categories[$item['category']]->id,
                    'brand_id' => $brands[$item['brand']]->id,
                    'name' => preg_replace('/^'.preg_quote($item['brand'], '/').'\s+/i', '', $item['name']),
                    'description' => $item['description'],
                ]);

                foreach ($item['images'] ?: ['images/placeholder.svg'] as $i => $path) {
                    $product->images()->create(['path' => $path, 'is_primary' => $i === 0]);
                }

                foreach ($item['variants'] as $variant) {
                    $product->variants()->create([
                        'sku' => $variant['sku'],
                        'color' => $variant['color'] ?? null,
                        'option_name' => $variant['option'] ?? null,
                        'price' => $variant['price'],
                        'stock_qty' => $variant['stock'],
                    ]);
                }
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
