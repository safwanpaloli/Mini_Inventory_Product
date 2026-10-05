<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ExportLog;
use App\Models\ProductVariant;
use App\Jobs\ExportProductsJob;
use Illuminate\Support\Str;

class ExportController extends Controller
{
    public function index()
    {
        $exports = ExportLog::with('user')->orderBy('created_at', 'desc')->get();
        return view('exports.index', compact('exports'));
    }

    public function start(Request $request)
    {
        $filters = $request->only('search');
        
        $query = ProductVariant::query();
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('sku', 'like', "%{$search}%");
        }
        $totalRows = $query->count();
        
        if ($totalRows === 0) {
            return response()->json(['error' => 'No data to export'], 400);
        }

        $fileName = 'products_export_' . Str::random(10) . '_' . date('Ymd_His') . '.csv';

        $exportLog = ExportLog::create([
            'user_id' => auth()->id(),
            'file_name' => $fileName,
            'total_rows' => $totalRows,
            'processed_rows' => 0,
            'progress' => 0,
            'status' => 'pending'
        ]);

        ExportProductsJob::dispatch($exportLog->id, $filters);

        return response()->json(['export_id' => $exportLog->id, 'message' => 'Export started in background']);
    }

    public function status($id)
    {
        $log = ExportLog::findOrFail($id);
        
        return response()->json([
            'id' => $log->id,
            'status' => $log->status,
            'progress' => $log->progress,
            'processed_rows' => $log->processed_rows,
            'total_rows' => $log->total_rows,
            'download_url' => $log->status === 'completed' ? route('exports.download', $log->id) : null,
            'error_message' => $log->error_message
        ]);
    }

    public function download($id)
    {
        $log = ExportLog::findOrFail($id);
        
        if ($log->status !== 'completed') {
            abort(404, 'Export not ready');
        }

        $path = storage_path('app/local/' . $log->file_name);
        if (!file_exists($path)) {
            // Check if it's in root storage/app instead
            $path = storage_path('app/' . $log->file_name);
        }
        
        if (!file_exists($path)) {
            abort(404, 'File not found on disk');
        }

        return response()->download($path);
    }
}
