<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportLog extends Model
{
    protected $fillable = [
        'user_id', 
        'file_name', 
        'total_rows', 
        'processed_rows', 
        'progress', 
        'status', 
        'error_message'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
