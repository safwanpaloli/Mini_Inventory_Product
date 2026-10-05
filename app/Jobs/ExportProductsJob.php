<?php

namespace App\Jobs;

use App\Models\ExportLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ProductsExport;
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
            // Excel store using ProductsExport which implements WithEvents for tracking progress
            // Note: For chunking with progress we pass the log ID
            Excel::store(new ProductsExport($this->exportLogId, $this->filters), $exportLog->file_name, 'local');

            $exportLog->update([
                'status' => 'completed',
                'progress' => 100
            ]);
        } catch (Throwable $e) {
            $exportLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage()
            ]);
        }
    }
}
