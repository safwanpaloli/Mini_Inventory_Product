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
        $products = Product::with(['category', 'variants'])->latest()->paginate(15);
        return view('products.index', compact('products'));
    }

    public function create()
    {
        $categories = Category::all();
        $brands = Brand::all();
        $attributes = Attribute::with('values')->get();
        return view('products.create', compact('categories', 'brands', 'attributes'));
    }

    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'variants.attributeValues']);
        return view('products.show', compact('product'));
    }

    public function store(\App\Http\Requests\StoreProductRequest $request)
    {
        // Add additional manual validation for variants array inside since the request validates the structure
        $request->validate([
            'variants.*.sku' => 'required|string|distinct|unique:product_variants,sku',
            'variants.*.selling_price' => 'required|numeric|min:0',
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        DB::transaction(function () use ($request, $thumbnailPath) {
            $product = Product::create([
                'name' => $request->name,
                'slug' => Str::slug($request->name) . '-' . uniqid(),
                'base_price' => $request->base_price,
                'category_id' => $request->category_id,
                'brand_id' => $request->brand_id,
                'status' => 'active',
                'thumbnail' => $thumbnailPath,
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
        $product->load('variants.attributeValues');
        $categories = Category::all();
        $brands = Brand::all();
        return view('products.edit', compact('product', 'categories', 'brands'));
    }

    public function update(\App\Http\Requests\UpdateProductRequest $request, Product $product)
    {
        $request->validate([
            'thumbnail' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $data = [
            'name' => $request->name,
            'base_price' => $request->base_price,
            'category_id' => $request->category_id,
            'brand_id' => $request->brand_id,
        ];

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        // Update base product details
        $product->update($data);

        // Update existing variants
        if ($request->has('variants') && is_array($request->variants)) {
            foreach ($request->variants as $vData) {
                if (isset($vData['id'])) {
                    $variant = $product->variants()->find($vData['id']);
                    if ($variant) {
                        $variant->update([
                            'sku' => $vData['sku'],
                            'cost_price' => $vData['cost_price'] ?: 0,
                            'selling_price' => $vData['selling_price'],
                            'stock' => $vData['stock'],
                            'active' => $vData['active'],
                        ]);
                    }
                }
            }
        }
        
        return redirect()->route('products.index')->with('success', 'Product and variants updated successfully!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product deleted successfully!');
    }
}
