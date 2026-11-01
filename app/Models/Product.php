<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['category_id', 'brand_id', 'name', 'description', 'is_active'])]
class Product extends Model
{
    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    /** Adds `sold_monthly`: units sold in the last 30 days (cancelled orders ignored). */
    public function scopeWithMonthlySold(Builder $query): void
    {
        $sold = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('product_variants', 'product_variants.id', '=', 'order_items.variant_id')
            ->whereColumn('product_variants.product_id', 'products.id')
            ->where('orders.status', '!=', 'cancelled')
            ->where('orders.placed_at', '>=', now()->subDays(30))
            ->selectRaw('COALESCE(SUM(order_items.quantity), 0)');

        $query->addSelect('products.*')->selectSub($sold, 'sold_monthly');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function wishedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlists');
    }
}
