<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportBatchError extends Model
{
    protected $fillable = [
        'import_batch_id',
        'row_number',
        'id_rup',
        'error_type',
        'error_message',
    ];

    public function batch()
    {
        return $this->belongsTo(ImportBatch::class, 'import_batch_id');
    }
}