<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OvertimeDetail extends Model
{
    protected $fillable = [
        'overtime_id',
        'employee_id',
        'daily_hours',
        'use_uang_makan',
        'rate_lembur_fix',
        'rate_makan_fix',
        'golongan_fix',
    ];

    protected $casts = [
        'daily_hours' => 'array',
        'use_uang_makan' => 'boolean',
    ];

    public function overtime()
    {
        return $this->belongsTo(Overtime::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
