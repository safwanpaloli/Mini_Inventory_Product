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
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Product::with(['category', 'brand', 'variants'])->select('products.*');
            
            // Custom filtering
            if ($request->has('category_id') && $request->category_id != '') {
                $query->where('category_id', $request->category_id);
            }
            if ($request->has('brand_id') && $request->brand_id != '') {
                $query->where('brand_id', $request->brand_id);
            }
            if ($request->has('status') && $request->status != '') {
                $query->where('status', $request->status);
            }

            return datatables()->eloquent($query)
                ->addColumn('category_name', function ($product) {
                    return $product->category ? $product->category->name : 'N/A';
                })
                ->addColumn('brand_name', function ($product) {
                    return $product->brand ? $product->brand->name : 'N/A';
                })
                ->addColumn('variants_count', function ($product) {
                    return $product->variants->count();
                })
                ->addColumn('action', function ($product) {
                    return view('products.partials.actions', compact('product'))->render();
                })
                ->filterColumn('category_name', function ($query, $keyword) {
                    $query->whereHas('category', function ($q) use ($keyword) {
                        $q->where('name', 'like', "%{$keyword}%");
                    });
                })
                ->orderColumn('category_name', function ($query, $order) {
                    $query->join('categories', 'products.category_id', '=', 'categories.id')
                          ->orderBy('categories.name', $order);
                })
                ->rawColumns(['action'])
                ->make(true);
        }

        $categories = Category::all();
        $brands = Brand::all();
        return view('products.index', compact('categories', 'brands'));
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
            if (!$request->file('thumbnail')->isValid()) {
                return back()->withInput()->with('error', 'The thumbnail failed to upload. PHP temporary directory error.');
            }
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
            if (!$request->file('thumbnail')->isValid()) {
                return back()->withInput()->with('error', 'The thumbnail failed to upload. PHP temporary directory error.');
            }
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
