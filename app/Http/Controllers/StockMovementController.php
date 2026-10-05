<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StockMovement;

class StockMovementController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = StockMovement::with(['variant.product', 'user'])->select('stock_movements.*');

            if ($request->has('type') && $request->type != '') {
                $query->where('type', $request->type);
            }

            if ($request->has('start_date') && $request->start_date != '') {
                $query->whereDate('created_at', '>=', $request->start_date);
            }

            if ($request->has('end_date') && $request->end_date != '') {
                $query->whereDate('created_at', '<=', $request->end_date);
            }

            return datatables()->eloquent($query)
                ->addColumn('product_name', function ($movement) {
                    return $movement->variant->product->name . ' - ' . $movement->variant->sku;
                })
                ->addColumn('user_name', function ($movement) {
                    return $movement->user ? $movement->user->name : 'System';
                })
                ->editColumn('type', function ($movement) {
                    return $movement->type === 'in' 
                        ? '<span class="badge bg-success">In</span>' 
                        : '<span class="badge bg-danger">Out</span>';
                })
                ->editColumn('created_at', function ($movement) {
                    return $movement->created_at->format('Y-m-d H:i:s');
                })
                ->rawColumns(['type'])
                ->make(true);
        }

        return view('stock_movements.index');
    }
}
