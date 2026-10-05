<?php

namespace App\Exports;

use App\Models\ProductVariant;
use App\Models\ExportLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

class ProductsExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    protected $exportLogId;
    protected $filters;
    protected $processed = 0;
    protected $log;

    public function __construct($exportLogId, $filters = [])
    {
        \Illuminate\Support\Facades\DB::disableQueryLog();
        $this->exportLogId = $exportLogId;
        $this->filters = $filters;
        $this->log = ExportLog::find($exportLogId);
    }

    public function query(): Builder|EloquentBuilder|Relation
    {
        // Query products with variants
        $query = ProductVariant::query()->with(['product.category', 'product.brand']);
        
        // Simple search filter if provided
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->whereHas('product', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('sku', 'like', "%{$search}%");
        }
        
        return $query;
    }

    public function headings(): array
    {
        return [
            'Product ID',
            'Product Name',
            'Category',
            'Brand',
            'Variant SKU',
            'Base Price',
            'Cost Price',
            'Selling Price',
            'Stock',
            'Reorder Level',
            'Active',
        ];
    }

    public function map($variant): array
    {
        $this->processed++;
        
        // Update database progress every 250 rows to avoid DB bottleneck
        if ($this->processed % 250 === 0 && $this->log) {
            $total = $this->log->total_rows ?: 1;
            $progress = min(99, round(($this->processed / $total) * 100));
            $this->log->update([
                'processed_rows' => $this->processed,
                'progress' => $progress
            ]);
        }

        return [
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
        ];
    }

    public function chunkSize(): int
    {
        return 250;
    }
}
