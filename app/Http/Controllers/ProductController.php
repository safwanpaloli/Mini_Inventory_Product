<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Attribute;
use Illuminate\Support\Str;
use App\Services\ProductService;

class ProductController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
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

        $this->productService->createProduct(
            $request->only(['name', 'base_price', 'category_id', 'brand_id']),
            $request->variants ?? [],
            $thumbnailPath
        );

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

        $thumbnailPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbnailPath = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $this->productService->updateProduct(
            $product,
            $request->only(['name', 'base_price', 'category_id', 'brand_id']),
            $request->variants ?? [],
            $thumbnailPath
        );
        
        return redirect()->route('products.index')->with('success', 'Product and variants updated successfully!');
    }

    public function destroy(Product $product)
    {
        $product->delete();
        return back()->with('success', 'Product deleted successfully!');
    }
}
