<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['product_id', 'sku', 'color', 'option_name', 'price', 'stock_qty'])]
class ProductVariant extends Model
{
    protected function casts(): array
    {
        return ['price' => 'decimal:2'];
    }

    /** "Blue · 4U-G5", or whichever part exists. */
    protected function label(): Attribute
    {
        return Attribute::get(fn () => collect([$this->color, $this->option_name])->filter()->implode(' · ') ?: 'Standard');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }
}
