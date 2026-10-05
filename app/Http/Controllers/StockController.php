<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockController extends Controller
{
    public function index()
    {
        $variants = ProductVariant::with('product')->get();
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
            DB::transaction(function () use ($request) {
                // Lock the variant row for update to prevent race conditions
                $variant = ProductVariant::where('id', $request->variant_id)->lockForUpdate()->firstOrFail();
                
                $oldStock = $variant->stock;
                $qty = $request->qty;
                
                if ($request->type === 'out' && $variant->stock < $qty) {
                    throw new \Exception("Insufficient stock. Current stock is {$variant->stock}.");
                }
                
                $newStock = $request->type === 'in' ? $oldStock + $qty : $oldStock - $qty;
                
                // Update variant stock
                $variant->stock = $newStock;
                $variant->save();
                
                // Write ledger row
                StockMovement::create([
                    'product_variant_id' => $variant->id,
                    'user_id' => auth()->id(),
                    'type' => $request->type,
                    'qty' => $qty,
                    'balance_after' => $newStock,
                    'reason' => $request->reason,
                ]);
            });
            
            return back()->with('success', 'Stock adjusted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
