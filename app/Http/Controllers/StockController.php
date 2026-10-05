<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Services\ProductService;

class StockController extends Controller
{
    protected ProductService $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    public function index()
    {
        $variants = ProductVariant::with('product')->paginate(15);
        return view('stock.index', compact('variants'));
    }

    public function adjust(Request $request)
    {
        $request->validate([
            'variant_id' => 'required|exists:product_variants,id',
            'type' => 'required|in:in,out',
            'qty' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        try {
            $this->productService->adjustStock(
                $request->variant_id,
                $request->type,
                $request->qty,
                $request->reason,
                auth()->id()
            );
            
            return back()->with('success', 'Stock adjusted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
