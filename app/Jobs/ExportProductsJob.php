<?php

namespace App\Jobs;

use App\Models\ExportLog;
use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ExportProductsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 0; // unlimited timeout for huge exports

    protected $exportLogId;
    protected $filters;

    public function __construct($exportLogId, $filters = [])
    {
        $this->exportLogId = $exportLogId;
        $this->filters = $filters;
    }

    public function handle(): void
    {
        $exportLog = ExportLog::find($this->exportLogId);
        if (!$exportLog) return;

        $exportLog->update(['status' => 'processing']);

        try {
            // Disable query log to prevent memory leaks
            \Illuminate\Support\Facades\DB::disableQueryLog();

            // Create file stream directly to avoid Maatwebsite memory overhead
            $filePath = Storage::disk('local')->path($exportLog->file_name);
            
            $directory = dirname($filePath);
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            $file = fopen($filePath, 'w');

            // Write CSV headers
            fputcsv($file, [
                'Product ID', 'Product Name', 'Category', 'Brand', 'Variant SKU',
                'Base Price', 'Cost Price', 'Selling Price', 'Stock', 'Reorder Level', 'Active'
            ]);

            // Query products
            $query = ProductVariant::query()->with(['product.category', 'product.brand']);
            if (!empty($this->filters['search'])) {
                $search = $this->filters['search'];
                $query->whereHas('product', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })->orWhere('sku', 'like', "%{$search}%");
            }

            $total = $exportLog->total_rows ?: 1;
            $processed = 0;

            // Stream results using chunk to keep memory extremely low
            $query->chunk(500, function($variants) use ($file, &$processed, $total, $exportLog) {
                foreach ($variants as $variant) {
                    fputcsv($file, [
                        $variant->product->id ?? '',
                        $variant->product->name ?? '',
                        $variant->product->category->name ?? 'None',
                        $variant->product->brand->name ?? 'None',
                        $variant->sku,
                        $variant->product->base_price ?? 0,
                        $variant->cost_price,
                        $variant->selling_price,
                        $variant->stock,
                        $variant->reorder_level,
                        $variant->active ? 'Yes' : 'No',
                    ]);
                    $processed++;
                }

                // Update progress
                $progress = min(99, round(($processed / $total) * 100));
                $exportLog->update([
                    'processed_rows' => $processed,
                    'progress' => $progress
                ]);
            });

            fclose($file);

            $exportLog->update([
                'status' => 'completed',
                'progress' => 100
            ]);
        } catch (Throwable $e) {
            if (isset($file) && is_resource($file)) {
                fclose($file);
            }
            $exportLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
