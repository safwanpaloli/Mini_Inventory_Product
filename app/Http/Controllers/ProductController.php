<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with(['category', 'variants'])->get();
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $attributes = Attribute::with('values')->get();
        return view('products.create', compact('categories', 'brands', 'attributes'));
    }

    public function store(\App\Http\Requests\StoreProductRequest $request)
    {
        // Add additional manual validation for variants array inside since the request validates the structure
        $request->validate([
            'variants.*.sku' => 'required|string|distinct|unique:product_variants,sku',
            'variants.*.selling_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request) {
            $product = Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . uniqid(),
                'base_price' => $request->base_price,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'status' => 'active',
            ]);

            foreach ($request->variants as $vData) {
                $variant = $product->variants()->create([
                    'sku' => $vData['sku'],
                    'cost_price' => $vData['cost_price'] ?: 0,
                    'selling_price' => $vData['selling_price'],
                    'stock' => $vData['stock'],
                    'active' => $vData['active'],
                ]);

                if (isset($vData['values'])) {
                    $valIds = explode(',', $vData['values']);
                    $variant->attributeValues()->attach($valIds);
                }
            }
        });

        return redirect()->route('products.index')->with('success', 'Product and variants saved successfully!');
    }

    public function edit(Product $product)
    {
        $categories = Category::all();
        $brands = Brand::all();
        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(\App\Http\Requests\UpdateProductRequest $request, Product $product)
    {
        // Currently only updating base product details for simplicity
        $product->update([
            'name' => $request->name,
            'base_price' => $request->base_price,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
        ]);
        
        return redirect()->route('products.index')->with('success', 'Product updated successfully! (Variant updating requires complex UI rehydration)');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product deleted successfully!');
    }
}
