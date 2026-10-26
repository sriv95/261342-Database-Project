<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class VariantController extends Controller
{
    public function store(Request $request, Product $product)
    {
        $product->variants()->create($request->validate($this->rules()));

        return back()->with('status', 'Variant added.');
    }

    public function update(Request $request, ProductVariant $variant)
    {
        $variant->update($request->validate($this->rules($variant)));

        return back()->with('status', 'Variant saved.');
    }

    public function destroy(ProductVariant $variant)
    {
        if ($variant->orderItems()->exists()) {
            return back()->withErrors(['variant' => 'This variant appears in past orders. Set its stock to 0 instead.']);
        }

        if ($variant->product->variants()->count() === 1) {
            return back()->withErrors(['variant' => 'A product needs at least one variant.']);
        }

        $variant->delete();

        return back()->with('status', 'Variant deleted.');
    }

    private function rules(?ProductVariant $variant = null): array
    {
        return [
            'sku' => ['required', 'string', 'max:40', Rule::unique('product_variants', 'sku')->ignore($variant)],
            'option_name' => ['required', 'string', 'max:60'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'stock_qty' => ['required', 'integer', 'min:0'],
        ];
    }
}
