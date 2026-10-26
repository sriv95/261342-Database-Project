<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['category', 'brand'])
            ->withCount('variants')
            ->withSum('variants', 'stock_qty')
            ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.products.index', ['products' => $products]);
    }

    public function create()
    {
        return view('admin.products.create', $this->formData());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            ...$this->productRules(),
            'sku' => ['required', 'string', 'max:40', 'unique:product_variants,sku'],
            'option_name' => ['required', 'string', 'max:60'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'stock_qty' => ['required', 'integer', 'min:0'],
        ]);

        $product = DB::transaction(function () use ($data, $request) {
            $product = Product::create([
                ...collect($data)->only(['category_id', 'brand_id', 'name', 'description'])->all(),
                'is_active' => $request->boolean('is_active'),
            ]);
            $product->variants()->create(collect($data)->only(['sku', 'option_name', 'price', 'stock_qty'])->all());

            return $product;
        });

        $this->storeImage($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Product created.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.edit', [
            ...$this->formData(),
            'product' => $product->load(['variants' => fn ($q) => $q->withCount('orderItems'), 'images']),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate($this->productRules());

        $product->update([...$data, 'is_active' => $request->boolean('is_active')]);
        $this->storeImage($request, $product);

        return back()->with('status', 'Product saved.');
    }

    public function destroy(Product $product)
    {
        $sold = OrderItem::whereIn('variant_id', $product->variants()->select('id'))->exists();

        if ($sold) {
            $product->update(['is_active' => false]);

            return redirect()->route('admin.products.index')
                ->with('status', 'Product has order history, so it was hidden instead of deleted.');
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    private function productRules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'brand_id' => ['required', 'exists:brands,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:5000'],
            'image' => ['nullable', 'image', 'max:2048'],
        ];
    }

    private function formData(): array
    {
        return ['categories' => Category::orderBy('name')->get(), 'brands' => Brand::orderBy('name')->get()];
    }

    private function storeImage(Request $request, Product $product): void
    {
        if (! $request->hasFile('image')) {
            return;
        }

        $file = $request->file('image');
        $name = Str::uuid().'.'.$file->extension();
        $file->move(public_path('images/products'), $name);

        DB::transaction(function () use ($product, $name) {
            $product->images()->update(['is_primary' => false]);
            $product->images()->create(['path' => 'images/products/'.$name, 'is_primary' => true]);
        });
    }
}
